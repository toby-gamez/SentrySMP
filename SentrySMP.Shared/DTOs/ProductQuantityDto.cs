namespace SentrySMP.Shared.DTOs
{
    public class ProductQuantityDto
    {
        public ProductResponse? Product { get; set; }
        public int Quantity { get; set; } = 1;
    }
}
