using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class TransactionsController : ControllerBase
    {
        private readonly SentrySMP.Api.Infrastructure.Data.SentryDbContext _db;

        public TransactionsController(SentrySMP.Api.Infrastructure.Data.SentryDbContext db)
        {
            _db = db;
        }

        [HttpPost]
        public async Task<IActionResult> Create([FromBody] CreateTransactionRequest req)
        {
            if (req == null) return BadRequest();

            decimal amount = 0;
            decimal.TryParse(req.Amount, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out amount);

            var tx = new SentrySMP.Domain.Entities.PaymentTransaction
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

            var resp = new TransactionResponse
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

            return Ok(resp);
        }

        [HttpGet("{id}")]
        public async Task<IActionResult> Get(long id)
        {
            var tx = await _db.PaymentTransactions.FindAsync(id);
            if (tx == null) return NotFound();

            var resp = new TransactionResponse
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

            return Ok(resp);
        }
    }
}
