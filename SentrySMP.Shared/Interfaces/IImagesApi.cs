using Refit;
using SentrySMP.Shared.DTOs;
namespace SentrySMP.Shared.Interfaces;

public interface IImagesApi
{
    [Post("/api/images/upload")]
    Task<FileUploadResponse> UploadImageAsync([Query] string subDirectory, [AliasAs("file")] StreamPart stream);

    [Get("/api/images")]
    Task<IEnumerable<FileUploadResponse>> GetImagesAsync([Query] string? subDirectory = null);

    [Delete("/api/images/{subDirectory}/{fileName}")]
    Task DeleteImageAsync(string subDirectory, string fileName);
}
