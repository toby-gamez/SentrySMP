using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/settings")]
public class SettingsController : ControllerBase
{
    private readonly SentryDbContext _context;
    private readonly ILogger<SettingsController> _logger;

    public SettingsController(SentryDbContext context, ILogger<SettingsController> logger)
    {
        _context = context;
        _logger = logger;
    }

    /// <summary>
    /// Get payment settings
    /// </summary>
    [HttpGet("payments")]
    [AllowAnonymous]
    public async Task<ActionResult<PaymentSettingsResponse>> GetPaymentSettings()
    {
        try
        {
            var settings = await _context.PaymentSettings.FirstOrDefaultAsync();
            
            // If no settings exist, create default ones
            if (settings == null)
            {
                settings = new PaymentSettings
                {
                    EnablePayments = true,
                    DisableStripe = false,
                    DisablePayPal = false,
                    UpdatedAt = DateTime.UtcNow
                };
                _context.PaymentSettings.Add(settings);
                await _context.SaveChangesAsync();
            }

            var response = new PaymentSettingsResponse
            {
                Id = settings.Id,
                EnablePayments = settings.EnablePayments,
                DisableStripe = settings.DisableStripe,
                DisablePayPal = settings.DisablePayPal,
                UpdatedAt = settings.UpdatedAt
            };

            return Ok(response);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while getting payment settings");
            return StatusCode(500, "Internal server error");
        }
    }

    /// <summary>
    /// Update payment settings (Admin only)
    /// </summary>
    [HttpPut("payments")]
    [Authorize]
    public async Task<ActionResult<PaymentSettingsResponse>> UpdatePaymentSettings([FromBody] UpdatePaymentSettingsRequest request)
    {
        try
        {
            var settings = await _context.PaymentSettings.FirstOrDefaultAsync();
            
            if (settings == null)
            {
                // Create new settings if none exist
                settings = new PaymentSettings
                {
                    EnablePayments = request.EnablePayments,
                    DisableStripe = request.DisableStripe,
                    DisablePayPal = request.DisablePayPal,
                    UpdatedAt = DateTime.UtcNow
                };
                _context.PaymentSettings.Add(settings);
            }
            else
            {
                // Update existing settings
                settings.EnablePayments = request.EnablePayments;
                settings.DisableStripe = request.DisableStripe;
                settings.DisablePayPal = request.DisablePayPal;
                settings.UpdatedAt = DateTime.UtcNow;
                _context.PaymentSettings.Update(settings);
            }

            await _context.SaveChangesAsync();

            var response = new PaymentSettingsResponse
            {
                Id = settings.Id,
                EnablePayments = settings.EnablePayments,
                DisableStripe = settings.DisableStripe,
                DisablePayPal = settings.DisablePayPal,
                UpdatedAt = settings.UpdatedAt
            };

            return Ok(response);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error occurred while updating payment settings");
            return StatusCode(500, "Internal server error");
        }
    }
}
