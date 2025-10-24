using Refit;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface ICheckoutApi
    {
        [Post("/api/paypal/create-order")]
        Task<CreateOrderResponse> CreateOrderAsync([Body] CreateOrderRequest request);

        [Post("/api/stripe/create-session")]
        Task<CreateStripeSessionResponse> CreateStripeSessionAsync([Body] CreateStripeSessionRequest request);
    }
}
