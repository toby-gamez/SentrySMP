namespace SentrySMP.Shared.DTOs
{
    public class ProductResponse
    {
        public int Id { get; set; }
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int? GlobalMaxOrder { get; set; }
        public ServerResponse? Server { get; set; }
        // Distinguish between product kinds stored in the cart (e.g. "Key", "Coin")
        public string Type { get; set; } = "Product";
    }
}
