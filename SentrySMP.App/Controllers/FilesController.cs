using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.App.Authentication;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Controllers;

[ApiController]
[Route("api/[controller]")]
[Authorize(AuthenticationSchemes = BasicAuthConstants.Scheme)]
public class FilesController : ControllerBase
{
    private readonly ILogger<FilesController> _logger;
    private readonly IWebHostEnvironment _environment;

    // Allowed file types and max size
    private static readonly string[] AllowedExtensions = { ".jpg", ".jpeg", ".png", ".webp" };
    private const long MaxFileSize = 5 * 1024 * 1024; // 5MB

    public FilesController(ILogger<FilesController> logger, IWebHostEnvironment environment)
    {
        _logger = logger;
        _environment = environment;
    }

    /// <summary>
    /// Upload image for keys
    /// </summary>
    [HttpPost("upload")]
    public async Task<ActionResult<FileUploadResponse>> UploadImage(IFormFile file)
    {
        try
        {
            // Validate file
            if (file == null || file.Length == 0)
                return BadRequest("No file provided");

            if (file.Length > MaxFileSize)
                return BadRequest($"File size exceeds maximum allowed size of {MaxFileSize / (1024 * 1024)}MB");

            var extension = Path.GetExtension(file.FileName).ToLowerInvariant();
            if (!AllowedExtensions.Contains(extension))
                return BadRequest($"File type {extension} is not allowed. Allowed types: {string.Join(", ", AllowedExtensions)}");

            // Generate unique filename
            var uniqueFileName = $"{Guid.NewGuid()}{extension}";
            
            // Create upload directory if it doesn't exist
            var uploadDir = Path.Combine(_environment.WebRootPath, "uploads", "keys");
            if (!Directory.Exists(uploadDir))
            {
                Directory.CreateDirectory(uploadDir);
                _logger.LogInformation("Created upload directory: {UploadDir}", uploadDir);
            }

            // Save file
            var filePath = Path.Combine(uploadDir, uniqueFileName);
            await using var stream = new FileStream(filePath, FileMode.Create);
            await file.CopyToAsync(stream);

            // Generate full URL path (including base address for cross-origin access)
            var baseUrl = $"{Request.Scheme}://{Request.Host}";
            var urlPath = $"{baseUrl}/uploads/keys/{uniqueFileName}";

            _logger.LogInformation("Successfully uploaded file {OriginalFileName} as {UniqueFileName}", 
                file.FileName, uniqueFileName);

            return Ok(new FileUploadResponse
            {
                FileName = uniqueFileName,
                OriginalFileName = file.FileName,
                Url = urlPath,
                Size = file.Length,
                ContentType = file.ContentType
            });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error uploading file");
            return StatusCode(500, "Internal server error occurred while uploading file");
        }
    }

    /// <summary>
    /// Delete uploaded image
    /// </summary>
    [HttpDelete("{fileName}")]
    public ActionResult DeleteImage(string fileName)
    {
        try
        {
            if (string.IsNullOrEmpty(fileName))
                return BadRequest("Filename is required");

            // Security check - ensure filename doesn't contain path traversal
            if (fileName.Contains("..") || fileName.Contains("/") || fileName.Contains("\\"))
                return BadRequest("Invalid filename");

            var filePath = Path.Combine(_environment.WebRootPath, "uploads", "keys", fileName);
            
            if (!System.IO.File.Exists(filePath))
                return NotFound("File not found");

            System.IO.File.Delete(filePath);
            
            _logger.LogInformation("Successfully deleted file {FileName}", fileName);
            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error deleting file {FileName}", fileName);
            return StatusCode(500, "Internal server error occurred while deleting file");
        }
    }
}

