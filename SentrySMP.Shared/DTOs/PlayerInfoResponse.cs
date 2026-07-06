namespace SentrySMP.Shared.DTOs
{
    public class PlayerInfoResponse
    {
        public string? Player { get; set; }
        public int? Gems { get; set; }
        public int? Money { get; set; }
        public string? Rank { get; set; }
        public string? Error { get; set; }
        public PlayerStatistics? Statistics { get; set; }
    }

    public class PlayerStatistics
    {
        public long? PlayTimeSeconds { get; set; }
        public long? PlayTimeTicks { get; set; }
        public int? Deaths { get; set; }
        public int? PlayerKills { get; set; }
        public int? MobsKilled { get; set; }
        public long? BlocksTravelled { get; set; }
    }
}
