using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class TeamRanksController : ControllerBase
{
    private readonly ITeamRankService _service;
    private readonly ILogger<TeamRanksController> _logger;

    public TeamRanksController(ITeamRankService service, ILogger<TeamRanksController> logger)
    {
        _service = service;
        _logger = logger;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<TeamRankDto>>> Get()
    {
        try
        {
            var r = await _service.GetAllAsync();
            return Ok(r);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting team ranks");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpGet("{id}")]
    [AllowAnonymous]
    public async Task<ActionResult<TeamRankDto>> Get(int id)
    {
        var ranks = await _service.GetAllAsync();
        var found = ranks.FirstOrDefault(x => x.Id == id);
        if (found == null) return NotFound();
        return Ok(found);
    }

    [HttpPost]
    [Authorize]
    public async Task<ActionResult<TeamRankDto>> Post([FromBody] TeamRankDto dto)
    {
        try
        {
            var created = await _service.CreateAsync(dto);
            return CreatedAtAction(nameof(Get), new { id = created.Id }, created);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error creating team rank");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPut("{id}")]
    [Authorize]
    public async Task<ActionResult<TeamRankDto>> Put(int id, [FromBody] TeamRankDto dto)
    {
        try
        {
            var updated = await _service.UpdateAsync(id, dto);
            return Ok(updated);
        }
        catch (KeyNotFoundException)
        {
            return NotFound();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error updating team rank");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpDelete("{id}")]
    [Authorize]
    public async Task<ActionResult> Delete(int id)
    {
        try
        {
            await _service.DeleteAsync(id);
            return NoContent();
        }
        catch (KeyNotFoundException)
        {
            return NotFound();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error deleting team rank");
            return StatusCode(500, "Internal server error");
        }
    }
}
