using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class AnnouncementsController : ControllerBase
{
    private readonly IAnnouncementsService _announcementsService;
    private readonly ILogger<AnnouncementsController> _logger;

    public AnnouncementsController(IAnnouncementsService announcementsService, ILogger<AnnouncementsController> logger)
    {
        _announcementsService = announcementsService;
        _logger = logger;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IEnumerable<AnnouncementDto>>> GetAnnouncements()
    {
        try
        {
            var items = await _announcementsService.GetLatestAnnouncementsAsync();
            return Ok(items);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error while fetching announcements");
            return StatusCode(500, "Internal server error");
        }
    }
}
