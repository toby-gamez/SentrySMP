using System;
using System.Net;
using System.Threading.Tasks;
using CoreRCON;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Logging;

namespace SentrySMP.Api.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class RconDebugController : ControllerBase
    {
        private readonly ILogger<RconDebugController> _logger;

        public RconDebugController(ILogger<RconDebugController> logger)
        {
            _logger = logger;
        }

        // Example: GET /api/rcondebug/test?ip=5.83.128.32&port=25568&password=539871&command=give%20Taneq%20diamond%201
        [HttpGet("test")]
        public async Task<IActionResult> Test(string ip, int port, string password, string command)
        {
            if (string.IsNullOrWhiteSpace(ip) || port == 0 || string.IsNullOrWhiteSpace(password) || string.IsNullOrWhiteSpace(command))
            {
                return BadRequest("Missing ip/port/password/command parameters");
            }

            try
            {
                var parsed = IPAddress.Parse(ip);
                var rcon = new RCON(parsed, (ushort)port, password);

                var connectTask = rcon.ConnectAsync();
                var connectTimeoutMs = 5000;
                var completedConnect = await Task.WhenAny(connectTask, Task.Delay(connectTimeoutMs));
                if (completedConnect != connectTask)
                {
                    try { rcon.Dispose(); } catch { }
                    _logger.LogWarning("RCON debug connect timed out for {Ip}:{Port}", ip, port);
                    return StatusCode(504, "connect timed out");
                }
                await connectTask;

                var sendTask = rcon.SendCommandAsync(command);
                var sendTimeoutMs = 5000;
                var completedSend = await Task.WhenAny(sendTask, Task.Delay(sendTimeoutMs));
                if (completedSend != sendTask)
                {
                    try { rcon.Dispose(); } catch { }
                    _logger.LogWarning("RCON debug send timed out for {Ip}:{Port}", ip, port);
                    return StatusCode(504, "send timed out");
                }

                var resp = await sendTask;
                try { rcon.Dispose(); } catch { }
                return Ok(new { response = resp });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "RCON debug failed");
                return StatusCode(500, ex.Message);
            }
        }
    }
}
