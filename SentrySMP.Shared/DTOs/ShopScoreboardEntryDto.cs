using System;

namespace SentrySMP.Shared.DTOs
{
    public enum ScoreboardPeriod
    {
        Today,
        ThisWeek,
        ThisMonth,
        AllTime
    }

    public class ShopScoreboardEntryDto
    {
        public int Rank { get; set; }
        public string MinecraftUsername { get; set; } = string.Empty;
        public decimal TotalPaid { get; set; }
        public int TransactionCount { get; set; }
        public DateTime LastPayment { get; set; }
    }
}
