using System.Text.Json;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class TeamService : ITeamService
{
    private readonly ILogger<TeamService> _logger;
    private readonly string _dataPath;

    public TeamService(IHostEnvironment env, IConfiguration configuration, ILogger<TeamService> logger)
    {
        _logger = logger;
        var webRoot = configuration["WebRootPath"];
        if (string.IsNullOrEmpty(webRoot))
        {
            // default to content root + wwwroot
            webRoot = Path.Combine(env.ContentRootPath, "wwwroot");
        }

        _dataPath = Path.Combine(webRoot, "data");
        if (!Directory.Exists(_dataPath)) Directory.CreateDirectory(_dataPath);
    }

    private string TeamFile => Path.Combine(_dataPath, "team.json");

    public Task<TeamResponseDto> GetTeamAsync()
    {
        try
        {
            if (!System.IO.File.Exists(TeamFile))
            {
                var empty = new TeamResponseDto();
                System.IO.File.WriteAllText(TeamFile, JsonSerializer.Serialize(empty, new JsonSerializerOptions { WriteIndented = true }));
                return Task.FromResult(empty);
            }

            var json = System.IO.File.ReadAllText(TeamFile);
            var data = JsonSerializer.Deserialize<TeamResponseDto>(json) ?? new TeamResponseDto();
            return Task.FromResult(data);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error reading team data");
            return Task.FromResult(new TeamResponseDto());
        }
    }

    public Task SaveTeamAsync(TeamResponseDto dto)
    {
        try
        {
            var json = JsonSerializer.Serialize(dto, new JsonSerializerOptions { WriteIndented = true });
            System.IO.File.WriteAllText(TeamFile, json);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error saving team data");
            throw;
        }

        return Task.CompletedTask;
    }
}
