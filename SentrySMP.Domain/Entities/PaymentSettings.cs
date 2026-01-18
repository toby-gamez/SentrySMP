namespace SentrySMP.Domain.Entities;

public class PaymentSettings
{
    public int Id { get; set; }
    public bool EnablePayments { get; set; } = true;
    public bool DisableStripe { get; set; } = false;
    public bool DisablePayPal { get; set; } = false;
    public DateTime UpdatedAt { get; set; } = DateTime.UtcNow;
}
