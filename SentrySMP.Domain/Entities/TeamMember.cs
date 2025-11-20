using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace SentrySMP.Domain.Entities;

public class TeamMember
{
    [Key]
    [MaxLength(36)]
    public string Id { get; set; } = Guid.NewGuid().ToString();

    [Required]
    [MaxLength(100)]
    public string MinecraftName { get; set; } = string.Empty;

    [MaxLength(100)]
    public string Role { get; set; } = string.Empty;

    [MaxLength(500)]
    public string? SkinUrl { get; set; }

    // FK back to category
    [MaxLength(36)]
    public string TeamCategoryId { get; set; } = string.Empty;

    public TeamCategory? Category { get; set; }
}
