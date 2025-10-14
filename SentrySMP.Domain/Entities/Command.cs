using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Domain.Entities
{
    public class Command
    {
        [Key]
        public int Id { get; set; }
        [Required]
        public string CommandText { get; set; } = string.Empty;
        [Required]
        public string Type { get; set; } = string.Empty; // e.g. KEYS, SHARDS
        [Required]
        public int TypeId { get; set; } // id of the related entity
    }
}