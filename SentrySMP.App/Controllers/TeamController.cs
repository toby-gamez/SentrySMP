using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using System.Text.Json;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class TeamController : ControllerBase
{
    private readonly ITeamService _teamService;
    private readonly ILogger<TeamController> _logger;

    public TeamController(ITeamService teamService, ILogger<TeamController> logger)
    {
        _teamService = teamService;
        _logger = logger;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<TeamResponseDto>> Get()
    {
        try
        {
            var data = await _teamService.GetTeamAsync();
            return Ok(data);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error reading team data");
            return StatusCode(500, "Failed to read team data");
        }
    }

    [HttpPost]
    [Authorize]
    public async Task<ActionResult<TeamResponseDto>> Save([FromBody] TeamResponseDto dto)
    {
        try
        {
            await _teamService.SaveTeamAsync(dto);
            return Ok(dto);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error saving team data");
            return StatusCode(500, "Failed to save team data");
        }
    }
}
