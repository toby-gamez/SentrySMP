using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class VoucherService : IVoucherService
{
    private readonly SentryDbContext _context;

    public VoucherService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<VoucherResponse>> GetAllVouchersAsync()
    {
        var vouchers = await _context.Vouchers
            .Include(v => v.Usages)
            .OrderByDescending(v => v.Id)
            .ToListAsync();
        return vouchers.Select(MapToResponse);
    }

    public async Task<VoucherResponse?> GetVoucherByIdAsync(int id)
    {
        var v = await _context.Vouchers.Include(x => x.Usages).FirstOrDefaultAsync(x => x.Id == id);
        return v == null ? null : MapToResponse(v);
    }

    public async Task<VoucherResponse> CreateVoucherAsync(CreateVoucherDto dto)
    {
        // Ensure code is uppercase
        dto.Code = dto.Code.Trim().ToUpperInvariant();

        if (await _context.Vouchers.AnyAsync(v => v.Code == dto.Code))
            throw new ArgumentException($"Voucher code '{dto.Code}' already exists.");

        var voucher = new Voucher
        {
            Code = dto.Code,
            Description = dto.Description,
            StartDate = dto.StartDate.ToUniversalTime(),
            ExpirationDate = dto.ExpirationDate.ToUniversalTime(),
            MaxUses = dto.MaxUses,
            DiscountPercent = dto.DiscountPercent,
            Scope = dto.Scope,
            ScopeCategory = dto.ScopeCategory,
            ScopeItemId = dto.ScopeItemId,
            IsActive = dto.IsActive,
            CurrentUses = 0
        };

        _context.Vouchers.Add(voucher);
        await _context.SaveChangesAsync();
        return MapToResponse(voucher);
    }

    public async Task<VoucherResponse?> UpdateVoucherAsync(int id, UpdateVoucherDto dto)
    {
        var voucher = await _context.Vouchers.Include(x => x.Usages).FirstOrDefaultAsync(x => x.Id == id);
        if (voucher == null) return null;

        var newCode = dto.Code.Trim().ToUpperInvariant();
        if (newCode != voucher.Code && await _context.Vouchers.AnyAsync(v => v.Code == newCode && v.Id != id))
            throw new ArgumentException($"Voucher code '{newCode}' already exists.");

        voucher.Code = newCode;
        voucher.Description = dto.Description;
        voucher.StartDate = dto.StartDate.ToUniversalTime();
        voucher.ExpirationDate = dto.ExpirationDate.ToUniversalTime();
        voucher.MaxUses = dto.MaxUses;
        voucher.DiscountPercent = dto.DiscountPercent;
        voucher.Scope = dto.Scope;
        voucher.ScopeCategory = dto.ScopeCategory;
        voucher.ScopeItemId = dto.ScopeItemId;
        voucher.IsActive = dto.IsActive;

        await _context.SaveChangesAsync();
        return MapToResponse(voucher);
    }

    public async Task<bool> DeleteVoucherAsync(int id)
    {
        var voucher = await _context.Vouchers.FindAsync(id);
        if (voucher == null) return false;
        _context.Vouchers.Remove(voucher);
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<ValidateVoucherResponse> ValidateVoucherAsync(ValidateVoucherRequest request)
    {
        var code = (request.Code ?? string.Empty).Trim().ToUpperInvariant();
        var voucher = await _context.Vouchers
            .Include(v => v.Usages)
            .FirstOrDefaultAsync(v => v.Code == code);

        if (voucher == null)
            return Invalid("Voucher code not found.");

        if (!voucher.IsActive)
            return Invalid("This voucher is no longer active.");

        var now = DateTime.UtcNow;
        if (now < voucher.StartDate)
            return Invalid($"This voucher is not yet valid (valid from {voucher.StartDate:yyyy-MM-dd}).");

        if (now > voucher.ExpirationDate)
            return Invalid("This voucher has expired.");

        if (voucher.MaxUses.HasValue && voucher.CurrentUses >= voucher.MaxUses.Value)
            return Invalid("This voucher has reached its maximum number of uses.");

        // Calculate discount amount from cart items
        decimal applicableTotal = 0;
        foreach (var item in request.Items)
        {
            bool applies = voucher.Scope switch
            {
                "All" => true,
                "Category" => string.Equals(item.Type, voucher.ScopeCategory, StringComparison.OrdinalIgnoreCase),
                "Item" => string.Equals(item.Type, voucher.ScopeCategory, StringComparison.OrdinalIgnoreCase)
                          && item.Id == voucher.ScopeItemId,
                _ => false
            };

            if (applies)
                applicableTotal += (decimal)(item.UnitPrice * item.Quantity);
        }

        // If there are no applicable items in the cart, do not claim the voucher's percent
        // (so UI won't display e.g. "50% discount applied" when amount is actually €0).
        var discountPercentToApply = applicableTotal > 0 ? voucher.DiscountPercent : 0m;
        var discountAmount = Math.Round(applicableTotal * discountPercentToApply / 100m, 2);

        return new ValidateVoucherResponse
        {
            IsValid = true,
            Message = discountPercentToApply > 0 ? $"{voucher.DiscountPercent}% discount applied!" : "No applicable items in cart",
            DiscountPercent = discountPercentToApply,
            DiscountAmount = discountAmount,
            Scope = voucher.Scope,
            ScopeCategory = voucher.ScopeCategory,
            ScopeItemId = voucher.ScopeItemId,
            Code = voucher.Code
        };
    }

    public async Task RecordVoucherUsageAsync(string code, string minecraftUsername)
    {
        code = (code ?? string.Empty).Trim().ToUpperInvariant();
        var voucher = await _context.Vouchers.FirstOrDefaultAsync(v => v.Code == code);
        if (voucher == null) return;

        var usage = new VoucherUsage
        {
            VoucherId = voucher.Id,
            MinecraftUsername = minecraftUsername,
            UsedAt = DateTime.UtcNow
        };
        voucher.CurrentUses++;
        _context.VoucherUsages.Add(usage);
        await _context.SaveChangesAsync();
    }

    private static ValidateVoucherResponse Invalid(string message) =>
        new() { IsValid = false, Message = message };

    private static VoucherResponse MapToResponse(Voucher v) => new()
    {
        Id = v.Id,
        Code = v.Code,
        Description = v.Description,
        StartDate = v.StartDate,
        ExpirationDate = v.ExpirationDate,
        MaxUses = v.MaxUses,
        CurrentUses = v.CurrentUses,
        DiscountPercent = v.DiscountPercent,
        Scope = v.Scope,
        ScopeCategory = v.ScopeCategory,
        ScopeItemId = v.ScopeItemId,
        IsActive = v.IsActive
    };
}
