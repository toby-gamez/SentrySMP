using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class KeyResponse
{
    public int Id { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;
    
    [Range(0, double.MaxValue)]
    public double Price { get; set; }
    
    public int ServerId { get; set; }
    
    public int Sale { get; set; } // Sale percentage (0-100)
    
    public string? Image { get; set; }
    
    // Navigation property for display
    public ServerResponse? Server { get; set; }
}

public class CreateKeyDto
{
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;
    
    [Range(0, double.MaxValue)]
    public double Price { get; set; }
    
    [Required]
    public int ServerId { get; set; }
    
    public int Sale { get; set; } = 0;
    
    public string? Image { get; set; }
}

public class UpdateKeyDto
{
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;
    
    [Range(0, double.MaxValue)]
    public double Price { get; set; }
    
    [Required]
    public int ServerId { get; set; }
    
    public int Sale { get; set; }
    
    public string? Image { get; set; }
}