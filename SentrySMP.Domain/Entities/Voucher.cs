using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Domain.Entities;

public class Voucher
{
    public int Id { get; set; }

    [Required]
    [MaxLength(50)]
    public string Code { get; set; } = string.Empty;

    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;

    public DateTime StartDate { get; set; }

    public DateTime ExpirationDate { get; set; }

    /// <summary>null = unlimited</summary>
    public int? MaxUses { get; set; }

    public int CurrentUses { get; set; } = 0;

    /// <summary>0–100</summary>
    public decimal DiscountPercent { get; set; }

    /// <summary>"All" | "Category" | "Item"</summary>
    [Required]
    [MaxLength(20)]
    public string Scope { get; set; } = "All";

    /// <summary>"Key" | "Rank" | "Bundle" | "Gem" | "Other" | "BattlePass" — used when Scope is Category or Item</summary>
    [MaxLength(30)]
    public string? ScopeCategory { get; set; }

    /// <summary>Specific product ID — used when Scope is Item</summary>
    public int? ScopeItemId { get; set; }

    public bool IsActive { get; set; } = true;

    public virtual ICollection<VoucherUsage> Usages { get; set; } = new List<VoucherUsage>();
}
