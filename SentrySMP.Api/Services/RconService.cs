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

        public async Task ExecuteCommandsForProductsAsync(List<ProductResponse> products, string? username)
        {
            if (products == null || products.Count == 0)
            {
                _logger.LogDebug("No products provided for RCON execution.");
                return;
            }

            List<Domain.Entities.Command> allCommands;
            try
            {
                allCommands = await _commandService.GetAllAsync();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to load commands for RCON execution");
                return;
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

            foreach (var product in products)
            {
                try
                {
                    var productType = product.Type ?? string.Empty;
                    var productId = product.Id;

                    var commands = allCommands.Where(c => string.Equals(c.Type, productType, StringComparison.OrdinalIgnoreCase) && c.TypeId == productId).ToList();
                    if (commands == null || commands.Count == 0)
                    {
                        _logger.LogDebug("No commands defined for product {Type}/{Id}", productType, productId);
                        continue;
                    }

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
                                try
                                {
                                    var text = cmd.CommandText ?? string.Empty;
                                    if (!string.IsNullOrEmpty(username))
                                    {
                                        text = text.Replace("%player%", username, StringComparison.OrdinalIgnoreCase);
                                    }
                                    _logger.LogInformation("Sending RCON to {Server}: {Cmd}", srv.Name, text);
                                    var resp = await rcon.SendCommandAsync(text);
                                    _logger.LogDebug("RCON response from {Server}: {Resp}", srv.Name, resp);
                                }
                                catch (Exception exCmd)
                                {
                                    _logger.LogError(exCmd, "Failed to send RCON command to server {Server}", srv.Name);
                                }
                            }
                        }
                        catch (Exception exConn)
                        {
                            _logger.LogError(exConn, "Failed to connect to RCON on server {Server}", srv.Name);
                        }
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Error executing commands for product {Product}", product?.Name);
                }
            }
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
                var authResp = await client.SendPacketAsync(2, password); // 2 = SERVERDATA_AUTH
                if (authResp.Item1 == -1)
                {
                    tcp.Dispose();
                    throw new InvalidOperationException("RCON authentication failed: invalid password");
                }
                return client;
            }

            public async Task<string> SendCommandAsync(string command)
            {
                var resp = await SendPacketAsync(2 + 1, command); // 2 = auth, 3 = exec; use 3 for exec (SERVERDATA_EXECCOMMAND)
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
