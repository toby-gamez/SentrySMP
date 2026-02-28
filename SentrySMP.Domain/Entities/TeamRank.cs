using System.ComponentModel.DataAnnotations;
using System.Collections.Generic;

namespace SentrySMP.Domain.Entities;

public class TeamRank
{
    [Key]
    public int Id { get; set; }

    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(20)]
    public string HexColor { get; set; } = string.Empty;

    public ICollection<TeamMember> Members { get; set; } = new List<TeamMember>();
}
