using System.Collections.Generic;

namespace SentrySMP.Shared.DTOs
{
    public class DeliveryApiResponse
    {
        public List<string>? Output { get; set; }
        public bool Success { get; set; }
        public string? Error { get; set; }
        public double? MinAllowedPrice { get; set; }
        public double? CartTotal { get; set; }
        public double? DiscountPercent { get; set; }
    }
}
