using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IPurchaseTrackingService
{
    /// <summary>
    /// Records a purchase for a user (updates or creates UserPurchaseRecord)
    /// </summary>
    Task RecordPurchaseAsync(string username, string productType, int productId, int quantity);
    
    /// <summary>
    /// Gets the total quantity purchased by a user for a specific product
    /// </summary>
    Task<int> GetTotalPurchasedAsync(string username, string productType, int productId);
    
    /// <summary>
    /// Gets the total quantity purchased globally (all users) for a specific product
    /// </summary>
    Task<int> GetGlobalPurchasedAsync(string productType, int productId);
    
    /// <summary>
    /// Checks if user can purchase the requested quantity based on GlobalMaxOrder limit
    /// </summary>
    Task<bool> CanUserPurchaseAsync(string username, ProductResponse product, int requestedQuantity);
    
    /// <summary>
    /// Validates entire cart against GlobalMaxOrder limits for a user
    /// Returns dictionary with product keys (Type_Id) and error messages
    /// </summary>
    Task<Dictionary<string, string>> ValidateCartLimitsAsync(string username, List<(ProductResponse product, int quantity)> cartItems);
}
