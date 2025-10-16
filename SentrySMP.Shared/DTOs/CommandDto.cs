namespace SentrySMP.Shared.DTOs
{
    public class CommandDto
    {
        public int Id { get; set; }
        public string CommandText { get; set; } = string.Empty;
        public string Type { get; set; } = string.Empty;
        public int TypeId { get; set; }
    }

    // DTO used when creating commands attached to a Key/Shard from higher-level DTOs
    public class CreateCommandDto
    {
        public string CommandText { get; set; } = string.Empty;
        // Id/Type/TypeId are set by server when attaching to a Key/Shard
    }
}