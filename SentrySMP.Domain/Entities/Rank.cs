using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace SentrySMP.Domain.Entities;

public class Rank
{
    public int Id { get; set; }

    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;

    public double Price { get; set; }

    public int Sale { get; set; }

    [MaxLength(255)]
    public string? Image { get; set; }
}
