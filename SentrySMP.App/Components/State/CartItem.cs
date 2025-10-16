using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Components.State
{
    public class CartItem
    {
        public ProductResponse Key { get; set; }
        public int Quantity { get; set; } = 1;
        public CartItem(ProductResponse key, int quantity = 1)
        {
            Key = key;
            Quantity = quantity;
        }
    }
}
