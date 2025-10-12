using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Domain.Entities;

public class Server
{
    public int Id { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;
    
    [Required]
    [MaxLength(50)]
    public string RCONIP { get; set; } = string.Empty;
    
    public int RCONPort { get; set; }
    
    [Required]
    [MaxLength(100)]
    public string RCONPassword { get; set; } = string.Empty;
    
    // Navigation property
    public virtual ICollection<Key> Keys { get; set; } = new List<Key>();
}