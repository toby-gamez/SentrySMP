using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class PurchaseTrackingService : IPurchaseTrackingService
{
    private readonly SentryDbContext _context;
    private readonly ILogger<PurchaseTrackingService> _logger;

    public PurchaseTrackingService(SentryDbContext context, ILogger<PurchaseTrackingService> logger)
    {
        _context = context;
        _logger = logger;
    }

    public async Task RecordPurchaseAsync(string username, string productType, int productId, int quantity)
    {
        if (string.IsNullOrWhiteSpace(username) || quantity <= 0)
            return;

        try
        {
            var existing = await _context.UserPurchaseRecords
                .FirstOrDefaultAsync(r => 
                    r.MinecraftUsername == username && 
                    r.ProductType == productType && 
                    r.ProductId == productId);

            if (existing != null)
            {
                existing.TotalQuantityPurchased += quantity;
                existing.LastPurchaseDate = DateTime.UtcNow;
                _context.UserPurchaseRecords.Update(existing);
            }
            else
            {
                var record = new UserPurchaseRecord
                {
                    MinecraftUsername = username,
                    ProductType = productType,
                    ProductId = productId,
                    TotalQuantityPurchased = quantity,
                    LastPurchaseDate = DateTime.UtcNow,
                    CreatedAt = DateTime.UtcNow
                };
                await _context.UserPurchaseRecords.AddAsync(record);
            }

            await _context.SaveChangesAsync();
            _logger.LogInformation("Recorded purchase: {Username} bought {Quantity}x {Type} ID={Id}", 
                username, quantity, productType, productId);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to record purchase for {Username}, {Type} ID={Id}", 
                username, productType, productId);
            throw;
        }
    }

    public async Task<int> GetTotalPurchasedAsync(string username, string productType, int productId)
    {
        if (string.IsNullOrWhiteSpace(username))
            return 0;

        try
        {
            var record = await _context.UserPurchaseRecords
                .FirstOrDefaultAsync(r => 
                    r.MinecraftUsername == username && 
                    r.ProductType == productType && 
                    r.ProductId == productId);

            return record?.TotalQuantityPurchased ?? 0;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get total purchased for {Username}, {Type} ID={Id}", 
                username, productType, productId);
            return 0;
        }
    }

    public async Task<int> GetGlobalPurchasedAsync(string productType, int productId)
    {
        try
        {
            var total = await _context.UserPurchaseRecords
                .Where(r => r.ProductType == productType && r.ProductId == productId)
                .SumAsync(r => r.TotalQuantityPurchased);

            return total;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get global purchased for {Type} ID={Id}", 
                productType, productId);
            return 0;
        }
    }

    public async Task<bool> CanUserPurchaseAsync(string username, ProductResponse product, int requestedQuantity)
    {
        if (product.GlobalMaxOrder == null || product.GlobalMaxOrder <= 0)
            return true; // No limit set

        if (string.IsNullOrWhiteSpace(username))
            return false;

        try
        {
            var globalPurchased = await GetGlobalPurchasedAsync(product.Type, product.Id);
            var totalAfterPurchase = globalPurchased + requestedQuantity;

            return totalAfterPurchase <= product.GlobalMaxOrder.Value;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to check if user can purchase {Type} ID={Id}", 
                product.Type, product.Id);
            return true; // Allow purchase on error to not block users
        }
    }

    public async Task<Dictionary<string, string>> ValidateCartLimitsAsync(string username, List<(ProductResponse product, int quantity)> cartItems)
    {
        var errors = new Dictionary<string, string>();

        if (string.IsNullOrWhiteSpace(username) || cartItems == null || !cartItems.Any())
            return errors;

        try
        {
            foreach (var (product, quantity) in cartItems)
            {
                if (product.GlobalMaxOrder == null || product.GlobalMaxOrder <= 0)
                    continue;

                var globalPurchased = await GetGlobalPurchasedAsync(product.Type, product.Id);
                var totalAfterPurchase = globalPurchased + quantity;

                if (totalAfterPurchase > product.GlobalMaxOrder.Value)
                {
                    var remaining = product.GlobalMaxOrder.Value - globalPurchased;
                    var key = $"{product.Type}_{product.Id}";
                    
                    if (remaining <= 0)
                    {
                        errors[key] = $"'{product.Name}' is sold out (limit: {product.GlobalMaxOrder}, sold: {globalPurchased}). Please remove it from your cart.";
                    }
                    else
                    {
                        errors[key] = $"Only {remaining} of '{product.Name}' available (limit: {product.GlobalMaxOrder}, already sold: {globalPurchased}). Please adjust the quantity in your cart.";
                    }
                }
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to validate cart limits for {Username}", username);
        }

        return errors;
    }
}
