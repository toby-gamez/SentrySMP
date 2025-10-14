using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Components.State
{
    public class CartItem
    {
        public KeyResponse Key { get; set; }
        public int Quantity { get; set; } = 1;
        public CartItem(KeyResponse key, int quantity = 1)
        {
            Key = key;
            Quantity = quantity;
        }
    }
}
