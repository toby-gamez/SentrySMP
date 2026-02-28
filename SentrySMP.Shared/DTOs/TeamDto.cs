using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class TeamMemberDto
{
    public string Id { get; set; } = Guid.NewGuid().ToString();

    [Required]
    [MaxLength(100)]
    public string MinecraftName { get; set; } = string.Empty;

    // Reference to TeamRank (nullable)
    public int? TeamRankId { get; set; }

    public TeamRankDto? Rank { get; set; }

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

public class TeamRankDto
{
    public int Id { get; set; }

    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(20)]
    public string HexColor { get; set; } = string.Empty;
}
