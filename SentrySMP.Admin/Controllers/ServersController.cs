using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Admin.Controllers;

[Authorize]
[ApiController]
[Route("api/[controller]")]
public class ServersController : ControllerBase
{
    private readonly ISentryApi _api;
    private readonly ILogger<ServersController> _logger;

    public ServersController(ISentryApi api, ILogger<ServersController> logger)
    {
        _api = api;
        _logger = logger;
    }

    [HttpGet]
    public async Task<ActionResult<IEnumerable<ServerResponse>>> GetServers()
    {
        try
        {
            var servers = await _api.GetServersAsync();
            return Ok(servers);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting servers");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpGet("{id}")]
    public async Task<ActionResult<ServerResponse>> GetServer(int id)
    {
        try
        {
            var server = await _api.GetServerAsync(id);
            return Ok(server);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting server {Id}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPost]
    public async Task<ActionResult<ServerResponse>> CreateServer(CreateServerDto createServerDto)
    {
        try
        {
            var server = await _api.CreateServerAsync(createServerDto);
            return CreatedAtAction(nameof(GetServer), new { id = server.Id }, server);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error creating server");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPut("{id}")]
    public async Task<ActionResult<ServerResponse>> UpdateServer(int id, UpdateServerDto updateServerDto)
    {
        try
        {
            var server = await _api.UpdateServerAsync(id, updateServerDto);
            return Ok(server);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error updating server {Id}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpDelete("{id}")]
    public async Task<IActionResult> DeleteServer(int id)
    {
        try
        {
            await _api.DeleteServerAsync(id);
            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error deleting server {Id}", id);
            return StatusCode(500, "Internal server error");
        }
    }
}
