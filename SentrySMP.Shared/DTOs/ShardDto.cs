using SentrySMP.Shared.DTOs;
namespace SentrySMP.Shared.DTOs
{
    public class ShardResponse
    {
        public int Id { get; set; }
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public ServerResponse? Server { get; set; }
        // TODO: Add composition/ingredients if needed
    }

    public class CreateShardDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int ServerId { get; set; }
        // TODO: Add composition/ingredients if needed
    }

    public class UpdateShardDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int ServerId { get; set; }
        // TODO: Add composition/ingredients if needed
    }
}
