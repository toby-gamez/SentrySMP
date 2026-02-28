using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class TeamRankService : ITeamRankService
{
    private readonly SentryDbContext _db;

    public TeamRankService(SentryDbContext db)
    {
        _db = db;
    }

    public async Task<IEnumerable<TeamRankDto>> GetAllAsync()
    {
        var ranks = await _db.TeamRanks.AsNoTracking().ToListAsync();
        return ranks.Select(r => new TeamRankDto { Id = r.Id, Name = r.Name, HexColor = r.HexColor });
    }

    public async Task<TeamRankDto> CreateAsync(TeamRankDto dto)
    {
        var entity = new TeamRank { Name = dto.Name, HexColor = dto.HexColor };
        _db.TeamRanks.Add(entity);
        await _db.SaveChangesAsync();
        return new TeamRankDto { Id = entity.Id, Name = entity.Name, HexColor = entity.HexColor };
    }

    public async Task<TeamRankDto> UpdateAsync(int id, TeamRankDto dto)
    {
        var entity = await _db.TeamRanks.FindAsync(id);
        if (entity == null) throw new KeyNotFoundException("TeamRank not found");
        entity.Name = dto.Name;
        entity.HexColor = dto.HexColor;
        await _db.SaveChangesAsync();
        return new TeamRankDto { Id = entity.Id, Name = entity.Name, HexColor = entity.HexColor };
    }

    public async Task DeleteAsync(int id)
    {
        var entity = await _db.TeamRanks.FindAsync(id);
        if (entity == null) throw new KeyNotFoundException("TeamRank not found");
        // Nullify references on members
        var members = await _db.TeamMembers.Where(m => m.TeamRankId == id).ToListAsync();
        foreach (var m in members) m.TeamRankId = null;
        _db.TeamRanks.Remove(entity);
        await _db.SaveChangesAsync();
    }
}
