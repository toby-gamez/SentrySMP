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

        var bpIds = list.Select(b => b.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "BATTLEPASS" && bpIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByBp = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());

        return list.Select(bp => MapToResponse(bp, commandsByBp.TryGetValue(bp.Id, out var cmds) ? cmds : new List<Domain.Entities.Command>()));
    }

    public async Task<IEnumerable<BattlePassResponse>> GetBattlePassesByServerIdAsync(int serverId)
    {
        var list = await _context.Set<BattlePass>()
            .Include(bp => bp.Server)
            .Where(bp => bp.ServerId == serverId)
            .ToListAsync();

        var bpIds = list.Select(b => b.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "BATTLEPASS" && bpIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByBp = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());
        return list.Select(bp => MapToResponse(bp, commandsByBp.TryGetValue(bp.Id, out var cmds) ? cmds : new List<Domain.Entities.Command>()));
    }

    public async Task<BattlePassResponse?> GetBattlePassByIdAsync(int id)
    {
        var bp = await _context.Set<BattlePass>()
            .Include(b => b.Server)
            .FirstOrDefaultAsync(b => b.Id == id);
        if (bp == null) return null;
        var commands = await _context.Commands.Where(c => c.Type == "BATTLEPASS" && c.TypeId == bp.Id).ToListAsync();
        return MapToResponse(bp, commands);
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
            Image = createDto.Image,
            GlobalMaxOrder = createDto.GlobalMaxOrder
        };

        _context.Set<BattlePass>().Add(bp);
        await _context.SaveChangesAsync();

        // If commands were provided, attach them
        if (createDto.Commands != null && createDto.Commands.Any())
        {
            var toAdd = createDto.Commands.Select(c => new Domain.Entities.Command
            {
                CommandText = c.CommandText,
                Type = "BATTLEPASS",
                TypeId = bp.Id
            }).ToList();

            _context.Commands.AddRange(toAdd);
            await _context.SaveChangesAsync();
        }

        await _context.Entry(bp).Reference(b => b.Server).LoadAsync();
        var commands = await _context.Commands.Where(c => c.Type == "BATTLEPASS" && c.TypeId == bp.Id).ToListAsync();
        return MapToResponse(bp, commands);
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
        bp.GlobalMaxOrder = updateDto.GlobalMaxOrder;
        // If commands are provided in the update, replace existing commands for this battlepass
        if (updateDto.Commands != null)
        {
            var existing = await _context.Commands.Where(c => c.Type == "BATTLEPASS" && c.TypeId == bp.Id).ToListAsync();
            if (existing.Any())
                _context.Commands.RemoveRange(existing);

            var newCommands = updateDto.Commands.Select(c => new Domain.Entities.Command
            {
                CommandText = c.CommandText,
                Type = "BATTLEPASS",
                TypeId = bp.Id
            }).ToList();
            if (newCommands.Any())
                _context.Commands.AddRange(newCommands);
        }

        await _context.SaveChangesAsync();
        if (bp.ServerId != updateDto.ServerId)
            await _context.Entry(bp).Reference(b => b.Server).LoadAsync();

        var commands = await _context.Commands.Where(c => c.Type == "BATTLEPASS" && c.TypeId == bp.Id).ToListAsync();
        return MapToResponse(bp, commands);
    }

    public async Task<bool> DeleteBattlePassAsync(int id)
    {
        var bp = await _context.Set<BattlePass>().FindAsync(id);
        if (bp == null) return false;
        // Remove associated commands (cascade-like behavior)
        var commands = await _context.Commands.Where(c => c.Type == "BATTLEPASS" && c.TypeId == id).ToListAsync();
        if (commands.Any())
            _context.Commands.RemoveRange(commands);

        _context.Set<BattlePass>().Remove(bp);
        await _context.SaveChangesAsync();
        return true;
    }

    private BattlePassResponse MapToResponse(BattlePass bp, List<Domain.Entities.Command> commands)
    {
        var commandDtos = commands.Select(c => new SentrySMP.Shared.DTOs.CommandDto
        {
            Id = c.Id,
            CommandText = c.CommandText,
            Type = c.Type,
            TypeId = c.TypeId
        }).ToList();

        return new BattlePassResponse
        {
            Id = bp.Id,
            Name = bp.Name,
            Description = bp.Description,
            Price = bp.Price,
            Sale = bp.Sale,
            Image = bp.Image,
            ServerId = bp.ServerId,
            GlobalMaxOrder = bp.GlobalMaxOrder,
            Server = bp.Server != null ? new ServerResponse
            {
                Id = bp.Server.Id,
                Name = bp.Server.Name,
                RCONIP = bp.Server.RCONIP,
                RCONPort = bp.Server.RCONPort,
                RCONPassword = bp.Server.RCONPassword
            } : null,
            Commands = commandDtos
        };
    }
}
