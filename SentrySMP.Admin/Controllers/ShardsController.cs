using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Admin.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ShardsController : ControllerBase
{
    private readonly IShardService _shardService;

    public ShardsController(IShardService shardService)
    {
        _shardService = shardService;
    }

    [HttpGet]
    public async Task<ActionResult<IEnumerable<ShardResponse>>> GetAll()
    {
        var result = await _shardService.GetAllShardsAsync();
        return Ok(result);
    }

    [HttpGet("by-server/{serverId}")]
    public async Task<ActionResult<IEnumerable<ShardResponse>>> GetByServer(int serverId)
    {
        var result = await _shardService.GetShardsByServerIdAsync(serverId);
        return Ok(result);
    }

    [HttpGet("{id}")]
    public async Task<ActionResult<ShardResponse>> GetById(int id)
    {
        var result = await _shardService.GetShardByIdAsync(id);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpPost]
    public async Task<ActionResult<ShardResponse>> Create(CreateShardDto dto)
    {
        var result = await _shardService.CreateShardAsync(dto);
        return CreatedAtAction(nameof(GetById), new { id = result.Id }, result);
    }

    [HttpPut("{id}")]
    public async Task<ActionResult<ShardResponse>> Update(int id, UpdateShardDto dto)
    {
        var result = await _shardService.UpdateShardAsync(id, dto);
        if (result == null) return NotFound();
        return Ok(result);
    }

    [HttpDelete("{id}")]
    public async Task<IActionResult> Delete(int id)
    {
        var success = await _shardService.DeleteShardAsync(id);
        if (!success) return NotFound();
        return NoContent();
    }
}
