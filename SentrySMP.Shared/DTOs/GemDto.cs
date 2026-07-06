using SentrySMP.Shared.DTOs;
namespace SentrySMP.Shared.DTOs
{
    public class GemResponse : ProductResponse
    {
        // Gem-specific fields
        // Commands attached to this gem
        public List<CommandDto>? Commands { get; set; }

        public GemResponse()
        {
            Type = "Gem";
        }
    }

    public class CreateGemDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int? GlobalMaxOrder { get; set; }
        public int ServerId { get; set; }
        // Optional commands to attach to this gem
        public List<CreateCommandDto>? Commands { get; set; }
    }

    public class UpdateGemDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int? GlobalMaxOrder { get; set; }
        public int ServerId { get; set; }
        // Optional commands to attach to this gem (replaces existing when provided)
        public List<CreateCommandDto>? Commands { get; set; }
    }
}
