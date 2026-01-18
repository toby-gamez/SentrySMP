namespace SentrySMP.Shared.DTOs;

public class PaymentSettingsResponse
{
    public int Id { get; set; }
    public bool EnablePayments { get; set; }
    public bool DisableStripe { get; set; }
    public bool DisablePayPal { get; set; }
    public DateTime UpdatedAt { get; set; }
}

public class UpdatePaymentSettingsRequest
{
    public bool EnablePayments { get; set; }
    public bool DisableStripe { get; set; }
    public bool DisablePayPal { get; set; }
}
