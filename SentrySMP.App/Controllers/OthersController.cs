using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class OthersController : ControllerBase
{
    private readonly IOtherService _otherService;

    public OthersController(IOtherService otherService)
    {
        _otherService = otherService;
    }

    [HttpGet]
    public async Task<ActionResult<IEnumerable<OtherResponse>>> GetAll()
    {
        var result = await _otherService.GetAllOthersAsync();
        return Ok(result);
    }

    [HttpGet("by-server/{serverId}")]
    public async Task<ActionResult<IEnumerable<OtherResponse>>> GetByServer(int serverId)
    {
        var result = await _otherService.GetOthersByServerIdAsync(serverId);
        return Ok(result);
    }

    [HttpGet("{id}")]
    public async Task<ActionResult<OtherResponse>> GetById(int id)
    {
        var result = await _otherService.GetOtherByIdAsync(id);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpPost]
    public async Task<ActionResult<OtherResponse>> Create(CreateOtherDto dto)
    {
        var result = await _otherService.CreateOtherAsync(dto);
        return CreatedAtAction(nameof(GetById), new { id = result.Id }, result);
    }

    [HttpPut("{id}")]
    public async Task<ActionResult<OtherResponse>> Update(int id, UpdateOtherDto dto)
    {
        var result = await _otherService.UpdateOtherAsync(id, dto);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpDelete("{id}")]
    public async Task<IActionResult> Delete(int id)
    {
        var success = await _otherService.DeleteOtherAsync(id);
        if (!success) return NotFound();
        return NoContent();
    }
}
