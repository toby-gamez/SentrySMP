using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services
{
    public class ShopScoreboardService : IShopScoreboardService
    {
        private readonly SentryDbContext _db;

        public ShopScoreboardService(SentryDbContext db)
        {
            _db = db;
        }

        public async Task<List<ShopScoreboardEntryDto>> GetScoreboardAsync(ScoreboardPeriod period, int topN = 50)
        {
            var now = DateTime.UtcNow;
            DateTime? from = period switch
            {
                ScoreboardPeriod.Today => now.Date,
                ScoreboardPeriod.ThisWeek => GetStartOfWeek(now),
                ScoreboardPeriod.ThisMonth => new DateTime(now.Year, now.Month, 1),
                _ => null
            };

            var query = _db.PaymentTransactions
                .AsNoTracking()
                .Where(t => t.Status != null && t.Status.ToLower() == "succeeded");

            if (from.HasValue)
            {
                query = query.Where(t => t.CreatedAt >= from.Value);
            }

            var grouped = await query
                .GroupBy(t => string.IsNullOrWhiteSpace(t.MinecraftUsername) ? "<unknown>" : t.MinecraftUsername)
                .Select(g => new
                {
                    MinecraftUsername = g.Key,
                    TotalPaid = g.Sum(x => x.Amount),
                    TransactionCount = g.Count(),
                    LastPayment = g.Max(x => x.CreatedAt)
                })
                .OrderByDescending(x => x.TotalPaid)
                .ThenByDescending(x => x.LastPayment)
                .Take(topN)
                .ToListAsync();

            var result = grouped
                .Select((g, idx) => new ShopScoreboardEntryDto
                {
                    Rank = idx + 1,
                    MinecraftUsername = g.MinecraftUsername,
                    TotalPaid = g.TotalPaid,
                    TransactionCount = g.TransactionCount,
                    LastPayment = g.LastPayment
                })
                .ToList();

            return result;
        }

        private DateTime GetStartOfWeek(DateTime now)
        {
            // ISO-8601 week start (Monday)
            var diff = (7 + (now.DayOfWeek - DayOfWeek.Monday)) % 7;
            return now.Date.AddDays(-diff);
        }
    }
}
