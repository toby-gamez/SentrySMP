using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface ITransactionsService
    {
        Task<TransactionResponse> CreateTransactionAsync(CreateTransactionRequest req);
        Task<TransactionResponse?> GetTransactionAsync(long id);
    }
}
