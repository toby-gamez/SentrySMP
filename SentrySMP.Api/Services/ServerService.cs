using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class ServerService : IServerService
{
    private readonly SentryDbContext _context;

    public ServerService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<ServerResponse>> GetAllServersAsync()
    {
        var servers = await _context.Servers
            .Include(s => s.Keys)
            .ToListAsync();

        return servers.Select(MapToResponse);
    }

    public async Task<ServerResponse?> GetServerByIdAsync(int id)
    {
        var server = await _context.Servers
            .Include(s => s.Keys)
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
        var server = await _context.Servers.FindAsync(id);
        if (server == null)
            return false;

        _context.Servers.Remove(server);
        await _context.SaveChangesAsync();
        return true;
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
            }).ToList()
        };
    }
}