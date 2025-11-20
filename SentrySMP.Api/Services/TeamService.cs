using System.Text.Json;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using Microsoft.EntityFrameworkCore;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Domain.Entities;

namespace SentrySMP.Api.Services;

public class TeamService : ITeamService
{
    private readonly ILogger<TeamService> _logger;
    private readonly SentryDbContext _db;

    public TeamService(IHostEnvironment env, IConfiguration configuration, ILogger<TeamService> logger, SentryDbContext db)
    {
        _logger = logger;
        _db = db;

        // Persistence is DB-only. We intentionally do not read or write a local team.json file here.
        _logger.LogDebug("TeamService initialized using DB-only persistence (no team.json import).");
    }

    public async Task<TeamResponseDto> GetTeamAsync()
    {
        try
        {
            var categories = await _db.TeamCategories
                .Include(c => c.Members)
                .AsNoTracking()
                .ToListAsync();

            var dto = new TeamResponseDto
            {
                Categories = categories.Select(c => new TeamCategoryDto
                {
                    Id = c.Id,
                    Name = c.Name,
                    Members = c.Members.Select(m => new TeamMemberDto
                    {
                        Id = m.Id,
                        MinecraftName = m.MinecraftName,
                        Role = m.Role,
                        SkinUrl = m.SkinUrl
                    }).ToList()
                }).ToList()
            };

            return dto;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error reading team data from DB");
            return new TeamResponseDto();
        }
    }

    public async Task SaveTeamAsync(TeamResponseDto dto)
    {
        try
        {
            // Simple approach: replace everything with the DTO contents
            using var tx = await _db.Database.BeginTransactionAsync();

            // Remove existing data
            var existingMembers = _db.TeamMembers.AsQueryable();
            var existingCats = _db.TeamCategories.Include(c => c.Members).ToList();
            if (existingMembers.Any()) _db.TeamMembers.RemoveRange(existingMembers);
            if (existingCats.Any()) _db.TeamCategories.RemoveRange(existingCats);
            await _db.SaveChangesAsync();

            // Insert new
            foreach (var cat in dto.Categories)
            {
                var entityCat = new TeamCategory { Id = cat.Id ?? Guid.NewGuid().ToString(), Name = cat.Name };
                foreach (var mem in cat.Members)
                {
                    entityCat.Members.Add(new TeamMember
                    {
                        Id = mem.Id ?? Guid.NewGuid().ToString(),
                        MinecraftName = mem.MinecraftName,
                        Role = mem.Role,
                        SkinUrl = mem.SkinUrl,
                        TeamCategoryId = entityCat.Id
                    });
                }
                _db.TeamCategories.Add(entityCat);
            }

            await _db.SaveChangesAsync();
            await tx.CommitAsync();

            // NOTE: we intentionally do NOT write back to team.json; persistence is DB-only now.
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error saving team data to DB");
            throw;
        }
    }
}
