using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class GemService : IGemService
{
    private readonly SentryDbContext _context;

    public GemService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<GemResponse>> GetAllGemsAsync()
    {
        var gems = await _context.Gems
            .Include(s => s.Server)
            .ToListAsync();
        var gemIds = gems.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "GEM" && gemIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByGem = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());

        var responses = gems.Select(s => MapToResponse(s, commandsByGem.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<IEnumerable<GemResponse>> GetGemsByServerIdAsync(int serverId)
    {
        var gems = await _context.Gems
            .Include(s => s.Server)
            .Where(s => s.ServerId == serverId)
            .ToListAsync();
        var gemIds = gems.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "GEM" && gemIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByGem = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());
        var responses = gems.Select(s => MapToResponse(s, commandsByGem.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<GemResponse?> GetGemByIdAsync(int id)
    {
        var gem = await _context.Gems
            .Include(s => s.Server)
            .FirstOrDefaultAsync(s => s.Id == id);
        if (gem == null) return null;
        var commands = await _context.Commands.Where(c => c.Type == "GEM" && c.TypeId == gem.Id).ToListAsync();
        return MapToResponse(gem, commands);
    }

    public async Task<GemResponse> CreateGemAsync(CreateGemDto createGemDto)
    {
        var serverExists = await _context.Servers.AnyAsync(s => s.Id == createGemDto.ServerId);
        if (!serverExists)
            throw new ArgumentException($"Server with ID {createGemDto.ServerId} does not exist.");

        var gem = new Gem
        {
            Name = createGemDto.Name,
            Description = createGemDto.Description,
            Price = createGemDto.Price,
            ServerId = createGemDto.ServerId,
            Sale = createGemDto.Sale,
            Image = createGemDto.Image,
            GlobalMaxOrder = createGemDto.GlobalMaxOrder
        };
        _context.Gems.Add(gem);
        await _context.SaveChangesAsync();
        // Attach commands if provided
        if (createGemDto.Commands != null && createGemDto.Commands.Any())
        {
            var toAdd = createGemDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "GEM",
                TypeId = gem.Id
            }).ToList();
            _context.Commands.AddRange(toAdd);
            await _context.SaveChangesAsync();
        }

        var commands = await _context.Commands.Where(c => c.Type == "GEM" && c.TypeId == gem.Id).ToListAsync();
        return MapToResponse(gem, commands);
    }

    public async Task<GemResponse?> UpdateGemAsync(int id, UpdateGemDto updateGemDto)
    {
        var gem = await _context.Gems.FindAsync(id);
        if (gem == null) return null;
        gem.Name = updateGemDto.Name;
        gem.Description = updateGemDto.Description;
        gem.Price = updateGemDto.Price;
        gem.ServerId = updateGemDto.ServerId;
        gem.Sale = updateGemDto.Sale;
        gem.Image = updateGemDto.Image;
        gem.GlobalMaxOrder = updateGemDto.GlobalMaxOrder;
        // If commands provided, replace existing
        if (updateGemDto.Commands != null)
        {
            var existing = await _context.Commands.Where(c => c.Type == "GEM" && c.TypeId == gem.Id).ToListAsync();
            if (existing.Any())
                _context.Commands.RemoveRange(existing);

            var newCommands = updateGemDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "GEM",
                TypeId = gem.Id
            }).ToList();
            if (newCommands.Any())
                _context.Commands.AddRange(newCommands);
        }
        await _context.SaveChangesAsync();
        var commands = await _context.Commands.Where(c => c.Type == "GEM" && c.TypeId == gem.Id).ToListAsync();
        return MapToResponse(gem, commands);
    }

    public async Task<bool> DeleteGemAsync(int id)
    {
        var gem = await _context.Gems.FindAsync(id);
        if (gem == null) return false;
        // Remove associated commands (cascade-like)
        var commands = await _context.Commands.Where(c => c.Type == "GEM" && c.TypeId == id).ToListAsync();
        if (commands.Any())
            _context.Commands.RemoveRange(commands);

        _context.Gems.Remove(gem);
        await _context.SaveChangesAsync();
        return true;
    }

    private static GemResponse MapToResponse(Gem gem)
    {
        return new GemResponse
        {
            Id = gem.Id,
            Name = gem.Name,
            Description = gem.Description,
            Price = gem.Price,
            Sale = gem.Sale,
            Image = gem.Image,
            Server = gem.Server != null ? new ServerResponse
            {
                Id = gem.Server.Id,
                Name = gem.Server.Name
            } : null
        };
    }
    private GemResponse MapToResponse(Gem gem, List<Command> commands)
    {
        var commandDtos = commands.Select(c => new CommandDto
        {
            Id = c.Id,
            CommandText = c.CommandText,
            Type = c.Type,
            TypeId = c.TypeId
        }).ToList();

        return new GemResponse
        {
            Id = gem.Id,
            Name = gem.Name,
            Description = gem.Description,
            Price = gem.Price,
            Sale = gem.Sale,
            Image = gem.Image,
            GlobalMaxOrder = gem.GlobalMaxOrder,
            Server = gem.Server != null ? new ServerResponse
            {
                Id = gem.Server.Id,
                Name = gem.Server.Name
            } : null,
            Commands = commandDtos
        };
    }
}
