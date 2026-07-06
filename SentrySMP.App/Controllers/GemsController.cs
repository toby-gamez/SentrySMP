using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class GemsController : ControllerBase
{
    private readonly IGemService _gemService;

    public GemsController(IGemService gemService)
    {
        _gemService = gemService;
    }

    [HttpGet]
    public async Task<ActionResult<IEnumerable<GemResponse>>> GetAll()
    {
        var result = await _gemService.GetAllGemsAsync();
        return Ok(result);
    }

    [HttpGet("by-server/{serverId}")]
    public async Task<ActionResult<IEnumerable<GemResponse>>> GetByServer(int serverId)
    {
        var result = await _gemService.GetGemsByServerIdAsync(serverId);
        return Ok(result);
    }

    [HttpGet("{id}")]
    public async Task<ActionResult<GemResponse>> GetById(int id)
    {
        var result = await _gemService.GetGemByIdAsync(id);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpPost]
    public async Task<ActionResult<GemResponse>> Create(CreateGemDto dto)
    {
        var result = await _gemService.CreateGemAsync(dto);
        return CreatedAtAction(nameof(GetById), new { id = result.Id }, result);
    }

    [HttpPut("{id}")]
    public async Task<ActionResult<GemResponse>> Update(int id, UpdateGemDto dto)
    {
        var result = await _gemService.UpdateGemAsync(id, dto);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpDelete("{id}")]
    public async Task<IActionResult> Delete(int id)
    {
        var success = await _gemService.DeleteGemAsync(id);
        if (!success) return NotFound();
        return NoContent();
    }
}
