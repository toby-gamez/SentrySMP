using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ServersController : ControllerBase
{
    private readonly IServerService _serverService;
    private readonly ILogger<ServersController> _logger;

    public ServersController(IServerService serverService, ILogger<ServersController> logger)
    {
        _serverService = serverService;
        _logger = logger;
    }

    /// <summary>
    /// Get all servers
    /// </summary>
    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<ServerResponse>>> GetServers()
    {
        try
        {
            var servers = await _serverService.GetAllServersAsync();
            return Ok(servers);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting servers");
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Get server by ID
    /// </summary>
    [HttpGet("{id}")]
    [AllowAnonymous]
    public async Task<ActionResult<ServerResponse>> GetServer(int id)
    {
        try
        {
            var server = await _serverService.GetServerByIdAsync(id);
            if (server == null)
                return NotFound($"Server with ID {id} not found");

            return Ok(server);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting server {ServerId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Create new server
    /// </summary>
    [HttpPost]
    [Authorize]
    public async Task<ActionResult<ServerResponse>> CreateServer([FromBody] CreateServerDto createServerDto)
    {
        try
        {
            if (!ModelState.IsValid)
                return BadRequest(ModelState);

            var server = await _serverService.CreateServerAsync(createServerDto);
            return CreatedAtAction(nameof(GetServer), new { id = server.Id }, server);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while creating server");
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Update server
    /// </summary>
    [HttpPut("{id}")]
    [Authorize]
    public async Task<ActionResult<ServerResponse>> UpdateServer(int id, [FromBody] UpdateServerDto updateServerDto)
    {
        try
        {
            if (!ModelState.IsValid)
                return BadRequest(ModelState);

            var server = await _serverService.UpdateServerAsync(id, updateServerDto);
            if (server == null)
                return NotFound($"Server with ID {id} not found");

            return Ok(server);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while updating server {ServerId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Delete server
    /// </summary>
    [HttpDelete("{id}")]
    [Authorize]
    public async Task<ActionResult> DeleteServer(int id)
    {
        try
        {
            var result = await _serverService.DeleteServerAsync(id);
            if (!result)
                return NotFound($"Server with ID {id} not found");

            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while deleting server {ServerId}", id);
            return StatusCode(500, "Internal server error");
        }
    }
}