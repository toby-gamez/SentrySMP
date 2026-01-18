using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;
using System.Linq;

namespace SentrySMP.Api.Services;

public class BundleService : IBundleService
{
    private readonly SentryDbContext _context;

    public BundleService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<BundleResponse>> GetAllBundlesAsync()
    {
        var bundles = await _context.Set<Bundle>()
            .Include(b => b.Server)
            .ToListAsync();

        var bundleIds = bundles.Select(b => b.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "BUNDLE" && bundleIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByBundle = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());

        return bundles.Select(b => MapToResponse(b, commandsByBundle.TryGetValue(b.Id, out var cmds) ? cmds : new List<Command>()));
    }

    public async Task<IEnumerable<BundleResponse>> GetBundlesByServerIdAsync(int serverId)
    {
        var bundles = await _context.Set<Bundle>()
            .Include(b => b.Server)
            .Where(b => b.ServerId == serverId)
            .ToListAsync();

        var bundleIds = bundles.Select(b => b.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "BUNDLE" && bundleIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByBundle = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());
        return bundles.Select(b => MapToResponse(b, commandsByBundle.TryGetValue(b.Id, out var cmds) ? cmds : new List<Command>()));
    }

    public async Task<BundleResponse?> GetBundleByIdAsync(int id)
    {
        var bundle = await _context.Set<Bundle>()
            .Include(b => b.Server)
            .FirstOrDefaultAsync(b => b.Id == id);

        if (bundle == null) return null;
        var commands = await _context.Commands.Where(c => c.Type == "BUNDLE" && c.TypeId == bundle.Id).ToListAsync();
        return MapToResponse(bundle, commands);
    }

    public async Task<BundleResponse> CreateBundleAsync(CreateBundleDto createBundleDto)
    {
        var serverExists = await _context.Servers.AnyAsync(s => s.Id == createBundleDto.ServerId);
        if (!serverExists)
            throw new ArgumentException($"Server with ID {createBundleDto.ServerId} does not exist.");

        var bundle = new Bundle
        {
            Name = createBundleDto.Name,
            Description = createBundleDto.Description,
            Price = createBundleDto.Price,
            ServerId = createBundleDto.ServerId,
            Sale = createBundleDto.Sale,
            Image = createBundleDto.Image,
            GlobalMaxOrder = createBundleDto.GlobalMaxOrder
        };

        _context.Set<Bundle>().Add(bundle);
        await _context.SaveChangesAsync();

        // If commands were provided, attach them
        if (createBundleDto.Commands != null && createBundleDto.Commands.Any())
        {
            var toAdd = createBundleDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "BUNDLE",
                TypeId = bundle.Id
            }).ToList();

            _context.Commands.AddRange(toAdd);
            await _context.SaveChangesAsync();
        }

        await _context.Entry(bundle).Reference(b => b.Server).LoadAsync();

        var commands = await _context.Commands.Where(c => c.Type == "BUNDLE" && c.TypeId == bundle.Id).ToListAsync();
        return MapToResponse(bundle, commands);
    }

    public async Task<BundleResponse?> UpdateBundleAsync(int id, UpdateBundleDto updateBundleDto)
    {
        var bundle = await _context.Set<Bundle>()
            .Include(b => b.Server)
            .FirstOrDefaultAsync(b => b.Id == id);

        if (bundle == null)
            return null;

        if (bundle.ServerId != updateBundleDto.ServerId)
        {
            var serverExists = await _context.Servers.AnyAsync(s => s.Id == updateBundleDto.ServerId);
            if (!serverExists)
                throw new ArgumentException($"Server with ID {updateBundleDto.ServerId} does not exist.");
        }

        bundle.Name = updateBundleDto.Name;
        bundle.Description = updateBundleDto.Description;
        bundle.Price = updateBundleDto.Price;
        bundle.ServerId = updateBundleDto.ServerId;
        bundle.Sale = updateBundleDto.Sale;
        bundle.Image = updateBundleDto.Image;
        bundle.GlobalMaxOrder = updateBundleDto.GlobalMaxOrder;
        // If commands are provided in the update, replace existing commands for this bundle
        if (updateBundleDto.Commands != null)
        {
            var existing = await _context.Commands.Where(c => c.Type == "BUNDLE" && c.TypeId == bundle.Id).ToListAsync();
            if (existing.Any())
                _context.Commands.RemoveRange(existing);

            var newCommands = updateBundleDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "BUNDLE",
                TypeId = bundle.Id
            }).ToList();
            if (newCommands.Any())
                _context.Commands.AddRange(newCommands);
        }

        await _context.SaveChangesAsync();
        
        // Reload server if it changed
        if (bundle.ServerId != updateBundleDto.ServerId)
        {
            await _context.Entry(bundle).Reference(b => b.Server).LoadAsync();
        }

        var commands = await _context.Commands.Where(c => c.Type == "BUNDLE" && c.TypeId == bundle.Id).ToListAsync();
        return MapToResponse(bundle, commands);
    }

    public async Task<bool> DeleteBundleAsync(int id)
    {
        var bundle = await _context.Set<Bundle>().FindAsync(id);
        if (bundle == null)
            return false;

        // Remove associated commands (cascade-like behavior)
        var commands = await _context.Commands.Where(c => c.Type == "BUNDLE" && c.TypeId == id).ToListAsync();
        if (commands.Any())
            _context.Commands.RemoveRange(commands);

        _context.Set<Bundle>().Remove(bundle);
        await _context.SaveChangesAsync();
        return true;
    }

    private BundleResponse MapToResponse(Bundle b, List<Command> commands)
    {
        var commandDtos = commands.Select(c => new CommandDto
        {
            Id = c.Id,
            CommandText = c.CommandText,
            Type = c.Type,
            TypeId = c.TypeId
        }).ToList();

        return new BundleResponse
        {
            Id = b.Id,
            Name = b.Name,
            Description = b.Description,
            Price = b.Price,
            ServerId = b.ServerId,
            Sale = b.Sale,
            Image = b.Image,
            GlobalMaxOrder = b.GlobalMaxOrder,
            Server = b.Server != null ? new ServerResponse
            {
                Id = b.Server.Id,
                Name = b.Server.Name,
                RCONIP = b.Server.RCONIP,
                RCONPort = b.Server.RCONPort,
                RCONPassword = b.Server.RCONPassword
            } : null,
            Commands = commandDtos
        };
    }
}
