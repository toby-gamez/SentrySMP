using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Threading.Tasks;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;
using System.Buffers.Binary;
using System.Net.Sockets;

namespace SentrySMP.Api.Services
{
    public class RconService : IRconService
    {
        private readonly ICommandService _commandService;
        private readonly IServerService _serverService;
        private readonly ILogger<RconService> _logger;

        public RconService(ICommandService commandService, IServerService serverService, ILogger<RconService> logger)
        {
            _commandService = commandService;
            _serverService = serverService;
            _logger = logger;
        }

        public async Task<SentrySMP.Shared.DTOs.RconExecutionResult> ExecuteCommandsForProductsAsync(List<SentrySMP.Shared.DTOs.ProductQuantityDto> productsWithQuantity, string? username)
        {
            if (productsWithQuantity == null || productsWithQuantity.Count == 0)
            {
                _logger.LogDebug("No products provided for RCON execution.");
                return new SentrySMP.Shared.DTOs.RconExecutionResult { AllSucceeded = true };
            }

            List<Domain.Entities.Command> allCommands;
            try
            {
                allCommands = await _commandService.GetAllAsync();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to load commands for RCON execution");
                return new SentrySMP.Shared.DTOs.RconExecutionResult
                {
                    AllSucceeded = false,
                    CommandResults = new System.Collections.Generic.List<SentrySMP.Shared.DTOs.RconCommandResult>
                    {
                        new SentrySMP.Shared.DTOs.RconCommandResult { CommandText = "<LOAD_COMMANDS_FAILURE>", Succeeded = false, ErrorMessage = ex.Message }
                    }
                };
            }

            // Preload all servers for fallback
            List<ServerResponse> allServers = new();
            try
            {
                var srvEnum = await _serverService.GetAllServersAsync();
                if (srvEnum != null) allServers = srvEnum.ToList();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to load servers for RCON execution");
            }

            // sanitize username: trim and remove CR/LF to avoid injecting newlines into commands
            var sanitizedUsername = (username ?? string.Empty).Trim().Replace("\r", string.Empty).Replace("\n", string.Empty);

            var result = new SentrySMP.Shared.DTOs.RconExecutionResult();
            // track whether we actually found any commands for the requested products
            var anyCommandsDefined = false;

            foreach (var pq in productsWithQuantity)
            {
                // expose product variable to the catch block below
                ProductResponse? product = pq.Product;
                var repeat = pq.Quantity <= 0 ? 1 : pq.Quantity;
                try
                {
                    if (product == null)
                    {
                        _logger.LogDebug("ProductQuantity entry had null Product, skipping");
                        continue;
                    }

                    var productType = product.Type ?? string.Empty;
                    var productId = product.Id;

                    var commands = allCommands.Where(c => string.Equals(c.Type, productType, StringComparison.OrdinalIgnoreCase) && c.TypeId == productId).ToList();
                    if (commands == null || commands.Count == 0)
                    {
                        _logger.LogDebug("No commands defined for product {Type}/{Id}", productType, productId);
                        continue;
                    }

                    // mark that at least one product had commands defined
                    anyCommandsDefined = true;

                    List<ServerResponse> targets = new();
                    if (product.Server != null && product.Server.Id != 0)
                    {
                        try
                        {
                            var single = await _serverService.GetServerByIdAsync(product.Server.Id);
                            if (single != null) targets.Add(single);
                        }
                        catch (Exception ex)
                        {
                            _logger.LogWarning(ex, "Failed to load assigned server {Id} for product {Product}", product.Server.Id, product.Name);
                        }
                    }

                    if (targets.Count == 0)
                    {
                        targets.AddRange(allServers);
                    }

                    // repeat the set of commands for the product according to the quantity
                    for (int r = 0; r < repeat; r++)
                    {
                        foreach (var srv in targets)
                        {
                            if (srv == null) continue;
                            if (string.IsNullOrWhiteSpace(srv.RCONIP) || srv.RCONPort == 0 || string.IsNullOrWhiteSpace(srv.RCONPassword))
                            {
                                _logger.LogWarning("Skipping server {ServerName} ({Id}) because RCON config is incomplete", srv.Name, srv.Id);
                                continue;
                            }

                            try
                            {
                                IPAddress ip = null!;
                                try
                                {
                                    var addrs = await Dns.GetHostAddressesAsync(srv.RCONIP);
                                    ip = addrs.FirstOrDefault() ?? IPAddress.Parse(srv.RCONIP);
                                }
                                catch
                                {
                                    ip = IPAddress.Parse(srv.RCONIP);
                                }

                                _logger.LogInformation("Connecting to RCON {Ip}:{Port} (server: {Name})", ip, srv.RCONPort, srv.Name);
                                // Use a lightweight internal RCON implementation to avoid depending on package API changes
                                using var rcon = await RconClient.ConnectAndAuthAsync(ip, srv.RCONPort, srv.RCONPassword, _logger);
                                foreach (var cmd in commands)
                                {
                                    // Prepare template and replaced command text
                                    var template = cmd.CommandText ?? string.Empty;
                                    var text = template;
                                    try
                                    {
                                        if (!string.IsNullOrEmpty(sanitizedUsername))
                                        {
                                            _logger.LogDebug("Replacing %player% with '{Username}' in command for server {Server}", sanitizedUsername, srv.Name);
                                        }
                                        text = template.Replace("%player%", sanitizedUsername, StringComparison.OrdinalIgnoreCase);
                                    }
                                    catch (Exception exReplace)
                                    {
                                        _logger.LogWarning(exReplace, "Failed to replace %player% token in command text for server {Server}", srv.Name);
                                        text = template;
                                    }

                                    // Retry loop: attempt up to 3 times when response is empty or not accepted
                                    const int maxAttempts = 3;
                                    string? lastResp = null;
                                    Exception? lastException = null;
                                    bool accepted = false;

                                    for (int attempt = 1; attempt <= maxAttempts; attempt++)
                                    {
                                        try
                                        {
                                            _logger.LogInformation("Sending RCON to {Server} (attempt {Attempt}): {Cmd}", srv.Name, attempt, text);
                                            var resp = await rcon.SendCommandAsync(text);
                                            lastResp = resp;
                                            _logger.LogDebug("RCON response from {Server} (attempt {Attempt}): {Resp}", srv.Name, attempt, resp);

                                            // Acceptance checks
                                            var respLower = (resp ?? string.Empty).ToLowerInvariant();
                                            var replacedLower = (text ?? string.Empty).ToLowerInvariant();
                                            var sanitizedLower = (sanitizedUsername ?? string.Empty).ToLowerInvariant();

                                            if (!string.IsNullOrEmpty(respLower))
                                            {
                                                if (!string.IsNullOrEmpty(replacedLower) && respLower.Contains(replacedLower))
                                                {
                                                    accepted = true;
                                                }
                                                else
                                                {
                                                    var withoutName = replacedLower;
                                                    if (!string.IsNullOrEmpty(sanitizedLower))
                                                    {
                                                        withoutName = withoutName.Replace(sanitizedLower, string.Empty).Replace("  ", " ").Trim();
                                                    }

                                                    if (!string.IsNullOrEmpty(withoutName) && respLower.Contains(withoutName))
                                                    {
                                                        accepted = true;
                                                    }
                                                    else
                                                    {
                                                        var keywords = new[] { "given", "gave", "added", "ok", "success", "granted", "received" };
                                                        if (keywords.Any(k => respLower.Contains(k)))
                                                        {
                                                            accepted = true;
                                                        }
                                                    }
                                                }
                                            }

                                            // Record response/debug
                                            var existing = result.CommandResults.FirstOrDefault(cr => cr.CommandText == text);
                                            if (existing == null)
                                            {
                                                var newRes = new SentrySMP.Shared.DTOs.RconCommandResult
                                                {
                                                    CommandText = text,
                                                    Succeeded = accepted,
                                                    Response = resp
                                                };
                                                newRes.Debug.Add($"Server:{srv.Name}; Addr:{srv.RCONIP}:{srv.RCONPort}; Attempt:{attempt}; Response:{resp}");
                                                result.CommandResults.Add(newRes);
                                            }
                                            else
                                            {
                                                existing.Succeeded = existing.Succeeded || accepted;
                                                if (string.IsNullOrEmpty(existing.Response)) existing.Response = resp;
                                                else if (!string.IsNullOrEmpty(resp)) existing.Response += "\n" + resp;
                                                existing.Debug.Add($"Server:{srv.Name}; Addr:{srv.RCONIP}:{srv.RCONPort}; Attempt:{attempt}; Response:{resp}");
                                                if (accepted) existing.ErrorMessage = null;
                                            }

                                            if (accepted) break;
                                            _logger.LogWarning("RCON response did not meet acceptance criteria for server {Server} (attempt {Attempt}). Retrying...", srv.Name, attempt);
                                        }
                                        catch (Exception exCmd)
                                        {
                                            lastException = exCmd;
                                            _logger.LogError(exCmd, "Failed to send RCON command to server {Server} (attempt {Attempt})", srv.Name, attempt);

                                            var existing = result.CommandResults.FirstOrDefault(cr => cr.CommandText == text);
                                            if (existing == null)
                                            {
                                                var newFail = new SentrySMP.Shared.DTOs.RconCommandResult { CommandText = text, Succeeded = false, ErrorMessage = exCmd.Message };
                                                newFail.Debug.Add($"Server:{srv.Name}; Addr:{srv.RCONIP}:{srv.RCONPort}; Attempt:{attempt}; Exception:{exCmd}");
                                                result.CommandResults.Add(newFail);
                                            }
                                            else
                                            {
                                                if (!existing.Succeeded)
                                                {
                                                    existing.ErrorMessage = existing.ErrorMessage == null ? exCmd.Message : existing.ErrorMessage + "; " + exCmd.Message;
                                                    existing.Debug.Add($"Server:{srv.Name}; Addr:{srv.RCONIP}:{srv.RCONPort}; Attempt:{attempt}; Exception:{exCmd}");
                                                }
                                            }
                                            // continue to next attempt
                                        }
                                    }

                                    // Finalize per-command result after attempts
                                    var final = result.CommandResults.FirstOrDefault(cr => cr.CommandText == text);
                                    if (final == null)
                                    {
                                        var errMsg = lastException?.Message ?? "No response from server";
                                        var newFail = new SentrySMP.Shared.DTOs.RconCommandResult { CommandText = text, Succeeded = false, ErrorMessage = errMsg };
                                        newFail.Debug.Add($"Final result after {maxAttempts} attempts; LastException:{lastException}; LastResponse:{lastResp}");
                                        result.CommandResults.Add(newFail);
                                    }
                                    else if (!final.Succeeded)
                                    {
                                        final.ErrorMessage = final.ErrorMessage ?? (lastException?.Message ?? "No response from server");
                                        final.Debug.Add($"Final result after {maxAttempts} attempts; LastException:{lastException}; LastResponse:{lastResp}");
                                    }
                                }
                            }
                            catch (Exception exConn)
                            {
                                _logger.LogError(exConn, "Failed to connect to RCON on server {Server}", srv.Name);
                                // connection-level errors do not immediately mark commands as failed; we will record an error entry for each command
                                foreach (var cmd in commands)
                                {
                                    var text = cmd.CommandText ?? string.Empty;
                                    try
                                    {
                                        if (!string.IsNullOrEmpty(sanitizedUsername))
                                        {
                                            text = text.Replace("%player%", sanitizedUsername, StringComparison.OrdinalIgnoreCase);
                                        }
                                    }
                                    catch
                                    {
                                        // ignore replacement errors and use original text
                                    }

                                    var existing = result.CommandResults.FirstOrDefault(cr => cr.CommandText == text);
                                    if (existing == null)
                                    {
                                        result.CommandResults.Add(new SentrySMP.Shared.DTOs.RconCommandResult { CommandText = text, Succeeded = false, ErrorMessage = exConn.Message });
                                    }
                                    else
                                    {
                                        if (!existing.Succeeded)
                                        {
                                            existing.ErrorMessage = existing.ErrorMessage == null ? exConn.Message : existing.ErrorMessage + "; " + exConn.Message;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Error executing commands for product {Product}", product?.Name);
                    // mark all commands for this product as failed
                    var commands = allCommands.Where(c => string.Equals(c.Type, product?.Type ?? string.Empty, StringComparison.OrdinalIgnoreCase) && c.TypeId == (product?.Id ?? 0)).ToList();
                    foreach (var cmd in commands)
                    {
                        var text = cmd.CommandText ?? string.Empty;
                        try
                        {
                            if (!string.IsNullOrEmpty(sanitizedUsername))
                            {
                                text = text.Replace("%player%", sanitizedUsername, StringComparison.OrdinalIgnoreCase);
                            }
                        }
                        catch
                        {
                            // ignore replacement errors and use original text
                        }

                        var existing = result.CommandResults.FirstOrDefault(r => r.CommandText == text);
                        if (existing == null)
                        {
                            result.CommandResults.Add(new SentrySMP.Shared.DTOs.RconCommandResult { CommandText = text, Succeeded = false, ErrorMessage = ex.Message });
                        }
                        else
                        {
                            if (!existing.Succeeded)
                            {
                                existing.ErrorMessage = existing.ErrorMessage == null ? ex.Message : existing.ErrorMessage + "; " + ex.Message;
                            }
                        }
                    }
                }
            }

            // Finalize AllSucceeded:
            // - If caller provided no products, we returned early above with AllSucceeded=true.
            // - If none of the provided products had any commands defined, consider this a non-successful RCON run
            //   (we don't want to claim "commands were successfully sent" when nothing was attempted).
            // - Otherwise, require all recorded command results to be successful.
            if (!anyCommandsDefined)
            {
                result.AllSucceeded = false;
                // Add a helpful entry so UI can show why nothing happened.
                try
                {
                    if (result.CommandResults == null) result.CommandResults = new System.Collections.Generic.List<SentrySMP.Shared.DTOs.RconCommandResult>();
                    result.CommandResults.Add(new SentrySMP.Shared.DTOs.RconCommandResult
                    {
                        CommandText = "<NO_RCON_COMMANDS_FOUND>",
                        Succeeded = false,
                        ErrorMessage = "No RCON commands were defined for the purchased products."
                    });
                }
                catch { }
            }
            else if (result.CommandResults.Count == 0)
            {
                // We expected commands but none were recorded (treat as failure)
                result.AllSucceeded = false;
            }
            else
            {
                result.AllSucceeded = result.CommandResults.All(r => r.Succeeded);
            }

            return result;
        }

        // Minimal RCON client implementation (Source RCON protocol)
        private sealed class RconClient : IDisposable
        {
            private readonly TcpClient _tcp;
            private readonly NetworkStream _stream;
            private int _nextId = 1;

            private RconClient(TcpClient tcp)
            {
                _tcp = tcp;
                _stream = tcp.GetStream();
            }

            public static async Task<RconClient> ConnectAndAuthAsync(IPAddress ip, int port, string password, ILogger logger)
            {
                var tcp = new TcpClient();
                await tcp.ConnectAsync(ip, port);
                var client = new RconClient(tcp);

                // Authenticate
                // Note: Minecraft/Source RCON uses SERVERDATA_AUTH = 3
                var authResp = await client.SendPacketAsync(3, password); // 3 = SERVERDATA_AUTH
                if (authResp.Item1 == -1)
                {
                    tcp.Dispose();
                    throw new InvalidOperationException("RCON authentication failed: invalid password");
                }
                return client;
            }

            public async Task<string> SendCommandAsync(string command)
            {
                // Use SERVERDATA_EXECCOMMAND = 2 for executing commands
                var resp = await SendPacketAsync(2, command);
                return resp.Item2 ?? string.Empty;
            }

            // send a packet with type and payload, return (id, body)
            private async Task<(int, string?)> SendPacketAsync(int type, string payload)
            {
                var id = System.Threading.Interlocked.Increment(ref _nextId);
                var payloadBytes = System.Text.Encoding.UTF8.GetBytes(payload);
                var len = 4 + 4 + payloadBytes.Length + 2; // id + type + payload + 2 nulls

                var buffer = new byte[4 + len]; // length prefix + rest
                BinaryPrimitives.WriteInt32LittleEndian(buffer.AsSpan(0,4), len);
                BinaryPrimitives.WriteInt32LittleEndian(buffer.AsSpan(4,4), id);
                BinaryPrimitives.WriteInt32LittleEndian(buffer.AsSpan(8,4), type);
                Array.Copy(payloadBytes, 0, buffer, 12, payloadBytes.Length);
                // trailing two null bytes are already zero

                await _stream.WriteAsync(buffer, 0, buffer.Length);
                await _stream.FlushAsync();

                // read length
                var lenBuf = new byte[4];
                await ReadExactAsync(lenBuf, 0, 4);
                var respLen = BinaryPrimitives.ReadInt32LittleEndian(lenBuf);
                var respBuf = new byte[respLen];
                await ReadExactAsync(respBuf, 0, respLen);

                var respId = BinaryPrimitives.ReadInt32LittleEndian(respBuf.AsSpan(0,4));
                var respType = BinaryPrimitives.ReadInt32LittleEndian(respBuf.AsSpan(4,4));
                var strLen = respLen - 8 - 2;
                string? body = null;
                if (strLen > 0)
                {
                    body = System.Text.Encoding.UTF8.GetString(respBuf, 8, strLen);
                }
                return (respId, body);
            }

            private async Task ReadExactAsync(byte[] buffer, int offset, int count)
            {
                var read = 0;
                while (read < count)
                {
                    var r = await _stream.ReadAsync(buffer, offset + read, count - read);
                    if (r == 0) throw new InvalidOperationException("RCON connection closed");
                    read += r;
                }
            }

            public void Dispose()
            {
                try { _stream.Dispose(); } catch { }
                try { _tcp.Dispose(); } catch { }
            }
        }
    }
}
