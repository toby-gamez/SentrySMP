using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class BattlePassesController : ControllerBase
{
    private readonly IBattlePassService _service;
    private readonly ILogger<BattlePassesController> _logger;

    public BattlePassesController(IBattlePassService service, ILogger<BattlePassesController> logger)
    {
        _service = service;
        _logger = logger;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<BattlePassResponse>>> GetBattlePasses()
    {
        try
        {
            var items = await _service.GetAllBattlePassesAsync();
            return Ok(items);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting battlepasses");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpGet("server/{serverId}")]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<BattlePassResponse>>> GetByServer(int serverId)
    {
        try
        {
            var items = await _service.GetBattlePassesByServerIdAsync(serverId);
            return Ok(items);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting battlepasses for server {ServerId}", serverId);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpGet("{id}")]
    [AllowAnonymous]
    public async Task<ActionResult<BattlePassResponse>> GetBattlePass(int id)
    {
        try
        {
            var item = await _service.GetBattlePassByIdAsync(id);
            if (item == null) return NotFound($"BattlePass with ID {id} not found");
            return Ok(item);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting battlepass {Id}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPost]
    [Authorize]
    public async Task<ActionResult<BattlePassResponse>> CreateBattlePass([FromBody] CreateBattlePassDto createDto)
    {
        try
        {
            if (!ModelState.IsValid) return BadRequest(ModelState);
            var bp = await _service.CreateBattlePassAsync(createDto);
            return CreatedAtAction(nameof(GetBattlePass), new { id = bp.Id }, bp);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while creating battlepass");
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while creating battlepass");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPut("{id}")]
    [Authorize]
    public async Task<ActionResult<BattlePassResponse>> UpdateBattlePass(int id, [FromBody] UpdateBattlePassDto updateDto)
    {
        try
        {
            if (!ModelState.IsValid) return BadRequest(ModelState);
            var bp = await _service.UpdateBattlePassAsync(id, updateDto);
            if (bp == null) return NotFound($"BattlePass with ID {id} not found");
            return Ok(bp);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while updating battlepass {Id}", id);
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while updating battlepass {Id}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpDelete("{id}")]
    [Authorize]
    public async Task<ActionResult> DeleteBattlePass(int id)
    {
        try
        {
            var result = await _service.DeleteBattlePassAsync(id);
            if (!result) return NotFound($"BattlePass with ID {id} not found");
            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while deleting battlepass {Id}", id);
            return StatusCode(500, "Internal server error");
        }
    }
}
