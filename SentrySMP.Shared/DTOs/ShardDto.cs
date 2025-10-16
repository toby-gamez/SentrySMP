using SentrySMP.Shared.DTOs;
namespace SentrySMP.Shared.DTOs
{
    public class ShardResponse : ProductResponse
    {
        // Shard-specific fields
        // Commands attached to this shard
        public List<CommandDto>? Commands { get; set; }

        public ShardResponse()
        {
            Type = "Shard";
        }
    }

    public class CreateShardDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int ServerId { get; set; }
        // Optional commands to attach to this shard
        public List<CreateCommandDto>? Commands { get; set; }
    }

    public class UpdateShardDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int ServerId { get; set; }
        // Optional commands to attach to this shard (replaces existing when provided)
        public List<CreateCommandDto>? Commands { get; set; }
    }
}
