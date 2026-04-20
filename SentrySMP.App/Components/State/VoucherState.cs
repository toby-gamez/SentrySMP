using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.JSInterop;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Components.State
{
    // Shared voucher state service to keep applied voucher in sync across components
    public class VoucherState
    {
        private readonly IJSRuntime _js;
        private readonly ISentryApi _sentryApi;
        private const string VoucherStorageKey = "applied_voucher";

        public event Action? OnChange;

        public ValidateVoucherResponse? AppliedVoucher { get; private set; }
        public string VoucherCode { get; private set; } = string.Empty;
        public bool IsLoading { get; private set; }
        public string? Error { get; private set; }

        public VoucherState(IJSRuntime js, ISentryApi sentryApi)
        {
            _js = js ?? throw new ArgumentNullException(nameof(js));
            _sentryApi = sentryApi ?? throw new ArgumentNullException(nameof(sentryApi));
        }

        private void Notify() => OnChange?.Invoke();

        public async Task LoadFromStorageAsync(Components.State.CartState cartState)
        {
            try
            {
                var persisted = await _js.InvokeAsync<string>("localStorage.getItem", VoucherStorageKey);
                if (!string.IsNullOrWhiteSpace(persisted))
                {
                    VoucherCode = persisted!;
                    await ApplyAsync(VoucherCode, cartState, persist: false);
                }
            }
            catch
            {
                // ignore localStorage errors
            }
        }

        public async Task ApplyAsync(string code, Components.State.CartState cartState, bool persist)
        {
            Error = null;
            AppliedVoucher = null;
            IsLoading = true;
            VoucherCode = code ?? string.Empty;
            Notify();

            try
            {
                var items = cartState.CartItems.Select(ci =>
                {
                    double unitPrice = ci.Key.Sale > 0
                        ? Convert.ToDouble(ci.Key.Price) * (1 - ci.Key.Sale / 100.0)
                        : Convert.ToDouble(ci.Key.Price);
                    return new VoucherCartItem
                    {
                        Type = ci.Key.Type,
                        Id = ci.Key.Id,
                        Quantity = ci.Quantity,
                        UnitPrice = unitPrice
                    };
                }).ToList();

                var response = await _sentryApi.ValidateVoucherAsync(new ValidateVoucherRequest { Code = code, Items = items });

                if (response != null && response.IsValid)
                {
                    AppliedVoucher = response;
                    VoucherCode = response.Code ?? code;
                    if (persist)
                    {
                        try { await _js.InvokeVoidAsync("localStorage.setItem", VoucherStorageKey, VoucherCode); } catch { }
                    }
                }
                else
                {
                    Error = response?.Message ?? "Invalid voucher code.";
                    AppliedVoucher = null;
                }
            }
            catch (Exception ex)
            {
                Error = $"Error applying voucher: {ex.Message}";
            }
            finally
            {
                IsLoading = false;
                Notify();
            }
        }

        public async Task RemoveAsync()
        {
            AppliedVoucher = null;
            VoucherCode = string.Empty;
            Error = null;
            try { await _js.InvokeVoidAsync("localStorage.removeItem", VoucherStorageKey); } catch { }
            Notify();
        }
    }
}
