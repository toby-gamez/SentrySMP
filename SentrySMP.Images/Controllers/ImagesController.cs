using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Images.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ImagesController : ControllerBase
{
    private readonly ILogger<ImagesController> _logger;
    private readonly IWebHostEnvironment _env;

    private static readonly string[] AllowedContentTypes = { "image/jpeg", "image/jpg", "image/png", "image/webp", "image/gif" };
    private const long MaxFileSize = 10 * 1024 * 1024; // 10MB

        public ImagesController(ILogger<ImagesController> logger, IWebHostEnvironment env)
    {
        _logger = logger;
        _env = env;
    }

    [HttpPost("upload")]
    [Authorize(AuthenticationSchemes = "BasicAuth")]
        public async Task<ActionResult<FileUploadResponse>> UploadImage(IFormFile file, [FromQuery] string? subDirectory = "keys")
    {
        if (file == null || file.Length == 0)
            return BadRequest(new { error = "No file provided" });

        if (file.Length > MaxFileSize)
            return BadRequest(new { error = $"File too large (max {MaxFileSize / (1024 * 1024)}MB)" });

        if (!AllowedContentTypes.Contains(file.ContentType?.ToLowerInvariant()))
            return BadRequest(new { error = "Unsupported file type" });

        var ext = Path.GetExtension(file.FileName).ToLowerInvariant();
        var uniqueName = $"{Guid.NewGuid()}{ext}";

        var webRoot = !string.IsNullOrWhiteSpace(_env.WebRootPath) ? _env.WebRootPath : Path.Combine(_env.ContentRootPath, "wwwroot");
        var uploadDir = Path.Combine(webRoot, "uploads", string.IsNullOrWhiteSpace(subDirectory) ? "keys" : subDirectory);
        if (!Directory.Exists(uploadDir)) Directory.CreateDirectory(uploadDir);

        var filePath = Path.Combine(uploadDir, uniqueName);
        await using var fs = new FileStream(filePath, FileMode.Create);
        await file.CopyToAsync(fs);

        var baseUrl = Request.Scheme + "://" + Request.Host;
        var url = $"{baseUrl}/uploads/{(string.IsNullOrWhiteSpace(subDirectory) ? "keys" : subDirectory)}/{Uri.EscapeDataString(uniqueName)}";

        _logger.LogInformation("Uploaded image {Original} as {Stored}", file.FileName, uniqueName);

        return Ok(new FileUploadResponse
        {
            FileName = uniqueName,
            OriginalFileName = file.FileName,
            Url = url,
            Size = file.Length,
            ContentType = file.ContentType
        });
    }

    [HttpGet]
    [AllowAnonymous]
    public ActionResult<IEnumerable<FileUploadResponse>> GetImages([FromQuery] string? subDirectory = "keys")
    {
        var webRoot = !string.IsNullOrWhiteSpace(_env.WebRootPath) ? _env.WebRootPath : Path.Combine(_env.ContentRootPath, "wwwroot");
        var uploadDir = Path.Combine(webRoot, "uploads", string.IsNullOrWhiteSpace(subDirectory) ? "keys" : subDirectory);
        if (!Directory.Exists(uploadDir)) return Ok(Enumerable.Empty<FileUploadResponse>());

        var baseUrl = Request.Scheme + "://" + Request.Host;
        var files = Directory.GetFiles(uploadDir)
            .Select(p => new FileInfo(p))
            .Select(fi => new FileUploadResponse
            {
                FileName = fi.Name,
                OriginalFileName = fi.Name,
                Url = $"{baseUrl}/uploads/{(string.IsNullOrWhiteSpace(subDirectory) ? "keys" : subDirectory)}/{Uri.EscapeDataString(fi.Name)}",
                Size = fi.Length,
                ContentType = GetContentType(fi.Name)
            })
            .ToList();

        return Ok(files);
    }

    [HttpDelete("{subDirectory}/{fileName}")]
    [Authorize(AuthenticationSchemes = "BasicAuth")]
    public ActionResult DeleteImage(string subDirectory, string fileName)
    {
        if (string.IsNullOrWhiteSpace(fileName)) return BadRequest(new { error = "fileName required" });

        var webRoot = !string.IsNullOrWhiteSpace(_env.WebRootPath) ? _env.WebRootPath : Path.Combine(_env.ContentRootPath, "wwwroot");
        var filePath = Path.Combine(webRoot, "uploads", subDirectory ?? "keys", fileName);
        if (!System.IO.File.Exists(filePath)) return NotFound(new { error = "Not found" });

        System.IO.File.Delete(filePath);
        return Ok(new { message = "Deleted" });
    }

    private static string GetContentType(string fileName)
    {
        var ext = Path.GetExtension(fileName).ToLowerInvariant();
        return ext switch
        {
            ".jpg" or ".jpeg" => "image/jpeg",
            ".png" => "image/png",
            ".gif" => "image/gif",
            ".webp" => "image/webp",
            _ => "application/octet-stream"
        };
    }
}
