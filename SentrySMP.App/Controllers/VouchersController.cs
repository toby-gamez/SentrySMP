using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class VouchersController : ControllerBase
{
    private readonly IVoucherService _voucherService;
    private readonly ILogger<VouchersController> _logger;

    public VouchersController(IVoucherService voucherService, ILogger<VouchersController> logger)
    {
        _voucherService = voucherService;
        _logger = logger;
    }

    [HttpGet]
    [Authorize]
    public async Task<ActionResult<IEnumerable<VoucherResponse>>> GetAll()
    {
        try { return Ok(await _voucherService.GetAllVouchersAsync()); }
        catch (Exception ex) { _logger.LogError(ex, "Error getting vouchers"); return StatusCode(500, "Internal server error"); }
    }

    [HttpGet("{id:int}")]
    [Authorize]
    public async Task<ActionResult<VoucherResponse>> GetById(int id)
    {
        try
        {
            var v = await _voucherService.GetVoucherByIdAsync(id);
            return v == null ? NotFound() : Ok(v);
        }
        catch (Exception ex) { _logger.LogError(ex, "Error getting voucher {Id}", id); return StatusCode(500, "Internal server error"); }
    }

    /// <summary>Returns voucher info by code/name — anonymous.</summary>
    [HttpGet("{name}")]
    [AllowAnonymous]
    public async Task<ActionResult<VoucherResponse>> GetByName(string name)
    {
        try
        {
            var v = await _voucherService.GetVoucherByNameAsync(name);
            return v == null ? NotFound() : Ok(v);
        }
        catch (Exception ex) { _logger.LogError(ex, "Error getting voucher by name {Name}", name); return StatusCode(500, "Internal server error"); }
    }

    [HttpPost]
    [Authorize]
    public async Task<ActionResult<VoucherResponse>> Create([FromBody] CreateVoucherDto dto)
    {
        if (!ModelState.IsValid) return BadRequest(ModelState);
        try
        {
            var v = await _voucherService.CreateVoucherAsync(dto);
            return CreatedAtAction(nameof(GetById), new { id = v.Id }, v);
        }
        catch (ArgumentException ex) { return BadRequest(ex.Message); }
        catch (Exception ex) { _logger.LogError(ex, "Error creating voucher"); return StatusCode(500, "Internal server error"); }
    }

    [HttpPut("{id:int}")]
    [Authorize]
    public async Task<ActionResult<VoucherResponse>> Update(int id, [FromBody] UpdateVoucherDto dto)
    {
        if (!ModelState.IsValid) return BadRequest(ModelState);
        try
        {
            var v = await _voucherService.UpdateVoucherAsync(id, dto);
            return v == null ? NotFound() : Ok(v);
        }
        catch (ArgumentException ex) { return BadRequest(ex.Message); }
        catch (Exception ex) { _logger.LogError(ex, "Error updating voucher {Id}", id); return StatusCode(500, "Internal server error"); }
    }

    [HttpDelete("{id:int}")]
    [Authorize]
    public async Task<ActionResult> Delete(int id)
    {
        try
        {
            var result = await _voucherService.DeleteVoucherAsync(id);
            return result ? NoContent() : NotFound();
        }
        catch (Exception ex) { _logger.LogError(ex, "Error deleting voucher {Id}", id); return StatusCode(500, "Internal server error"); }
    }

    /// <summary>Called from checkout — anonymous, validates a code against the cart.</summary>
    [HttpPost("validate")]
    [AllowAnonymous]
    public async Task<ActionResult<ValidateVoucherResponse>> Validate([FromBody] ValidateVoucherRequest request)
    {
        if (!ModelState.IsValid) return BadRequest(ModelState);
        try { return Ok(await _voucherService.ValidateVoucherAsync(request)); }
        catch (Exception ex) { _logger.LogError(ex, "Error validating voucher"); return StatusCode(500, "Internal server error"); }
    }

    /// <summary>Called after a successful payment to record the voucher usage.</summary>
    [HttpPost("record-usage")]
    [AllowAnonymous]
    public async Task<ActionResult> RecordUsage([FromBody] RecordVoucherUsageRequest request)
    {
        if (string.IsNullOrWhiteSpace(request.Code)) return BadRequest("Code is required.");
        try
        {
            await _voucherService.RecordVoucherUsageAsync(request.Code, request.MinecraftUsername);
            return NoContent();
        }
        catch (Exception ex) { _logger.LogError(ex, "Error recording voucher usage"); return StatusCode(500, "Internal server error"); }
    }
}
