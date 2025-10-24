using System;

namespace SentrySMP.Domain.Entities
{
    public class PaymentTransaction
    {
        public long Id { get; set; }
        public string Provider { get; set; } = string.Empty; // e.g. PayPal, Stripe
        public string ProviderTransactionId { get; set; } = string.Empty; // order id or session id
        public decimal Amount { get; set; }
        public string Currency { get; set; } = "EUR";
        public string MinecraftUsername { get; set; } = string.Empty;
        public string ItemsJson { get; set; } = string.Empty; // JSON payload of items/cart
        public string Status { get; set; } = string.Empty; // e.g. captured, succeeded
        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
        public string RawResponse { get; set; } = string.Empty; // raw JSON from provider for debugging
    }
}
