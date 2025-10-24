using Refit;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface ITransactionsApi
    {
        [Post("/api/transactions")]
        Task<TransactionResponse> CreateTransactionAsync([Body] CreateTransactionRequest request);

        [Get("/api/transactions/{id}")]
        Task<TransactionResponse> GetTransactionAsync(long id);
    }
}
