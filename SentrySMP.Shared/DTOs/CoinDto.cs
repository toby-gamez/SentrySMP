using SentrySMP.Shared.DTOs;
namespace SentrySMP.Shared.DTOs
{
    public class CoinResponse : ProductResponse
    {
        // Coin-specific fields
        // Commands attached to this coin
        public List<CommandDto>? Commands { get; set; }

        public CoinResponse()
        {
            Type = "Coin";
        }
    }

    public class CreateCoinDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int? GlobalMaxOrder { get; set; }
        public int ServerId { get; set; }
        // Optional commands to attach to this coin
        public List<CreateCommandDto>? Commands { get; set; }
    }

    public class UpdateCoinDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int? GlobalMaxOrder { get; set; }
        public int ServerId { get; set; }
        // Optional commands to attach to this coin (replaces existing when provided)
        public List<CreateCommandDto>? Commands { get; set; }
    }
}
