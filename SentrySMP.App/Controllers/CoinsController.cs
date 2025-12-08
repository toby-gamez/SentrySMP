using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class CoinsController : ControllerBase
{
    private readonly ICoinService _coinService;

    public CoinsController(ICoinService coinService)
    {
        _coinService = coinService;
    }

    [HttpGet]
    public async Task<ActionResult<IEnumerable<CoinResponse>>> GetAll()
    {
        var result = await _coinService.GetAllCoinsAsync();
        return Ok(result);
    }

    [HttpGet("by-server/{serverId}")]
    public async Task<ActionResult<IEnumerable<CoinResponse>>> GetByServer(int serverId)
    {
        var result = await _coinService.GetCoinsByServerIdAsync(serverId);
        return Ok(result);
    }

    [HttpGet("{id}")]
    public async Task<ActionResult<CoinResponse>> GetById(int id)
    {
        var result = await _coinService.GetCoinByIdAsync(id);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpPost]
    public async Task<ActionResult<CoinResponse>> Create(CreateCoinDto dto)
    {
        var result = await _coinService.CreateCoinAsync(dto);
        return CreatedAtAction(nameof(GetById), new { id = result.Id }, result);
    }

    [HttpPut("{id}")]
    public async Task<ActionResult<CoinResponse>> Update(int id, UpdateCoinDto dto)
    {
        var result = await _coinService.UpdateCoinAsync(id, dto);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpDelete("{id}")]
    public async Task<IActionResult> Delete(int id)
    {
        var success = await _coinService.DeleteCoinAsync(id);
        if (!success) return NotFound();
        return NoContent();
    }
}
