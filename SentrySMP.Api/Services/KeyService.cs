using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class KeyService : IKeyService
{
    private readonly SentryDbContext _context;

    public KeyService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<KeyResponse>> GetAllKeysAsync()
    {
        var keys = await _context.Keys
            .Include(k => k.Server)
            .ToListAsync();

        return keys.Select(MapToResponse);
    }

    public async Task<IEnumerable<KeyResponse>> GetKeysByServerIdAsync(int serverId)
    {
        var keys = await _context.Keys
            .Include(k => k.Server)
            .Where(k => k.ServerId == serverId)
            .ToListAsync();

        return keys.Select(MapToResponse);
    }

    public async Task<KeyResponse?> GetKeyByIdAsync(int id)
    {
        var key = await _context.Keys
            .Include(k => k.Server)
            .FirstOrDefaultAsync(k => k.Id == id);

        return key != null ? MapToResponse(key) : null;
    }

    public async Task<KeyResponse> CreateKeyAsync(CreateKeyDto createKeyDto)
    {
        // Verify that server exists
        var serverExists = await _context.Servers.AnyAsync(s => s.Id == createKeyDto.ServerId);
        if (!serverExists)
            throw new ArgumentException($"Server with ID {createKeyDto.ServerId} does not exist.");

        var key = new Key
        {
            Name = createKeyDto.Name,
            Description = createKeyDto.Description,
            Price = createKeyDto.Price,
            ServerId = createKeyDto.ServerId,
            Sale = createKeyDto.Sale,
            Image = createKeyDto.Image
        };

        _context.Keys.Add(key);
        await _context.SaveChangesAsync();

        // Load the server for the response
        await _context.Entry(key)
            .Reference(k => k.Server)
            .LoadAsync();

        return MapToResponse(key);
    }

    public async Task<KeyResponse?> UpdateKeyAsync(int id, UpdateKeyDto updateKeyDto)
    {
        var key = await _context.Keys
            .Include(k => k.Server)
            .FirstOrDefaultAsync(k => k.Id == id);
            
        if (key == null)
            return null;

        // Verify that server exists if it's being changed
        if (key.ServerId != updateKeyDto.ServerId)
        {
            var serverExists = await _context.Servers.AnyAsync(s => s.Id == updateKeyDto.ServerId);
            if (!serverExists)
                throw new ArgumentException($"Server with ID {updateKeyDto.ServerId} does not exist.");
        }

        key.Name = updateKeyDto.Name;
        key.Description = updateKeyDto.Description;
        key.Price = updateKeyDto.Price;
        key.ServerId = updateKeyDto.ServerId;
        key.Sale = updateKeyDto.Sale;
        key.Image = updateKeyDto.Image;

        await _context.SaveChangesAsync();
        
        // Reload server if it changed
        if (key.ServerId != updateKeyDto.ServerId)
        {
            await _context.Entry(key)
                .Reference(k => k.Server)
                .LoadAsync();
        }

        return MapToResponse(key);
    }

    public async Task<bool> DeleteKeyAsync(int id)
    {
        var key = await _context.Keys.FindAsync(id);
        if (key == null)
            return false;

        _context.Keys.Remove(key);
        await _context.SaveChangesAsync();
        return true;
    }

    private static KeyResponse MapToResponse(Key key)
    {
        return new KeyResponse
        {
            Id = key.Id,
            Name = key.Name,
            Description = key.Description,
            Price = key.Price,
            ServerId = key.ServerId,
            Sale = key.Sale,
            Image = key.Image,
            Server = key.Server != null ? new ServerResponse
            {
                Id = key.Server.Id,
                Name = key.Server.Name,
                RCONIP = key.Server.RCONIP,
                RCONPort = key.Server.RCONPort,
                RCONPassword = key.Server.RCONPassword
            } : null
        };
    }
}