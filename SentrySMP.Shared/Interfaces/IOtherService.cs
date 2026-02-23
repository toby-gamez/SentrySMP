using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IOtherService
{
    Task<IEnumerable<OtherResponse>> GetAllOthersAsync();
    Task<IEnumerable<OtherResponse>> GetOthersByServerIdAsync(int serverId);
    Task<OtherResponse?> GetOtherByIdAsync(int id);
    Task<OtherResponse> CreateOtherAsync(CreateOtherDto createOtherDto);
    Task<OtherResponse?> UpdateOtherAsync(int id, UpdateOtherDto updateOtherDto);
    Task<bool> DeleteOtherAsync(int id);
}
