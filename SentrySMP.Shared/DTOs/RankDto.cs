using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class RankResponse : ProductResponse
{
    // No ServerId for Rank - ranks are global

    // Commands associated with this rank (optional)
    public List<CommandDto>? Commands { get; set; }

    public RankResponse()
    {
        Type = "Rank";
    }
}

public class CreateRankDto
{
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;

    [Range(0, double.MaxValue)]
    public double Price { get; set; }

    // ServerId removed for Rank

    public int Sale { get; set; } = 0;

    public string? Image { get; set; }
    public List<CreateCommandDto>? Commands { get; set; }
}

public class UpdateRankDto
{
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(500)]
    public string Description { get; set; } = string.Empty;

    [Range(0, double.MaxValue)]
    public double Price { get; set; }

    // ServerId removed for Rank

    public int Sale { get; set; }

    public string? Image { get; set; }
    public List<CreateCommandDto>? Commands { get; set; }
}
