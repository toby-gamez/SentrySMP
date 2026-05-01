using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Controllers
{
    [ApiController]
    public class ScoreboardController : ControllerBase
    {
        private readonly IShopScoreboardService _scoreboard;

        public ScoreboardController(IShopScoreboardService scoreboard)
        {
            _scoreboard = scoreboard;
        }

        [AllowAnonymous]
        [HttpGet("api/scoreboard/all")]
        public async Task<ActionResult<List<ShopScoreboardEntryDto>>> All([FromQuery] int topN = 100)
        {
            var entries = await _scoreboard.GetScoreboardAsync(ScoreboardPeriod.AllTime, topN);
            var filtered = Filter(entries);
            return Ok(filtered);
        }

        [AllowAnonymous]
        [HttpGet("api/scoreboard/today")]
        public async Task<ActionResult<List<ShopScoreboardEntryDto>>> Today([FromQuery] int topN = 100)
        {
            var entries = await _scoreboard.GetScoreboardAsync(ScoreboardPeriod.Today, topN);
            var filtered = Filter(entries);
            return Ok(filtered);
        }

        [AllowAnonymous]
        [HttpGet("api/scoreboard/week")]
        public async Task<ActionResult<List<ShopScoreboardEntryDto>>> Week([FromQuery] int topN = 100)
        {
            var entries = await _scoreboard.GetScoreboardAsync(ScoreboardPeriod.ThisWeek, topN);
            var filtered = Filter(entries);
            return Ok(filtered);
        }

        [AllowAnonymous]
        [HttpGet("api/scoreboard/month")]
        public async Task<ActionResult<List<ShopScoreboardEntryDto>>> Month([FromQuery] int topN = 100)
        {
            var entries = await _scoreboard.GetScoreboardAsync(ScoreboardPeriod.ThisMonth, topN);
            var filtered = Filter(entries);
            return Ok(filtered);
        }

        private List<ShopScoreboardEntryDto> Filter(List<ShopScoreboardEntryDto>? entries)
        {
            if (entries == null) return new List<ShopScoreboardEntryDto>();

            var excludedNames = new[] { "Taneq", "webdev" };

            var filtered = entries
                .Where(e => !string.IsNullOrWhiteSpace(e.MinecraftUsername))
                .Where(e => e.TotalPaid > 0)
                .Where(e => !excludedNames.Contains(e.MinecraftUsername, StringComparer.OrdinalIgnoreCase))
                .Where(e => !string.Equals(e.MinecraftUsername?.Trim(), "<unknown>", StringComparison.OrdinalIgnoreCase))
                .OrderByDescending(e => e.TotalPaid)
                .ToList();

            for (int i = 0; i < filtered.Count; i++)
            {
                filtered[i].Rank = i + 1;
            }

            return filtered;
        }
    }
}
