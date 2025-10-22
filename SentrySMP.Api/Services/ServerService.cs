using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class ServerService : IServerService
{
    private readonly SentryDbContext _context;
    private readonly ILogger<ServerService> _logger;

    public ServerService(SentryDbContext context, ILogger<ServerService> logger)
    {
        _context = context;
        _logger = logger;
    }


    public async Task<IEnumerable<ServerResponse>> GetAllServersAsync()
    {
        var servers = await _context.Servers
            .Include(s => s.Keys)
            .Include(s => s.Shards)
            .Include(s => s.Bundles)
            .Include(s => s.BattlePasses)
            .ToListAsync();
        return servers.Select(MapToResponse);
    }


    public async Task<ServerResponse?> GetServerByIdAsync(int id)
    {
        var server = await _context.Servers
            .Include(s => s.Keys)
            .Include(s => s.Shards)
            .Include(s => s.Bundles)
            .Include(s => s.BattlePasses)
            .FirstOrDefaultAsync(s => s.Id == id);
        return server != null ? MapToResponse(server) : null;
    }

    public async Task<ServerResponse> CreateServerAsync(CreateServerDto createServerDto)
    {
        var server = new Server
        {
            Name = createServerDto.Name,
            RCONIP = createServerDto.RCONIP,
            RCONPort = createServerDto.RCONPort,
            RCONPassword = createServerDto.RCONPassword
        };

        _context.Servers.Add(server);
        await _context.SaveChangesAsync();

        return MapToResponse(server);
    }

    public async Task<ServerResponse?> UpdateServerAsync(int id, UpdateServerDto updateServerDto)
    {
        var server = await _context.Servers.FindAsync(id);
        if (server == null)
            return null;

        server.Name = updateServerDto.Name;
        server.RCONIP = updateServerDto.RCONIP;
        server.RCONPort = updateServerDto.RCONPort;
        server.RCONPassword = updateServerDto.RCONPassword;

        await _context.SaveChangesAsync();
        return MapToResponse(server);
    }

    public async Task<bool> DeleteServerAsync(int id)
    {
        try
        {
            var server = await _context.Servers
                .Include(s => s.Keys)
                .FirstOrDefaultAsync(s => s.Id == id);
                
            if (server == null)
                return false;

            // Log the deletion attempt
            _logger.LogInformation("Attempting to delete server {ServerId} with {KeyCount} keys", 
                id, server.Keys?.Count ?? 0);

            // Explicitly delete related keys first (even though cascade should handle this)
            if (server.Keys != null && server.Keys.Any())
            {
                _context.Keys.RemoveRange(server.Keys);
                _logger.LogInformation("Removing {KeyCount} keys for server {ServerId}", 
                    server.Keys.Count, id);
            }

            // Then delete the server
            _context.Servers.Remove(server);
            await _context.SaveChangesAsync();
            
            _logger.LogInformation("Successfully deleted server {ServerId}", id);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error deleting server {ServerId}: {Message}", id, ex.Message);
            throw;
        }
    }

    private static ServerResponse MapToResponse(Server server)
    {
        return new ServerResponse
        {
            Id = server.Id,
            Name = server.Name,
            RCONIP = server.RCONIP,
            RCONPort = server.RCONPort,
            RCONPassword = server.RCONPassword,
            Keys = server.Keys?.Select(k => new KeyResponse
            {
                Id = k.Id,
                Name = k.Name,
                Description = k.Description,
                Price = k.Price,
                ServerId = k.ServerId,
                Sale = k.Sale,
                Image = k.Image
            }).ToList(),
            Shards = server.Shards?.Select(s => new ShardResponse
            {
                Id = s.Id,
                Name = s.Name,
                Description = s.Description,
                Price = s.Price,
                Sale = s.Sale,
                Image = s.Image
            }).ToList(),
            Bundles = server.Bundles?.Select(b => new BundleResponse
            {
                Id = b.Id,
                Name = b.Name,
                Description = b.Description,
                Price = b.Price,
                ServerId = b.ServerId,
                Sale = b.Sale,
                Image = b.Image
            }).ToList(),
            BattlePasses = server.BattlePasses?.Select(bp => new BattlePassResponse
            {
                Id = bp.Id,
                Name = bp.Name,
                Description = bp.Description,
                Price = bp.Price,
                ServerId = bp.ServerId,
                Sale = bp.Sale,
                Image = bp.Image
            }).ToList()
        };
    }
}