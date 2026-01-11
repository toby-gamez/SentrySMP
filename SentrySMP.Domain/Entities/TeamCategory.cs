using System.ComponentModel.DataAnnotations;
namespace SentrySMP.Domain.Entities;

public class TeamCategory
{
    [Key]
    [MaxLength(36)]
    public string Id { get; set; } = Guid.NewGuid().ToString();

    [Required]
    [MaxLength(100)]
    public string Name { get; set; } = string.Empty;

    public int SortOrder { get; set; }

    public List<TeamMember> Members { get; set; } = new List<TeamMember>();
}
