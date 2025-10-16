using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class BundlesController : ControllerBase
{
    private readonly IBundleService _bundleService;
    private readonly ILogger<BundlesController> _logger;

    public BundlesController(IBundleService bundleService, ILogger<BundlesController> logger)
    {
        _bundleService = bundleService;
        _logger = logger;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<BundleResponse>>> GetBundles()
    {
        try
        {
            var bundles = await _bundleService.GetAllBundlesAsync();
            return Ok(bundles);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting bundles");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpGet("server/{serverId}")]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<BundleResponse>>> GetBundlesByServer(int serverId)
    {
        try
        {
            var bundles = await _bundleService.GetBundlesByServerIdAsync(serverId);
            return Ok(bundles);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting bundles for server {ServerId}", serverId);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpGet("{id}")]
    [AllowAnonymous]
    public async Task<ActionResult<BundleResponse>> GetBundle(int id)
    {
        try
        {
            var bundle = await _bundleService.GetBundleByIdAsync(id);
            if (bundle == null) return NotFound($"Bundle with ID {id} not found");
            return Ok(bundle);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting bundle {BundleId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPost]
    [Authorize]
    public async Task<ActionResult<BundleResponse>> CreateBundle([FromBody] CreateBundleDto createBundleDto)
    {
        try
        {
            if (!ModelState.IsValid) return BadRequest(ModelState);

            var created = await _bundleService.CreateBundleAsync(createBundleDto);
            return CreatedAtAction(nameof(GetBundle), new { id = created.Id }, created);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while creating bundle");
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error creating bundle");
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpPut("{id}")]
    [Authorize]
    public async Task<ActionResult<BundleResponse>> UpdateBundle(int id, [FromBody] UpdateBundleDto updateBundleDto)
    {
        try
        {
            if (!ModelState.IsValid) return BadRequest(ModelState);

            var updated = await _bundleService.UpdateBundleAsync(id, updateBundleDto);
            if (updated == null) return NotFound($"Bundle with ID {id} not found");
            return Ok(updated);
        }
        catch (ArgumentException ex)
        {
            _logger.LogWarning(ex, "Invalid argument while updating bundle {BundleId}", id);
            return BadRequest(ex.Message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error updating bundle {BundleId}", id);
            return StatusCode(500, "Internal server error");
        }
    }

    [HttpDelete("{id}")]
    [Authorize]
    public async Task<ActionResult> DeleteBundle(int id)
    {
        try
        {
            var result = await _bundleService.DeleteBundleAsync(id);
            if (!result) return NotFound($"Bundle with ID {id} not found");
            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error deleting bundle {BundleId}", id);
            return StatusCode(500, "Internal server error");
        }
    }
}
