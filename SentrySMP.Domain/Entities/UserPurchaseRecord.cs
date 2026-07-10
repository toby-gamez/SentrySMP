using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace SentrySMP.Domain.Entities;

/// <summary>
/// Tracks the total quantity of each product purchased by each user
/// </summary>
public class UserPurchaseRecord
{
    [Key]
    public int Id { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string MinecraftUsername { get; set; } = string.Empty;
    
    [Required]
    [MaxLength(50)]
    public string ProductType { get; set; } = string.Empty; // "Key", "Coin", "Bundle", "Rank", "BattlePass"
    
    [Required]
    public int ProductId { get; set; }
    
    public int TotalQuantityPurchased { get; set; }
    
    public DateTime LastPurchaseDate { get; set; }
    
    public DateTime CreatedAt { get; set; }
}
