using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class BundleResponse : ProductResponse
{
    public int ServerId { get; set; }

    // Commands attached to this bundle (if any)
    public List<CommandDto>? Commands { get; set; }

    public BundleResponse()
    {
        Type = "Bundle";
    }
}

public class CreateBundleDto
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
    // Optional commands to attach to the bundle during creation
    public List<CreateCommandDto>? Commands { get; set; }
}

public class UpdateBundleDto
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
    // Update commands: the client can supply full list to replace existing commands
    public List<CreateCommandDto>? Commands { get; set; }
}
