namespace SentrySMP.Shared.DTOs
{
    public class CreateStripeSessionRequest
    {
        // Amount as decimal string in EUR (e.g. "5.00")
        public string Amount { get; set; } = string.Empty;
        public string? Description { get; set; }
    }

    public class CreateStripeSessionResponse
    {
        public string Url { get; set; } = string.Empty;
        public string SessionId { get; set; } = string.Empty;
    }
}
