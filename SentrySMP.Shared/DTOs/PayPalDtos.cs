using System.Text.Json.Serialization;

namespace SentrySMP.Shared.DTOs
{
    public class CreateOrderRequest
    {
        public string Amount { get; set; } = string.Empty;
    }

    public class CreateOrderResponse
    {
        [JsonPropertyName("approveUrl")]
        public string ApproveUrl { get; set; } = string.Empty;
        
        [JsonPropertyName("orderId")]
        public string OrderId { get; set; } = string.Empty;
    }
}
