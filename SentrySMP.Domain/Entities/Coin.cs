using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace SentrySMP.Domain.Entities
{
    public class Coin
    {
        [Key]
        public int Id { get; set; }

        [Required]
        [MaxLength(100)]
        public string Name { get; set; } = string.Empty;

        [MaxLength(500)]
        public string? Description { get; set; }

        public double Price { get; set; }

        public int Sale { get; set; } // percent discount

        public string? Image { get; set; }

        public int ServerId { get; set; }

        [ForeignKey(nameof(ServerId))]
        public virtual Server? Server { get; set; }

        // TODO: Add composition/ingredients if needed (e.g. List<CoinComponent> Components)
    }
}
