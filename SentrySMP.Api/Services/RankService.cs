using Microsoft.EntityFrameworkCore;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class RankService : IRankService
{
    private readonly SentryDbContext _context;

    public RankService(SentryDbContext context)
    {
        _context = context;
    }

    public async Task<IEnumerable<RankResponse>> GetAllRanksAsync()
    {
        var ranks = await _context.Set<Rank>().ToListAsync();

        // If commands exist for ranks, fetch them (Type == "RANK")
        var rankIds = ranks.Select(r => r.Id).ToList();
        var allCommands = await _context.Commands
            .Where(c => c.Type == "RANK" && rankIds.Contains(c.TypeId))
            .ToListAsync();

        var commandsByRank = allCommands.GroupBy(c => c.TypeId).ToDictionary(g => g.Key, g => g.ToList());

        return ranks.Select(r => MapToResponse(r, commandsByRank.TryGetValue(r.Id, out var cmds) ? cmds : new List<Command>()));
    }

    

    public async Task<RankResponse?> GetRankByIdAsync(int id)
    {
        var rank = await _context.Set<Rank>().FirstOrDefaultAsync(r => r.Id == id);

        if (rank == null) return null;
        var commands = await _context.Commands.Where(c => c.Type == "RANK" && c.TypeId == rank.Id).ToListAsync();
        return MapToResponse(rank, commands);
    }

    public async Task<RankResponse> CreateRankAsync(CreateRankDto createRankDto)
    {

        var rank = new Rank
        {
            Name = createRankDto.Name,
            Description = createRankDto.Description,
            Price = createRankDto.Price,
            Sale = createRankDto.Sale,
            Image = createRankDto.Image
        };

        _context.Set<Rank>().Add(rank);
        await _context.SaveChangesAsync();

        // Persist commands if supplied
        if (createRankDto.Commands != null && createRankDto.Commands.Any())
        {
            var commandEntities = createRankDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "RANK",
                TypeId = rank.Id
            }).ToList();
            _context.Commands.AddRange(commandEntities);
            await _context.SaveChangesAsync();
        }

        var commands = await _context.Commands.Where(c => c.Type == "RANK" && c.TypeId == rank.Id).ToListAsync();
        return MapToResponse(rank, commands);
    }

    public async Task<RankResponse?> UpdateRankAsync(int id, UpdateRankDto updateRankDto)
    {
        var rank = await _context.Set<Rank>()
            .FirstOrDefaultAsync(r => r.Id == id);

        if (rank == null)
            return null;


        rank.Name = updateRankDto.Name;
        rank.Description = updateRankDto.Description;
        rank.Price = updateRankDto.Price;
        rank.Sale = updateRankDto.Sale;
        rank.Image = updateRankDto.Image;

        await _context.SaveChangesAsync();

        // Replace commands if provided
        if (updateRankDto.Commands != null)
        {
            var existing = await _context.Commands.Where(c => c.Type == "RANK" && c.TypeId == rank.Id).ToListAsync();
            if (existing.Any()) _context.Commands.RemoveRange(existing);

            var newCommands = updateRankDto.Commands.Select(c => new Command
            {
                CommandText = c.CommandText,
                Type = "RANK",
                TypeId = rank.Id
            }).ToList();

            if (newCommands.Any())
            {
                _context.Commands.AddRange(newCommands);
            }

            await _context.SaveChangesAsync();
        }

        var commands = await _context.Commands.Where(c => c.Type == "RANK" && c.TypeId == rank.Id).ToListAsync();
        return MapToResponse(rank, commands);
    }

    public async Task<bool> DeleteRankAsync(int id)
    {
        var rank = await _context.Set<Rank>().FindAsync(id);
        if (rank == null)
            return false;

        var commands = await _context.Commands.Where(c => c.Type == "RANK" && c.TypeId == id).ToListAsync();
        if (commands.Any())
            _context.Commands.RemoveRange(commands);

        _context.Set<Rank>().Remove(rank);
        await _context.SaveChangesAsync();
        return true;
    }

    private RankResponse MapToResponse(Rank rank, List<Command> commands)
    {
        var commandDtos = commands.Select(c => new CommandDto
        {
            Id = c.Id,
            CommandText = c.CommandText,
            Type = c.Type,
            TypeId = c.TypeId
        }).ToList();

        return new RankResponse
        {
            Id = rank.Id,
            Name = rank.Name,
            Description = rank.Description,
            Price = rank.Price,
            Sale = rank.Sale,
            Image = rank.Image,
            // Attach mapped commands
            Commands = commandDtos
        };
    }
}
