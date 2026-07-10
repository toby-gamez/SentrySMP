using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class CoinService : ICoinService
{
    private readonly SentryDbContext _context;

    public CoinService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<CoinResponse>> GetAllCoinsAsync()
    {
        var coins = await _context.Coins
            .Include(s => s.Server)
            .ToListAsync();
        var coinIds = coins.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "COIN" && coinIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByCoin = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());

        var responses = coins.Select(s => MapToResponse(s, commandsByCoin.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<IEnumerable<CoinResponse>> GetCoinsByServerIdAsync(int serverId)
    {
        var coins = await _context.Coins
            .Include(s => s.Server)
            .Where(s => s.ServerId == serverId)
            .ToListAsync();
        var coinIds = coins.Select(s => s.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "COIN" && coinIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByCoin = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());
        var responses = coins.Select(s => MapToResponse(s, commandsByCoin.TryGetValue(s.Id, out var cmds) ? cmds : new List<Command>()));
        return responses;
    }

    public async Task<CoinResponse?> GetCoinByIdAsync(int id)
    {
        var coin = await _context.Coins
            .Include(s => s.Server)
            .FirstOrDefaultAsync(s => s.Id == id);
        if (coin == null) return null;
        var commands = await _context.Commands.Where(c => c.Type == "COIN" && c.TypeId == coin.Id).ToListAsync();
        return MapToResponse(coin, commands);
    }

    public async Task<CoinResponse> CreateCoinAsync(CreateCoinDto createCoinDto)
    {
        var serverExists = await _context.Servers.AnyAsync(s => s.Id == createCoinDto.ServerId);
        if (!serverExists)
            throw new ArgumentException($"Server with ID {createCoinDto.ServerId} does not exist.");

        var coin = new Coin
        {
            Name = createCoinDto.Name,
            Description = createCoinDto.Description,
            Price = createCoinDto.Price,
            ServerId = createCoinDto.ServerId,
            Sale = createCoinDto.Sale,
            Image = createCoinDto.Image,
            GlobalMaxOrder = createCoinDto.GlobalMaxOrder
        };
        _context.Coins.Add(coin);
        await _context.SaveChangesAsync();
        // Attach commands if provided
        if (createCoinDto.Commands != null && createCoinDto.Commands.Any())
        {
            var toAdd = createCoinDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "COIN",
                TypeId = coin.Id
            }).ToList();
            _context.Commands.AddRange(toAdd);
            await _context.SaveChangesAsync();
        }

        var commands = await _context.Commands.Where(c => c.Type == "COIN" && c.TypeId == coin.Id).ToListAsync();
        return MapToResponse(coin, commands);
    }

    public async Task<CoinResponse?> UpdateCoinAsync(int id, UpdateCoinDto updateCoinDto)
    {
        var coin = await _context.Coins.FindAsync(id);
        if (coin == null) return null;
        coin.Name = updateCoinDto.Name;
        coin.Description = updateCoinDto.Description;
        coin.Price = updateCoinDto.Price;
        coin.ServerId = updateCoinDto.ServerId;
        coin.Sale = updateCoinDto.Sale;
        coin.Image = updateCoinDto.Image;
        coin.GlobalMaxOrder = updateCoinDto.GlobalMaxOrder;
        // If commands provided, replace existing
        if (updateCoinDto.Commands != null)
        {
            var existing = await _context.Commands.Where(c => c.Type == "COIN" && c.TypeId == coin.Id).ToListAsync();
            if (existing.Any())
                _context.Commands.RemoveRange(existing);

            var newCommands = updateCoinDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "COIN",
                TypeId = coin.Id
            }).ToList();
            if (newCommands.Any())
                _context.Commands.AddRange(newCommands);
        }
        await _context.SaveChangesAsync();
        var commands = await _context.Commands.Where(c => c.Type == "COIN" && c.TypeId == coin.Id).ToListAsync();
        return MapToResponse(coin, commands);
    }

    public async Task<bool> DeleteCoinAsync(int id)
    {
        var coin = await _context.Coins.FindAsync(id);
        if (coin == null) return false;
        // Remove associated commands (cascade-like)
        var commands = await _context.Commands.Where(c => c.Type == "COIN" && c.TypeId == id).ToListAsync();
        if (commands.Any())
            _context.Commands.RemoveRange(commands);

        _context.Coins.Remove(coin);
        await _context.SaveChangesAsync();
        return true;
    }

    private static CoinResponse MapToResponse(Coin coin)
    {
        return new CoinResponse
        {
            Id = coin.Id,
            Name = coin.Name,
            Description = coin.Description,
            Price = coin.Price,
            Sale = coin.Sale,
            Image = coin.Image,
            Server = coin.Server != null ? new ServerResponse
            {
                Id = coin.Server.Id,
                Name = coin.Server.Name
            } : null
        };
    }
    private CoinResponse MapToResponse(Coin coin, List<Command> commands)
    {
        var commandDtos = commands.Select(c => new CommandDto
        {
            Id = c.Id,
            CommandText = c.CommandText,
            Type = c.Type,
            TypeId = c.TypeId
        }).ToList();

        return new CoinResponse
        {
            Id = coin.Id,
            Name = coin.Name,
            Description = coin.Description,
            Price = coin.Price,
            Sale = coin.Sale,
            Image = coin.Image,
            GlobalMaxOrder = coin.GlobalMaxOrder,
            Server = coin.Server != null ? new ServerResponse
            {
                Id = coin.Server.Id,
                Name = coin.Server.Name
            } : null,
            Commands = commandDtos
        };
    }
}
