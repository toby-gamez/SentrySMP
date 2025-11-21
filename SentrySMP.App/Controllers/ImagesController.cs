using System;
using System.Collections.Generic;
using System.IO;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Hosting;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.Interfaces;
using System.Threading.Tasks;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ImagesController : ControllerBase
{
    private readonly ILogger<ImagesController> _logger;
    private readonly IWebHostEnvironment _env;
    private readonly IImageService _imageService;

    public ImagesController(ILogger<ImagesController> logger, IWebHostEnvironment env, IImageService imageService)
    {
        _logger = logger;
        _env = env;
        _imageService = imageService;
    }

    [HttpGet]
    public async Task<IActionResult> GetAll()
    {
        var items = await _imageService.GetAllImagesAsync();
        return Ok(items);
    }

    [HttpPost("download/{fileName}")]
    public async Task<IActionResult> DownloadImage(string fileName, [FromQuery] string? remoteBase)
    {
        if (string.IsNullOrWhiteSpace(fileName)) return BadRequest("fileName required");
        var safeName = Path.GetFileName(fileName);
        var dto = await _imageService.SyncImagesAsync(new[] { safeName }, remoteBase);
        return Ok(dto);
    }

    public class DownloadUrlRequest
    {
        public string Url { get; set; } = string.Empty;
    }

    [HttpPost("download-by-url")]
    public async Task<IActionResult> DownloadByUrl([FromBody] DownloadUrlRequest req)
    {
        if (req == null || string.IsNullOrWhiteSpace(req.Url)) return BadRequest("url required");
        var dto = await _imageService.DownloadByUrlAsync(req.Url);
        return Ok(dto);
    }


    private static string GetContentTypeByExtension(string ext)
    {
        if (string.IsNullOrEmpty(ext)) return "application/octet-stream";
        ext = ext.ToLowerInvariant();
        return ext switch
        {
            ".png" => "image/png",
            ".jpg" or ".jpeg" => "image/jpeg",
            ".gif" => "image/gif",
            ".webp" => "image/webp",
            ".bmp" => "image/bmp",
            _ => "application/octet-stream",
        };
    }
}
