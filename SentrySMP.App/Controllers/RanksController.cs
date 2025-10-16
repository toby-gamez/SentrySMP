using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class RanksController : ControllerBase
{
    private readonly IRankService _rankService;
    private readonly ILogger<RanksController> _logger;

    public RanksController(IRankService rankService, ILogger<RanksController> logger)
    {
        _rankService = rankService;
        _logger = logger;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<RankResponse>>> GetRanks()
    {
        try
        {
            var ranks = await _rankService.GetAllRanksAsync();
            return Ok(ranks);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting ranks");
            return StatusCode(500, "Internal server error");
        }
    }

    // Ranks are global now; no server-specific endpoint


    [HttpGet("{id}")]
    [AllowAnonymous]
    public async Task<ActionResult<RankResponse>> GetRank(int id)
    {
        try
        {
            var rank = await _rankService.GetRankByIdAsync(id);
            if (rank == null)
                return NotFound($"Rank with ID {id} not found");

            return Ok(rank);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting rank {RankId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPost]
    [Authorize]
    public async Task<ActionResult<RankResponse>> CreateRank([FromBody] CreateRankDto createRankDto)
    {
        try
        {
            if (!ModelState.IsValid)
                return BadRequest(ModelState);

            var rank = await _rankService.CreateRankAsync(createRankDto);
            return CreatedAtAction(nameof(GetRank), new { id = rank.Id }, rank);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while creating rank");
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while creating rank");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPut("{id}")]
    [Authorize]
    public async Task<ActionResult<RankResponse>> UpdateRank(int id, [FromBody] UpdateRankDto updateRankDto)
    {
        try
        {
            if (!ModelState.IsValid)
                return BadRequest(ModelState);

            var rank = await _rankService.UpdateRankAsync(id, updateRankDto);
            if (rank == null)
                return NotFound($"Rank with ID {id} not found");

            return Ok(rank);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while updating rank {RankId}", id);
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while updating rank {RankId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpDelete("{id}")]
    [Authorize]
    public async Task<ActionResult> DeleteRank(int id)
    {
        try
        {
            var result = await _rankService.DeleteRankAsync(id);
            if (!result)
                return NotFound($"Rank with ID {id} not found");

            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while deleting rank {RankId}", id);
            return StatusCode(500, "Internal server error");
        }
    }
}
