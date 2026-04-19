using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IVoucherService
{
    Task<IEnumerable<VoucherResponse>> GetAllVouchersAsync();
    Task<VoucherResponse?> GetVoucherByIdAsync(int id);
    Task<VoucherResponse> CreateVoucherAsync(CreateVoucherDto dto);
    Task<VoucherResponse?> UpdateVoucherAsync(int id, UpdateVoucherDto dto);
    Task<bool> DeleteVoucherAsync(int id);
    Task<ValidateVoucherResponse> ValidateVoucherAsync(ValidateVoucherRequest request);
    Task RecordVoucherUsageAsync(string code, string minecraftUsername);
}
