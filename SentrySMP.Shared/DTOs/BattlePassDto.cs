using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class BattlePassResponse : ProductResponse
{
    public int ServerId { get; set; }

    public BattlePassResponse()
    {
        Type = "BattlePass";
    }
}

public class CreateBattlePassDto
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

public class UpdateBattlePassDto
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
