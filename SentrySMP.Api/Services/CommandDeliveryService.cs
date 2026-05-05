using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services
{
    public class CommandDeliveryService : IRconService
    {
        private readonly ICommandService _commandService;
        private readonly IHttpClientFactory _httpClientFactory;
        private readonly IConfiguration _configuration;
        private readonly ILogger<CommandDeliveryService> _logger;

        public CommandDeliveryService(
            ICommandService commandService,
            IHttpClientFactory httpClientFactory,
            IConfiguration configuration,
            ILogger<CommandDeliveryService> logger)
        {
            _commandService = commandService;
            _httpClientFactory = httpClientFactory;
            _configuration = configuration;
            _logger = logger;
        }

        public async Task<RconExecutionResult> ExecuteCommandsForProductsAsync(
            List<ProductQuantityDto> productsWithQuantity,
            string? username,
            string? voucherCode = null,
            double? paidAmount = null)
        {
            if (productsWithQuantity == null || productsWithQuantity.Count == 0)
                return new RconExecutionResult { AllSucceeded = true };

            // Sanitize username to prevent command injection via newlines or control chars
            var sanitizedUsername = (username ?? string.Empty)
                .Trim()
                .Replace("\r", string.Empty)
                .Replace("\n", string.Empty);

            // 1. Fetch all commands from database
            List<Domain.Entities.Command> allCommands;
            try
            {
                allCommands = await _commandService.GetAllAsync();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to load commands for delivery execution");
                return new RconExecutionResult
                {
                    AllSucceeded = false,
                    CommandResults = new List<RconCommandResult>
                    {
                        new RconCommandResult
                        {
                            CommandText = "<LOAD_COMMANDS_FAILURE>",
                            Succeeded = false,
                            ErrorMessage = ex.Message
                        }
                    }
                };
            }

            // 2. Resolve command texts for each product (replace %player% token)
            var commandTexts = new List<string>();
            var commandProductNames = new List<string>(); // parallel list: product name for each command
            var anyCommandsDefined = false;

            foreach (var pq in productsWithQuantity)
            {
                var product = pq.Product;
                if (product == null) continue;

                var cmds = allCommands
                    .Where(c => string.Equals(c.Type, product.Type, StringComparison.OrdinalIgnoreCase)
                                && c.TypeId == product.Id)
                    .ToList();

                if (cmds.Count == 0)
                {
                    _logger.LogWarning("No commands defined for product {Type}/{Id} ({Name})", product.Type, product.Id, product.Name);
                    continue;
                }

                anyCommandsDefined = true;
                var repeat = pq.Quantity <= 0 ? 1 : pq.Quantity;
                var productName = product.Name ?? $"{product.Type} #{product.Id}";

                for (var r = 0; r < repeat; r++)
                {
                    foreach (var cmd in cmds)
                    {
                        var text = (cmd.CommandText ?? string.Empty)
                            .Replace("%player%", sanitizedUsername, StringComparison.OrdinalIgnoreCase);
                        commandTexts.Add(text);
                        commandProductNames.Add(productName);
                    }
                }
            }

            if (!anyCommandsDefined || commandTexts.Count == 0)
            {
                _logger.LogWarning("No delivery commands found for any of the {Count} product(s)", productsWithQuantity.Count);
                return new RconExecutionResult
                {
                    AllSucceeded = false,
                    CommandResults = new List<RconCommandResult>
                    {
                        new RconCommandResult
                        {
                            CommandText = "<NO_RCON_COMMANDS_FOUND>",
                            Succeeded = false
                        }
                    }
                };
            }

            // 3. Compute total price from products if not provided
            var price = paidAmount ?? productsWithQuantity.Sum(pq =>
                (pq.Product?.Sale > 0
                    ? pq.Product.Price * (1 - pq.Product.Sale / 100.0)
                    : pq.Product?.Price ?? 0)
                * (pq.Quantity <= 0 ? 1 : pq.Quantity));

            // 4. Build cart as a pretty-printed JSON string.
            //    The Java API has `String cart` — sending an array causes 400.
            //    Each cart item: { "<ProductType>": { Id, Name, Price, Server }, "Quantity": n }
            var cartForJson = productsWithQuantity.Select(pq =>
            {
                var srv = pq.Product?.Server;
                var item = new Dictionary<string, object?>
                {
                    [pq.Product?.Type ?? "Product"] = new
                    {
                        Id = pq.Product?.Id ?? 0,
                        Name = pq.Product?.Name ?? string.Empty,
                        Price = pq.Product?.Price ?? 0,
                        Server = srv == null ? null : (object)new
                        {
                            Id = srv.Id,
                            Name = srv.Name,
                            RCONIP = srv.RCONIP,
                            RCONPort = srv.RCONPort,
                            RCONPassword = srv.RCONPassword
                        }
                    },
                    ["Quantity"] = (object)pq.Quantity
                };
                return item;
            }).ToList();

            // Build the outer request as a plain dictionary to avoid JsonNode/TypeInfoResolver issues
            var requestDict = new Dictionary<string, object?>
            {
                ["commands"] = commandTexts,
                ["playerName"] = sanitizedUsername,
                ["price"] = price,
                ["cart"] = cartForJson
            };
            if (!string.IsNullOrWhiteSpace(voucherCode))
                requestDict["voucher"] = voucherCode;

            // 5. Send POST request to delivery API
            var apiUrl = _configuration["Delivery:ApiUrl"] ?? "http://cz1.sentrysmp.eu:27013/command";
            var apiKey = _configuration["Delivery:ApiKey"] ?? string.Empty;

            _logger.LogInformation(
                "Sending delivery request to {Url} for player '{Player}' with {CmdCount} command(s) price={Price} voucher={Voucher}",
                apiUrl, sanitizedUsername, commandTexts.Count, price, voucherCode ?? "(none)");

            try
            {
                var client = _httpClientFactory.CreateClient("Delivery");
                client.DefaultRequestHeaders.Clear();
                client.DefaultRequestHeaders.Add("accept", "application/json");
                if (!string.IsNullOrWhiteSpace(apiKey))
                    client.DefaultRequestHeaders.Add("X-API-Key", apiKey);

                var json = JsonSerializer.Serialize(requestDict);
                _logger.LogInformation("Delivery API request body: {Json}", json);
                var content = new StringContent(json, Encoding.UTF8, "application/json");

                var response = await client.PostAsync(apiUrl, content);
                var responseBody = await response.Content.ReadAsStringAsync();

                _logger.LogInformation("Delivery API response HTTP {Code}: {Body}", (int)response.StatusCode, responseBody);

                DeliveryApiResponse? deliveryResp = null;
                try
                {
                    deliveryResp = JsonSerializer.Deserialize<DeliveryApiResponse>(responseBody, new JsonSerializerOptions(JsonSerializerDefaults.Web));
                }
                catch (Exception exParse)
                {
                    _logger.LogWarning(exParse, "Could not parse delivery API response body");
                }

                if (deliveryResp?.Success == true)
                {
                    var outputStr = deliveryResp.Output != null && deliveryResp.Output.Count > 0
                        ? string.Join("; ", deliveryResp.Output)
                        : string.Empty;

                    return new RconExecutionResult
                    {
                        AllSucceeded = true,
                        CommandResults = commandTexts.Select((cmd, i) => new RconCommandResult
                        {
                            CommandText = cmd,
                            ProductName = commandProductNames.Count > i ? commandProductNames[i] : null,
                            Succeeded = true,
                            Response = outputStr
                        }).ToList()
                    };
                }
                else
                {
                    var errorMsg = deliveryResp?.Error
                        ?? $"Delivery API returned an error (HTTP {(int)response.StatusCode})";

                    _logger.LogWarning("Delivery API failure for player '{Player}': {Error}", sanitizedUsername, errorMsg);

                    return new RconExecutionResult
                    {
                        AllSucceeded = false,
                        CommandResults = commandTexts.Select((cmd, i) => new RconCommandResult
                        {
                            CommandText = cmd,
                            ProductName = commandProductNames.Count > i ? commandProductNames[i] : null,
                            Succeeded = false,
                            ErrorMessage = errorMsg
                        }).ToList()
                    };
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Exception while calling delivery API for player '{Player}'", sanitizedUsername);
                return new RconExecutionResult
                {
                    AllSucceeded = false,
                    CommandResults = commandTexts.Select((cmd, i) => new RconCommandResult
                    {
                        CommandText = cmd,
                        ProductName = commandProductNames.Count > i ? commandProductNames[i] : null,
                        Succeeded = false,
                        ErrorMessage = ex.Message
                    }).ToList()
                };
            }
        }
    }
}
