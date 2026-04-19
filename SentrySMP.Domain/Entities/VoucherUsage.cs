using System.ComponentModel.DataAnnotations;

namespace SentrySMP.Domain.Entities;

public class VoucherUsage
{
    public int Id { get; set; }

    public int VoucherId { get; set; }

    public virtual Voucher Voucher { get; set; } = null!;

    [Required]
    [MaxLength(100)]
    public string MinecraftUsername { get; set; } = string.Empty;

    public DateTime UsedAt { get; set; } = DateTime.UtcNow;
}
