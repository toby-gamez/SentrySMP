using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class BattlePassService : IBattlePassService
{
    private readonly SentryDbContext _context;

    public BattlePassService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<BattlePassResponse>> GetAllBattlePassesAsync()
    {
        var list = await _context.Set<BattlePass>()
            .Include(bp => bp.Server)
            .ToListAsync();

        return list.Select(bp => MapToResponse(bp));
    }

    public async Task<IEnumerable<BattlePassResponse>> GetBattlePassesByServerIdAsync(int serverId)
    {
        var list = await _context.Set<BattlePass>()
            .Include(bp => bp.Server)
            .Where(bp => bp.ServerId == serverId)
            .ToListAsync();

        return list.Select(bp => MapToResponse(bp));
    }

    public async Task<BattlePassResponse?> GetBattlePassByIdAsync(int id)
    {
        var bp = await _context.Set<BattlePass>()
            .Include(b => b.Server)
            .FirstOrDefaultAsync(b => b.Id == id);
        if (bp == null) return null;
        return MapToResponse(bp);
    }

    public async Task<BattlePassResponse> CreateBattlePassAsync(CreateBattlePassDto createDto)
    {
        var serverExists = await _context.Servers.AnyAsync(s => s.Id == createDto.ServerId);
        if (!serverExists)
            throw new ArgumentException($"Server with ID {createDto.ServerId} does not exist.");

        var bp = new BattlePass
        {
            Name = createDto.Name,
            Description = createDto.Description,
            Price = createDto.Price,
            ServerId = createDto.ServerId,
            Sale = createDto.Sale,
            Image = createDto.Image
        };

        _context.Set<BattlePass>().Add(bp);
        await _context.SaveChangesAsync();

        await _context.Entry(bp).Reference(b => b.Server).LoadAsync();
        return MapToResponse(bp);
    }

    public async Task<BattlePassResponse?> UpdateBattlePassAsync(int id, UpdateBattlePassDto updateDto)
    {
        var bp = await _context.Set<BattlePass>()
            .Include(b => b.Server)
            .FirstOrDefaultAsync(b => b.Id == id);

        if (bp == null) return null;

        if (bp.ServerId != updateDto.ServerId)
        {
            var serverExists = await _context.Servers.AnyAsync(s => s.Id == updateDto.ServerId);
            if (!serverExists)
                throw new ArgumentException($"Server with ID {updateDto.ServerId} does not exist.");
        }

        bp.Name = updateDto.Name;
        bp.Description = updateDto.Description;
        bp.Price = updateDto.Price;
        bp.ServerId = updateDto.ServerId;
        bp.Sale = updateDto.Sale;
        bp.Image = updateDto.Image;

        await _context.SaveChangesAsync();
        if (bp.ServerId != updateDto.ServerId)
            await _context.Entry(bp).Reference(b => b.Server).LoadAsync();

        return MapToResponse(bp);
    }

    public async Task<bool> DeleteBattlePassAsync(int id)
    {
        var bp = await _context.Set<BattlePass>().FindAsync(id);
        if (bp == null) return false;
        _context.Set<BattlePass>().Remove(bp);
        await _context.SaveChangesAsync();
        return true;
    }

    private BattlePassResponse MapToResponse(BattlePass bp)
    {
        return new BattlePassResponse
        {
            Id = bp.Id,
            Name = bp.Name,
            Description = bp.Description,
            Price = bp.Price,
            Sale = bp.Sale,
            Image = bp.Image,
            ServerId = bp.ServerId,
            Server = bp.Server != null ? new ServerResponse
            {
                Id = bp.Server.Id,
                Name = bp.Server.Name,
                RCONIP = bp.Server.RCONIP,
                RCONPort = bp.Server.RCONPort,
                RCONPassword = bp.Server.RCONPassword
            } : null
        };
    }
}
