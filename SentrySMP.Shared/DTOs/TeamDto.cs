using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class TeamMemberDto
{
    public string Id { get; set; } = Guid.NewGuid().ToString();

    [Required]
    [MaxLength(100)]
    public string MinecraftName { get; set; } = string.Empty;

    [MaxLength(100)]
    public string Role { get; set; } = string.Empty;

    // Optional: allow a custom skin url (if empty use Minotar helm)
    [MaxLength(500)]
    public string? SkinUrl { get; set; }
}

public class TeamCategoryDto
{
    public string Id { get; set; } = Guid.NewGuid().ToString();

    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    public List<TeamMemberDto> Members { get; set; } = new List<TeamMemberDto>();
}

public class TeamResponseDto
{
    public List<TeamCategoryDto> Categories { get; set; } = new List<TeamCategoryDto>();
}
