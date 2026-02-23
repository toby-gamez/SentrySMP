using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class OtherService : IOtherService
{
    private readonly SentryDbContext _context;

    public OtherService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<OtherResponse>> GetAllOthersAsync()
    {
        var items = await _context.Others
            .Include(s => s.Server)
            .ToListAsync();
        var ids = items.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "OTHER" && ids.Contains(c.TypeId))
            .ToListAsync();

        var commandsBy = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());
        var responses = items.Select(s => MapToResponse(s, commandsBy.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<IEnumerable<OtherResponse>> GetOthersByServerIdAsync(int serverId)
    {
        var items = await _context.Others
            .Include(s => s.Server)
            .Where(s => s.ServerId == serverId)
            .ToListAsync();
        var ids = items.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "OTHER" && ids.Contains(c.TypeId))
            .ToListAsync();

        var commandsBy = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());
        var responses = items.Select(s => MapToResponse(s, commandsBy.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<OtherResponse?> GetOtherByIdAsync(int id)
    {
        var item = await _context.Others.Include(s => s.Server).FirstOrDefaultAsync(s => s.Id == id);
        if (item == null) return null;
        var commands = await _context.Commands.Where(c => c.Type == "OTHER" && c.TypeId == item.Id).ToListAsync();
        return MapToResponse(item, commands);
    }

    public async Task<OtherResponse> CreateOtherAsync(CreateOtherDto createOtherDto)
    {
        var serverExists = await _context.Servers.AnyAsync(s => s.Id == createOtherDto.ServerId);
        if (!serverExists)
            throw new ArgumentException($"Server with ID {createOtherDto.ServerId} does not exist.");

        var entity = new Other
        {
            Name = createOtherDto.Name,
            Description = createOtherDto.Description,
            Price = createOtherDto.Price,
            ServerId = createOtherDto.ServerId,
            Sale = createOtherDto.Sale,
            Image = createOtherDto.Image,
            GlobalMaxOrder = createOtherDto.GlobalMaxOrder
        };
        _context.Others.Add(entity);
        await _context.SaveChangesAsync();

        if (createOtherDto.Commands != null && createOtherDto.Commands.Any())
        {
            var toAdd = createOtherDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "OTHER",
                TypeId = entity.Id
            }).ToList();
            _context.Commands.AddRange(toAdd);
            await _context.SaveChangesAsync();
        }

        var commands = await _context.Commands.Where(c => c.Type == "OTHER" && c.TypeId == entity.Id).ToListAsync();
        return MapToResponse(entity, commands);
    }

    public async Task<OtherResponse?> UpdateOtherAsync(int id, UpdateOtherDto updateOtherDto)
    {
        var entity = await _context.Others.FindAsync(id);
        if (entity == null) return null;
        entity.Name = updateOtherDto.Name;
        entity.Description = updateOtherDto.Description;
        entity.Price = updateOtherDto.Price;
        entity.ServerId = updateOtherDto.ServerId;
        entity.Sale = updateOtherDto.Sale;
        entity.Image = updateOtherDto.Image;
        entity.GlobalMaxOrder = updateOtherDto.GlobalMaxOrder;

        if (updateOtherDto.Commands != null)
        {
            var existing = await _context.Commands.Where(c => c.Type == "OTHER" && c.TypeId == entity.Id).ToListAsync();
            if (existing.Any())
                _context.Commands.RemoveRange(existing);

            var newCommands = updateOtherDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "OTHER",
                TypeId = entity.Id
            }).ToList();
            if (newCommands.Any())
                _context.Commands.AddRange(newCommands);
        }

        await _context.SaveChangesAsync();
        var commands = await _context.Commands.Where(c => c.Type == "OTHER" && c.TypeId == entity.Id).ToListAsync();
        return MapToResponse(entity, commands);
    }

    public async Task<bool> DeleteOtherAsync(int id)
    {
        var entity = await _context.Others.FindAsync(id);
        if (entity == null) return false;
        var commands = await _context.Commands.Where(c => c.Type == "OTHER" && c.TypeId == id).ToListAsync();
        if (commands.Any())
            _context.Commands.RemoveRange(commands);

        _context.Others.Remove(entity);
        await _context.SaveChangesAsync();
        return true;
    }

    private OtherResponse MapToResponse(Other other)
    {
        return new OtherResponse
        {
            Id = other.Id,
            Name = other.Name,
            Description = other.Description,
            Price = other.Price,
            Sale = other.Sale,
            Image = other.Image,
            Server = other.Server != null ? new ServerResponse
            {
                Id = other.Server.Id,
                Name = other.Server.Name,
                RCONIP = other.Server.RCONIP,
                RCONPort = other.Server.RCONPort,
                RCONPassword = other.Server.RCONPassword
            } : null
        };
    }

    private OtherResponse MapToResponse(Other other, List<Command> commands)
    {
        var commandDtos = commands.Select(c => new CommandDto
        {
            Id = c.Id,
            CommandText = c.CommandText,
            Type = c.Type,
            TypeId = c.TypeId
        }).ToList();

        return new OtherResponse
        {
            Id = other.Id,
            Name = other.Name,
            Description = other.Description,
            Price = other.Price,
            Sale = other.Sale,
            Image = other.Image,
            GlobalMaxOrder = other.GlobalMaxOrder,
            Server = other.Server != null ? new ServerResponse
            {
                Id = other.Server.Id,
                Name = other.Server.Name,
                RCONIP = other.Server.RCONIP,
                RCONPort = other.Server.RCONPort,
                RCONPassword = other.Server.RCONPassword
            } : null,
            Commands = commandDtos
        };
    }
}
