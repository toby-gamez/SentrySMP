using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class TransactionsService : ITransactionsService
{
    private readonly SentryDbContext _db;
    private readonly ILogger<TransactionsService> _logger;

    public TransactionsService(SentryDbContext db, ILogger<TransactionsService> logger)
    {
        _db = db;
        _logger = logger;
    }

    public async Task<TransactionResponse> CreateTransactionAsync(CreateTransactionRequest req)
    {
        if (req == null) throw new ArgumentNullException(nameof(req));

        decimal amount = 0;
        decimal.TryParse(req.Amount, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out amount);

        var tx = new PaymentTransaction
        {
            Provider = req.Provider ?? string.Empty,
            ProviderTransactionId = req.ProviderTransactionId ?? string.Empty,
            Amount = amount,
            Currency = req.Currency ?? "EUR",
            MinecraftUsername = req.MinecraftUsername ?? string.Empty,
            ItemsJson = req.ItemsJson ?? string.Empty,
            Status = req.Status ?? string.Empty,
            RawResponse = req.RawResponse ?? string.Empty,
            CreatedAt = DateTime.UtcNow
        };

        _db.PaymentTransactions.Add(tx);
        await _db.SaveChangesAsync();

        _logger.LogInformation("Created transaction {TxId} provider={Provider} amount={Amount}", tx.Id, tx.Provider, tx.Amount);

        return new TransactionResponse
        {
            Id = tx.Id,
            Provider = tx.Provider,
            ProviderTransactionId = tx.ProviderTransactionId,
            Amount = tx.Amount,
            Currency = tx.Currency,
            MinecraftUsername = tx.MinecraftUsername,
            ItemsJson = tx.ItemsJson,
            Status = tx.Status,
            CreatedAt = tx.CreatedAt
        };
    }

    public async Task<TransactionResponse?> GetTransactionAsync(long id)
    {
        var tx = await _db.PaymentTransactions.FindAsync(id);
        if (tx == null) return null;

        return new TransactionResponse
        {
            Id = tx.Id,
            Provider = tx.Provider,
            ProviderTransactionId = tx.ProviderTransactionId,
            Amount = tx.Amount,
            Currency = tx.Currency,
            MinecraftUsername = tx.MinecraftUsername,
            ItemsJson = tx.ItemsJson,
            Status = tx.Status,
            CreatedAt = tx.CreatedAt
        };
    }
}
