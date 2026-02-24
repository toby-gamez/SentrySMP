using System.Collections.Generic;
using System.Threading.Tasks;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface IShopScoreboardService
    {
        Task<List<ShopScoreboardEntryDto>> GetScoreboardAsync(ScoreboardPeriod period, int topN = 50);
    }
}
