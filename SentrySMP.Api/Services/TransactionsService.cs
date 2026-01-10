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
    private readonly SentrySMP.Shared.Interfaces.IRconService _rconService;

    public TransactionsService(SentryDbContext db, ILogger<TransactionsService> logger, SentrySMP.Shared.Interfaces.IRconService rconService)
    {
        _db = db;
        _logger = logger;
        _rconService = rconService;
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

        // After storing transaction, attempt to execute any RCON commands attached to purchased products
        try
        {
            if (!string.IsNullOrEmpty(tx.ItemsJson))
            {
                var opts = new System.Text.Json.JsonSerializerOptions(System.Text.Json.JsonSerializerDefaults.Web);
                var products = System.Text.Json.JsonSerializer.Deserialize<List<SentrySMP.Shared.DTOs.ProductQuantityDto>>(tx.ItemsJson, opts);
                if (products != null && products.Any())
                {
                    _logger.LogInformation("Triggering RCON execution for transaction {TxId}", tx.Id);
                    var rconResult = await _rconService.ExecuteCommandsForProductsAsync(products, tx.MinecraftUsername);
                    try
                    {
                        var serialized = System.Text.Json.JsonSerializer.Serialize(rconResult, opts);
                        // append RCON result to raw response for debugging
                        tx.RawResponse = string.IsNullOrEmpty(tx.RawResponse) ? serialized : tx.RawResponse + "\n" + serialized;
                        // mark status to indicate RCON outcome
                        if (rconResult.AllSucceeded)
                        {
                            tx.Status = string.IsNullOrEmpty(tx.Status) ? "RCON_OK" : tx.Status + ";RCON_OK";
                        }
                        else
                        {
                            tx.Status = string.IsNullOrEmpty(tx.Status) ? "RCON_FAILED" : tx.Status + ";RCON_FAILED";
                        }
                        _db.PaymentTransactions.Update(tx);
                        await _db.SaveChangesAsync();
                    }
                    catch (Exception exSerial)
                    {
                        _logger.LogWarning(exSerial, "Failed to serialize/store RCON result for transaction {TxId}", tx.Id);
                    }
                }
            }
        }
        catch (Exception exRcon)
        {
            _logger.LogError(exRcon, "Error during RCON execution for transaction {TxId}", tx.Id);
            try
            {
                tx.Status = string.IsNullOrEmpty(tx.Status) ? "RCON_ERROR" : tx.Status + ";RCON_ERROR";
                _db.PaymentTransactions.Update(tx);
                await _db.SaveChangesAsync();
            }
            catch { }
        }

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
