namespace SentrySMP.Shared.DTOs
{
    public class OnlinePlayerEntry
    {
        public string? Name { get; set; }
        public string? Uuid { get; set; }
        public string? Rank { get; set; }
    }

    public class OnlinePlayersResponse
    {
        public List<OnlinePlayerEntry> Players { get; set; } = new();
        public string? Error { get; set; }
    }

    public class BannedEntry
    {
        public string? Name { get; set; }
        public string? Uuid { get; set; }
        public string? Reason { get; set; }
    }

    public class BanlistResponse
    {
        public List<BannedEntry> Banned { get; set; } = new();
        public string? Error { get; set; }
    }
}
