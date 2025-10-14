using System.Collections.Generic;
using System.Linq;
using System.Text.Json;
using System.Threading.Tasks;
using Microsoft.JSInterop;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Components.State
{
    public class CartState
    {
        private readonly IJSRuntime? _js;
        private const string StorageKey = "cart_items";

        public CartState() { }
        public CartState(IJSRuntime js)
        {
            _js = js;
        }
        public async Task LoadFromStorageAsync(IJSRuntime js)
        {
            var json = await js.InvokeAsync<string>("localStorage.getItem", StorageKey);
            if (!string.IsNullOrEmpty(json))
            {
                try
                {
                    var items = JsonSerializer.Deserialize<List<CartItem>>(json);
                    if (items != null)
                    {
                        _cartItems.Clear();
                        _cartItems.AddRange(items);
                        NotifyStateChanged();
                    }
                }
                catch { }
            }
        }

        private async Task SaveToStorageAsync()
        {
            if (_js == null) return;
            var json = JsonSerializer.Serialize(_cartItems);
            await _js.InvokeVoidAsync("localStorage.setItem", StorageKey, json);
        }
        public event Action? OnChange;

        private readonly List<CartItem> _cartItems = new();
        public IReadOnlyList<CartItem> CartItems => _cartItems;

        public async Task AddToCartAsync(KeyResponse key)
        {
            var item = _cartItems.FirstOrDefault(i => i.Key.Id == key.Id);
            if (item != null)
            {
                if (item.Quantity < 10)
                {
                    item.Quantity++;
                }
            }
            else
            {
                _cartItems.Add(new CartItem(key, 1));
            }
            NotifyStateChanged();
            await SaveToStorageAsync();
        }

        public async Task RemoveFromCartAsync(KeyResponse key)
        {
            var item = _cartItems.FirstOrDefault(i => i.Key.Id == key.Id);
            if (item != null)
            {
                _cartItems.Remove(item);
                NotifyStateChanged();
                await SaveToStorageAsync();
            }
        }

        public async Task ChangeQuantityAsync(KeyResponse key, int delta)
        {
            var item = _cartItems.FirstOrDefault(i => i.Key.Id == key.Id);
            if (item != null)
            {
                item.Quantity = Math.Clamp(item.Quantity + delta, 1, 10);
                NotifyStateChanged();
                await SaveToStorageAsync();
            }
        }

        public async Task ClearCartAsync()
        {
            _cartItems.Clear();
            NotifyStateChanged();
            await SaveToStorageAsync();
        }

        private void NotifyStateChanged() => OnChange?.Invoke();
    }
}