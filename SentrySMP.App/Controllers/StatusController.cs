using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class StatusController : ControllerBase
{
    private readonly IStatusService _statusService;
    private readonly ILogger<StatusController> _logger;

    public StatusController(IStatusService statusService, ILogger<StatusController> logger)
    {
        _statusService = statusService;
        _logger = logger;
    }

    /// <summary>
    /// Get approximate Discord member count
    /// </summary>
    [HttpGet("discord")]
    [AllowAnonymous]
    public async Task<ActionResult<int?>> GetDiscordMembers()
    {
        try
        {
            var members = await _statusService.GetDiscordMembersAsync();
            return Ok(new { total = members });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error while fetching Discord members");
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Get current Minecraft online players
    /// </summary>
    [HttpGet("mc")]
    [AllowAnonymous]
    public async Task<ActionResult<int?>> GetMcPlayers()
    {
        try
        {
            var players = await _statusService.GetMcPlayersAsync();
            return Ok(new { players });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error while fetching Minecraft players");
            return StatusCode(500, "Internal server error");
        }
    }
}
