using System;

namespace SentrySMP.Shared.DTOs
{
    public class CreateTransactionRequest
    {
        public string Provider { get; set; } = string.Empty;
        public string ProviderTransactionId { get; set; } = string.Empty;
        public string Amount { get; set; } = "0.00"; // as string in EUR
        public string Currency { get; set; } = "EUR";
        public string MinecraftUsername { get; set; } = string.Empty;
        public string ItemsJson { get; set; } = string.Empty;
        public string Status { get; set; } = string.Empty;
        public string RawResponse { get; set; } = string.Empty;
    }

    public class TransactionResponse
    {
        public long Id { get; set; }
        public string Provider { get; set; } = string.Empty;
        public string ProviderTransactionId { get; set; } = string.Empty;
        public decimal Amount { get; set; }
        public string Currency { get; set; } = "EUR";
        public string MinecraftUsername { get; set; } = string.Empty;
        public string ItemsJson { get; set; } = string.Empty;
        public string Status { get; set; } = string.Empty;
        public DateTime CreatedAt { get; set; }
    }
}
