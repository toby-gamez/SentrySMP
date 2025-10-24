namespace SentrySMP.Shared.DTOs
{
    public class CreateOrderRequest
    {
        public string Amount { get; set; } = string.Empty;
    }

    public class CreateOrderResponse
    {
        public string ApproveUrl { get; set; } = string.Empty;
        public string OrderId { get; set; } = string.Empty;
    }
}
