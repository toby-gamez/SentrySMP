using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class ShardService : IShardService
{
    private readonly SentryDbContext _context;

    public ShardService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<ShardResponse>> GetAllShardsAsync()
    {
        var shards = await _context.Shards
            .Include(s => s.Server)
            .ToListAsync();
        var shardIds = shards.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "SHARD" && shardIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByShard = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());

        var responses = shards.Select(s => MapToResponse(s, commandsByShard.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<IEnumerable<ShardResponse>> GetShardsByServerIdAsync(int serverId)
    {
        var shards = await _context.Shards
            .Include(s => s.Server)
            .Where(s => s.ServerId == serverId)
            .ToListAsync();
        var shardIds = shards.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "SHARD" && shardIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByShard = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());
        var responses = shards.Select(s => MapToResponse(s, commandsByShard.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<ShardResponse?> GetShardByIdAsync(int id)
    {
        var shard = await _context.Shards
            .Include(s => s.Server)
            .FirstOrDefaultAsync(s => s.Id == id);
        if (shard == null) return null;
        var commands = await _context.Commands.Where(c => c.Type == "SHARD" && c.TypeId == shard.Id).ToListAsync();
        return MapToResponse(shard, commands);
    }

    public async Task<ShardResponse> CreateShardAsync(CreateShardDto createShardDto)
    {
        var serverExists = await _context.Servers.AnyAsync(s => s.Id == createShardDto.ServerId);
        if (!serverExists)
            throw new ArgumentException($"Server with ID {createShardDto.ServerId} does not exist.");

        var shard = new Shard
        {
            Name = createShardDto.Name,
            Description = createShardDto.Description,
            Price = createShardDto.Price,
            ServerId = createShardDto.ServerId,
            Sale = createShardDto.Sale,
            Image = createShardDto.Image
        };
        _context.Shards.Add(shard);
        await _context.SaveChangesAsync();
        // Attach commands if provided
        if (createShardDto.Commands != null && createShardDto.Commands.Any())
        {
            var toAdd = createShardDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "SHARD",
                TypeId = shard.Id
            }).ToList();
            _context.Commands.AddRange(toAdd);
            await _context.SaveChangesAsync();
        }

        var commands = await _context.Commands.Where(c => c.Type == "SHARD" && c.TypeId == shard.Id).ToListAsync();
        return MapToResponse(shard, commands);
    }

    public async Task<ShardResponse?> UpdateShardAsync(int id, UpdateShardDto updateShardDto)
    {
        var shard = await _context.Shards.FindAsync(id);
        if (shard == null) return null;
        shard.Name = updateShardDto.Name;
        shard.Description = updateShardDto.Description;
        shard.Price = updateShardDto.Price;
        shard.ServerId = updateShardDto.ServerId;
        shard.Sale = updateShardDto.Sale;
        shard.Image = updateShardDto.Image;
        // If commands provided, replace existing
        if (updateShardDto.Commands != null)
        {
            var existing = await _context.Commands.Where(c => c.Type == "SHARD" && c.TypeId == shard.Id).ToListAsync();
            if (existing.Any())
                _context.Commands.RemoveRange(existing);

            var newCommands = updateShardDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "SHARD",
                TypeId = shard.Id
            }).ToList();
            if (newCommands.Any())
                _context.Commands.AddRange(newCommands);
        }
        await _context.SaveChangesAsync();
        var commands = await _context.Commands.Where(c => c.Type == "SHARD" && c.TypeId == shard.Id).ToListAsync();
        return MapToResponse(shard, commands);
    }

    public async Task<bool> DeleteShardAsync(int id)
    {
        var shard = await _context.Shards.FindAsync(id);
        if (shard == null) return false;
        // Remove associated commands (cascade-like)
        var commands = await _context.Commands.Where(c => c.Type == "SHARD" && c.TypeId == id).ToListAsync();
        if (commands.Any())
            _context.Commands.RemoveRange(commands);

        _context.Shards.Remove(shard);
        await _context.SaveChangesAsync();
        return true;
    }

    private static ShardResponse MapToResponse(Shard shard)
    {
        return new ShardResponse
        {
            Id = shard.Id,
            Name = shard.Name,
            Description = shard.Description,
            Price = shard.Price,
            Sale = shard.Sale,
            Image = shard.Image,
            Server = shard.Server != null ? new ServerResponse
            {
                Id = shard.Server.Id,
                Name = shard.Server.Name
            } : null
        };
    }
    private ShardResponse MapToResponse(Shard shard, List<Command> commands)
    {
        var commandDtos = commands.Select(c => new CommandDto
        {
            Id = c.Id,
            CommandText = c.CommandText,
            Type = c.Type,
            TypeId = c.TypeId
        }).ToList();

        return new ShardResponse
        {
            Id = shard.Id,
            Name = shard.Name,
            Description = shard.Description,
            Price = shard.Price,
            Sale = shard.Sale,
            Image = shard.Image,
            Server = shard.Server != null ? new ServerResponse
            {
                Id = shard.Server.Id,
                Name = shard.Server.Name
            } : null,
            Commands = commandDtos
        };
    }
}
