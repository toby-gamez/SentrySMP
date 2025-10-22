using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Shared.DTOs;

public class ServerResponse
{
    public int Id { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [Required]
    [MaxLength(50)]
    public string RCONIP { get; set; } = string.Empty;
    
    [Range(1, 65535)]
    public int RCONPort { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string RCONPassword { get; set; } = string.Empty;
    
    public List<KeyResponse>? Keys { get; set; }
    public List<ShardResponse>? Shards { get; set; }
    public List<BundleResponse>? Bundles { get; set; }
    public List<BattlePassResponse>? BattlePasses { get; set; }
}

public class CreateServerDto
{
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [Required]
    [MaxLength(50)]
    public string RCONIP { get; set; } = string.Empty;
    
    [Range(1, 65535)]
    public int RCONPort { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string RCONPassword { get; set; } = string.Empty;
}

public class UpdateServerDto
{
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [Required]
    [MaxLength(50)]
    public string RCONIP { get; set; } = string.Empty;
    
    [Range(1, 65535)]
    public int RCONPort { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string RCONPassword { get; set; } = string.Empty;
}