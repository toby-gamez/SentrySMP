using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class VoucherResponse
{
    public int Id { get; set; }
    public string Code { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public DateTime StartDate { get; set; }
    public DateTime ExpirationDate { get; set; }
    public int? MaxUses { get; set; }
    public int CurrentUses { get; set; }
    public decimal DiscountPercent { get; set; }
    /// <summary>"All" | "Category" | "Item"</summary>
    public string Scope { get; set; } = "All";
    /// <summary>"Key" | "Rank" | "Bundle" | "Coin" | "Other" | "BattlePass"</summary>
    public string? ScopeCategory { get; set; }
    public int? ScopeItemId { get; set; }
    public bool IsActive { get; set; }
}

public class CreateVoucherDto
{
    [Required]
    [MaxLength(50)]
    public string Code { get; set; } = string.Empty;

    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;

    public DateTime StartDate { get; set; } = DateTime.UtcNow;

    public DateTime ExpirationDate { get; set; } = DateTime.UtcNow.AddMonths(1);

    public int? MaxUses { get; set; }

    [Range(0, 100)]
    public decimal DiscountPercent { get; set; }

    [Required]
    public string Scope { get; set; } = "All";

    public string? ScopeCategory { get; set; }

    public int? ScopeItemId { get; set; }

    public bool IsActive { get; set; } = true;
}

public class UpdateVoucherDto
{
    [Required]
    [MaxLength(50)]
    public string Code { get; set; } = string.Empty;

    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;

    public DateTime StartDate { get; set; }

    public DateTime ExpirationDate { get; set; }

    public int? MaxUses { get; set; }

    [Range(0, 100)]
    public decimal DiscountPercent { get; set; }

    [Required]
    public string Scope { get; set; } = "All";

    public string? ScopeCategory { get; set; }

    public int? ScopeItemId { get; set; }

    public bool IsActive { get; set; }
}

/// <summary>One line item from the cart, used when validating a voucher code.</summary>
public class VoucherCartItem
{
    /// <summary>Product type: "Key", "Rank", "Bundle", "Coin", "Other", "BattlePass"</summary>
    public string Type { get; set; } = string.Empty;
    public int Id { get; set; }
    public int Quantity { get; set; }
    /// <summary>Unit price after the product's own Sale discount has been applied.</summary>
    public double UnitPrice { get; set; }
}

public class ValidateVoucherRequest
{
    [Required]
    public string Code { get; set; } = string.Empty;

    public List<VoucherCartItem> Items { get; set; } = new();
}

public class ValidateVoucherResponse
{
    public bool IsValid { get; set; }
    public string? Message { get; set; }
    public decimal DiscountPercent { get; set; }
    /// <summary>Concrete EUR amount subtracted from the total.</summary>
    public decimal DiscountAmount { get; set; }
    public string? Scope { get; set; }
    public string? ScopeCategory { get; set; }
    public int? ScopeItemId { get; set; }
    /// <summary>The voucher code echoed back for UI display.</summary>
    public string? Code { get; set; }
}

public class RecordVoucherUsageRequest
{
    [Required]
    public string Code { get; set; } = string.Empty;
    public string MinecraftUsername { get; set; } = string.Empty;
}
