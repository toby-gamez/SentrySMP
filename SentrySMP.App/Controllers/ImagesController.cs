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

    [HttpGet("{fileName}")]
    public IActionResult GetImage(string fileName)
    {
        if (string.IsNullOrWhiteSpace(fileName)) return BadRequest("fileName required");
        var safeName = Path.GetFileName(fileName);
        var webRoot = _env.WebRootPath ?? Path.Combine(Directory.GetCurrentDirectory(), "wwwroot");
        var uploads = Path.Combine(webRoot, "uploads", "keys");
        var path = Path.Combine(uploads, safeName);
        if (!System.IO.File.Exists(path)) return NotFound();
        var contentType = GetContentTypeByExtension(Path.GetExtension(safeName));
        var bytes = System.IO.File.ReadAllBytes(path);
        return File(bytes, contentType);
    }

    [HttpPost("sync")]
    public async Task<IActionResult> SyncImages([FromBody] List<string>? fileNames, [FromQuery] string? remoteBase)
    {
        if (fileNames == null || fileNames.Count == 0) return BadRequest("Provide a JSON array of file names in the request body.");
        var dto = await _imageService.SyncImagesAsync(fileNames, remoteBase);
        return Ok(dto);
    }
    
    [HttpPost("check-remote")]
    public async Task<IActionResult> CheckRemote([FromBody] List<string>? fileNames, [FromQuery] string? remoteBase)
    {
        if (fileNames == null || fileNames.Count == 0) return BadRequest("Provide a JSON array of file names in the request body.");
        var found = await _imageService.CheckRemoteExistsAsync(fileNames, remoteBase);
        return Ok(found);
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
