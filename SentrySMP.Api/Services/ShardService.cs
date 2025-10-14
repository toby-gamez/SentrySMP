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
        return shards.Select(MapToResponse);
    }

    public async Task<IEnumerable<ShardResponse>> GetShardsByServerIdAsync(int serverId)
    {
        var shards = await _context.Shards
            .Include(s => s.Server)
            .Where(s => s.ServerId == serverId)
            .ToListAsync();
        return shards.Select(MapToResponse);
    }

    public async Task<ShardResponse?> GetShardByIdAsync(int id)
    {
        var shard = await _context.Shards
            .Include(s => s.Server)
            .FirstOrDefaultAsync(s => s.Id == id);
        return shard != null ? MapToResponse(shard) : null;
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
        return MapToResponse(shard);
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
        await _context.SaveChangesAsync();
        return MapToResponse(shard);
    }

    public async Task<bool> DeleteShardAsync(int id)
    {
        var shard = await _context.Shards.FindAsync(id);
        if (shard == null) return false;
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
}
