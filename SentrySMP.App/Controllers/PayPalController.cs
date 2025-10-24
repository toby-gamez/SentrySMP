using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class PayPalController : ControllerBase
    {
        private readonly IHttpClientFactory _httpFactory;
        private readonly IConfiguration _config;
        private readonly SentrySMP.Api.Infrastructure.Data.SentryDbContext _db;

        public PayPalController(IHttpClientFactory httpFactory, IConfiguration config, SentrySMP.Api.Infrastructure.Data.SentryDbContext db)
        {
            _httpFactory = httpFactory;
            _config = config;
            _db = db;
        }

        [HttpPost("create-order")]
        public async Task<IActionResult> CreateOrder([FromBody] CreateOrderRequest request)
        {
            var clientId = _config["PAYPAL_CLIENT_ID"] ?? Environment.GetEnvironmentVariable("PAYPAL_CLIENT_ID");
            var secret = _config["PAYPAL_SECRET"] ?? Environment.GetEnvironmentVariable("PAYPAL_SECRET");
            if (string.IsNullOrWhiteSpace(clientId) || string.IsNullOrWhiteSpace(secret))
            {
                return BadRequest("PayPal credentials not configured on server (PAYPAL_CLIENT_ID / PAYPAL_SECRET)");
            }

            var http = _httpFactory.CreateClient();

            // Get OAuth token
            var tokenRequest = new HttpRequestMessage(HttpMethod.Post, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
            var basic = Convert.ToBase64String(Encoding.UTF8.GetBytes($"{clientId}:{secret}"));
            tokenRequest.Headers.Authorization = new AuthenticationHeaderValue("Basic", basic);
            tokenRequest.Content = new FormUrlEncodedContent(new[] { new KeyValuePair<string, string>("grant_type", "client_credentials") });

            var tokenResponse = await http.SendAsync(tokenRequest);
            if (!tokenResponse.IsSuccessStatusCode)
            {
                var txt = await tokenResponse.Content.ReadAsStringAsync();
                return StatusCode((int)tokenResponse.StatusCode, txt);
            }

            using var tokenStream = await tokenResponse.Content.ReadAsStreamAsync();
            var tokenJson = await JsonDocument.ParseAsync(tokenStream);
            var accessToken = tokenJson.RootElement.GetProperty("access_token").GetString();

            // Build return/cancel URLs (PayPal needs full URLs)
            var baseUrl = $"{Request.Scheme}://{Request.Host}";
            var returnUrl = baseUrl + "/api/paypal/return";
            var cancelUrl = baseUrl + "/checkout?status=cancelled";

            var createPayload = new
            {
                intent = "CAPTURE",
                purchase_units = new[] {
                    new {
                        amount = new { currency_code = "EUR", value = request.Amount }
                    }
                },
                application_context = new {
                    return_url = returnUrl,
                    cancel_url = cancelUrl
                }
            };

            var createReq = new HttpRequestMessage(HttpMethod.Post, "https://api-m.sandbox.paypal.com/v2/checkout/orders");
            createReq.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
            createReq.Content = new StringContent(JsonSerializer.Serialize(createPayload), Encoding.UTF8, "application/json");

            var createResp = await http.SendAsync(createReq);
            if (!createResp.IsSuccessStatusCode)
            {
                var txt = await createResp.Content.ReadAsStringAsync();
                return StatusCode((int)createResp.StatusCode, txt);
            }

            using var createStream = await createResp.Content.ReadAsStreamAsync();
            var createJson = await JsonDocument.ParseAsync(createStream);
            var orderId = createJson.RootElement.GetProperty("id").GetString();
            string approveUrl = string.Empty;
            if (createJson.RootElement.TryGetProperty("links", out var links))
            {
                foreach (var l in links.EnumerateArray())
                {
                    if (l.GetProperty("rel").GetString() == "approve")
                    {
                        approveUrl = l.GetProperty("href").GetString();
                        break;
                    }
                }
            }

            return Ok(new CreateOrderResponse { ApproveUrl = approveUrl ?? string.Empty, OrderId = orderId ?? string.Empty });
        }

        // PayPal will redirect the buyer to this endpoint after approval
        [HttpGet("return")]
        public async Task<IActionResult> Return()
        {
            // PayPal v2 typically returns 'token' query parameter containing order id
            var token = Request.Query["token"].ToString();
            if (string.IsNullOrEmpty(token)) token = Request.Query["orderId"].ToString();
            if (string.IsNullOrEmpty(token)) return Redirect("/checkout?status=error");

            var clientId = _config["PAYPAL_CLIENT_ID"] ?? Environment.GetEnvironmentVariable("PAYPAL_CLIENT_ID");
            var secret = _config["PAYPAL_SECRET"] ?? Environment.GetEnvironmentVariable("PAYPAL_SECRET");
            if (string.IsNullOrWhiteSpace(clientId) || string.IsNullOrWhiteSpace(secret))
            {
                return Redirect("/checkout?status=error");
            }

            var http = _httpFactory.CreateClient();

            // Get access token again
            var tokenRequest = new HttpRequestMessage(HttpMethod.Post, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
            var basic = Convert.ToBase64String(Encoding.UTF8.GetBytes($"{clientId}:{secret}"));
            tokenRequest.Headers.Authorization = new AuthenticationHeaderValue("Basic", basic);
            tokenRequest.Content = new FormUrlEncodedContent(new[] { new KeyValuePair<string, string>("grant_type", "client_credentials") });
            var tokenResponse = await http.SendAsync(tokenRequest);
            if (!tokenResponse.IsSuccessStatusCode) return Redirect("/checkout?status=error");
            using var tokenStream = await tokenResponse.Content.ReadAsStreamAsync();
            var tokenJson = await JsonDocument.ParseAsync(tokenStream);
            var accessToken = tokenJson.RootElement.GetProperty("access_token").GetString();

            // Capture order
            var captureReq = new HttpRequestMessage(HttpMethod.Post, $"https://api-m.sandbox.paypal.com/v2/checkout/orders/{Uri.EscapeDataString(token)}/capture");
            captureReq.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
            captureReq.Content = new StringContent("{}", Encoding.UTF8, "application/json");

            var captureResp = await http.SendAsync(captureReq);
            var captureTxt = await captureResp.Content.ReadAsStringAsync();
            if (!captureResp.IsSuccessStatusCode)
            {
                return Redirect("/checkout?status=error");
            }

            // Persist transaction to DB (minimal info)
            try
            {
                decimal amount = 0;
                string currency = "EUR";
                // Try to parse captured amount from response JSON
                try
                {
                    using var doc = JsonDocument.Parse(captureTxt);
                    var root = doc.RootElement;
                    if (root.TryGetProperty("purchase_units", out var pus))
                    {
                        foreach (var pu in pus.EnumerateArray())
                        {
                            if (pu.TryGetProperty("payments", out var payments) && payments.TryGetProperty("captures", out var caps))
                            {
                                foreach (var cap in caps.EnumerateArray())
                                {
                                    if (cap.TryGetProperty("amount", out var amt) && amt.TryGetProperty("value", out var v))
                                    {
                                        decimal.TryParse(v.GetString() ?? "0", System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out amount);
                                    }
                                    if (cap.TryGetProperty("amount", out var amt2) && amt2.TryGetProperty("currency_code", out var c))
                                    {
                                        currency = c.GetString() ?? currency;
                                    }
                                }
                            }
                        }
                    }
                }
                catch { }

                var tx = new SentrySMP.Domain.Entities.PaymentTransaction
                {
                    Provider = "PayPal",
                    ProviderTransactionId = token,
                    Amount = (decimal)amount,
                    Currency = currency,
                    MinecraftUsername = string.Empty,
                    ItemsJson = string.Empty,
                    Status = "captured",
                    RawResponse = captureTxt,
                    CreatedAt = DateTime.UtcNow
                };
                _db.PaymentTransactions.Add(tx);
                await _db.SaveChangesAsync();
            }
            catch (Exception ex)
            {
                // swallow DB errors but log if necessary
            }

            // Redirect user back to frontend where Blazor can clear cart and show success
            var frontendSuccess = "/checkout?status=success&orderId=" + Uri.EscapeDataString(token);
            return Redirect(frontendSuccess);
        }
    }
}
