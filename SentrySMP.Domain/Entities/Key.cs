using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace SentrySMP.Domain.Entities;

public class Key
{
    public int Id { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;
    
    public double Price { get; set; }
    
    [ForeignKey(nameof(Server))]
    public int ServerId { get; set; }
    
    public int Sale { get; set; }
    
    [MaxLength(255)]
    public string? Image { get; set; } // Bude obsahovat cestu k obrázku nebo URL
    
    // Navigation property
    public virtual Server Server { get; set; } = new();
}