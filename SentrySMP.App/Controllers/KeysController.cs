using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class KeysController : ControllerBase
{
    private readonly IKeyService _keyService;
    private readonly ILogger<KeysController> _logger;

    public KeysController(IKeyService keyService, ILogger<KeysController> logger)
    {
        _keyService = keyService;
        _logger = logger;
    }

    /// <summary>
    /// Get all keys
    /// </summary>
    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<KeyResponse>>> GetKeys()
    {
        try
        {
            var keys = await _keyService.GetAllKeysAsync();
            return Ok(keys);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting keys");
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Get keys by server ID
    /// </summary>
    [HttpGet("server/{serverId}")]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<KeyResponse>>> GetKeysByServer(int serverId)
    {
        try
        {
            var keys = await _keyService.GetKeysByServerIdAsync(serverId);
            return Ok(keys);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting keys for server {ServerId}", serverId);
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Get key by ID
    /// </summary>
    [HttpGet("{id}")]
    [AllowAnonymous]
    public async Task<ActionResult<KeyResponse>> GetKey(int id)
    {
        try
        {
            var key = await _keyService.GetKeyByIdAsync(id);
            if (key == null)
                return NotFound($"Key with ID {id} not found");

            return Ok(key);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting key {KeyId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Create new key
    /// </summary>
    [HttpPost]
    [Authorize]
    public async Task<ActionResult<KeyResponse>> CreateKey([FromBody] CreateKeyDto createKeyDto)
    {
        try
        {
            if (!ModelState.IsValid)
                return BadRequest(ModelState);

            var key = await _keyService.CreateKeyAsync(createKeyDto);
            return CreatedAtAction(nameof(GetKey), new { id = key.Id }, key);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while creating key");
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while creating key");
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Update key
    /// </summary>
    [HttpPut("{id}")]
    [Authorize]
    public async Task<ActionResult<KeyResponse>> UpdateKey(int id, [FromBody] UpdateKeyDto updateKeyDto)
    {
        try
        {
            if (!ModelState.IsValid)
                return BadRequest(ModelState);

            var key = await _keyService.UpdateKeyAsync(id, updateKeyDto);
            if (key == null)
                return NotFound($"Key with ID {id} not found");

            return Ok(key);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while updating key {KeyId}", id);
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while updating key {KeyId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Delete key
    /// </summary>
    [HttpDelete("{id}")]
    [Authorize]
    public async Task<ActionResult> DeleteKey(int id)
    {
        try
        {
            var result = await _keyService.DeleteKeyAsync(id);
            if (!result)
                return NotFound($"Key with ID {id} not found");

            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while deleting key {KeyId}", id);
            return StatusCode(500, "Internal server error");
        }
    }
}