using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Design;
using Microsoft.Extensions.Configuration;
using System.IO;

namespace SentrySMP.Api.Infrastructure.Data;

/// <summary>
/// Design-time factory for EF Core tools. It reads the connection string from
/// the environment (ConnectionStrings__DefaultConnection) or from appsettings.json
/// located next to the project. If not found, it will throw a helpful exception.
/// </summary>
public class DesignTimeDbContextFactory : IDesignTimeDbContextFactory<SentryDbContext>
{
    public SentryDbContext CreateDbContext(string[] args)
    {
        // Try environment first
        var conn = Environment.GetEnvironmentVariable("ConnectionStrings__DefaultConnection");

        // If not set, try appsettings.json next to the project
        if (string.IsNullOrEmpty(conn))
        {
            var basePath = Directory.GetCurrentDirectory();
            var configPath = Path.Combine(basePath, "appsettings.json");
            if (File.Exists(configPath))
            {
                var cfg = new ConfigurationBuilder()
                    .SetBasePath(basePath)
                    .AddJsonFile("appsettings.json")
                    .Build();

                conn = cfg.GetConnectionString("DefaultConnection");
            }
        }

        if (string.IsNullOrEmpty(conn))
        {
            throw new InvalidOperationException("Connection string for design-time DbContext not found. Set ConnectionStrings__DefaultConnection environment variable or provide appsettings.json with DefaultConnection.");
        }

        var optionsBuilder = new DbContextOptionsBuilder<SentryDbContext>();
        optionsBuilder.UseMySql(conn, ServerVersion.AutoDetect(conn), b => b.MigrationsAssembly("SentrySMP.Api"));

        return new SentryDbContext(optionsBuilder.Options);
    }
}
