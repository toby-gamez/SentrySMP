# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SentrySMP is a Minecraft SMP shopping website built with ASP.NET 8 and Blazor Server. It allows players to purchase in-game items (keys, ranks, bundles, coins, battle passes) and handles delivery via a game server HTTP API.

## Commands

```bash
# Build entire solution
dotnet build

# Run the main App (port 7271 in dev)
dotnet run --project SentrySMP.App

# Run the Admin panel
dotnet run --project SentrySMP.Admin

# Run the Images microservice
dotnet run --project SentrySMP.Images

# Add a new EF Core migration
dotnet ef migrations add <MigrationName> --context SentryDbContext --project SentrySMP.Api --startup-project SentrySMP.App

# Apply migrations
dotnet ef database update --context SentryDbContext --project SentrySMP.Api --startup-project SentrySMP.App

# Deploy via Cake (FTP) — run from the build/ directory
dotnet cake build-ftp.cake         # deploys App
dotnet cake build-ftp-admin.cake   # deploys Admin
dotnet cake build-ftp-images.cake  # deploys Images
```

## Architecture

### Solution Projects

| Project | Type | Role |
|---|---|---|
| `SentrySMP.App` | Blazor Server Web App | Main public-facing shop frontend + embedded API controllers |
| `SentrySMP.Admin` | Blazor Server Web App | Admin panel for content management |
| `SentrySMP.Api` | Class Library | Shared API services, EF Core DbContext, and migrations — **not a standalone process** |
| `SentrySMP.Domain` | Class Library | EF Core entity models only |
| `SentrySMP.Shared` | Class Library | DTOs, service interfaces, and Refit API contracts |
| `SentrySMP.Images` | ASP.NET Web API | Standalone microservice for image storage and serving |

### Key Design Decisions

**`SentrySMP.Api` is a class library embedded into both App and Admin.** It is not a standalone service. Both `SentrySMP.App` and `SentrySMP.Admin` reference it and register its services (e.g. `KeyService`, `RankService`) directly via DI. This means both projects share the same service implementations and talk directly to the MySQL database.

**Refit clients for cross-service HTTP calls.** `ISentryApi` (defined in `SentrySMP.Shared/Interfaces/ISentryApi.cs`) is the Refit contract for calling App API endpoints from Admin. `IImagesApi` talks to `SentrySMP.Images`. `ICheckoutApi` handles PayPal checkout flows.

**Authentication is Basic Auth** via a custom `BasicAuthHandler` in each web project. Credentials are configured in `appsettings.json` under `Auth:Basic`.

**Command delivery** to the Minecraft game server uses `CommandDeliveryService` (HTTP API) rather than direct RCON. The game server URL is configured under `GameServer:BaseUrl`.

**Background task queue** (`BackgroundTaskQueue` + `BackgroundTaskQueueHostedService` in App) handles fire-and-forget work like RCON/delivery jobs after purchase.

**Cart state** (`CartState` in `SentrySMP.App/Components/State/`) is scoped and persists to `localStorage` via JS interop.

### Data Layer

- Database: MySQL (Pomelo EF Core provider)
- `SentryDbContext` is defined in `SentrySMP.Api/Infrastructure/Data/SentryDbContext.cs`
- Migrations live in `SentrySMP.Api/Migrations/` (startup project must be App or Admin when running EF CLI commands)
- A second `TobisoDbContext` exists in the same folder for a separate database

### Configuration

Copy `appsettings.template.json` to `appsettings.Development.json` in `SentrySMP.App` and `SentrySMP.Admin` and fill in real values. The App also loads a `.env` file at startup via DotNetEnv. Key config sections:

- `ConnectionStrings:DefaultConnection` — MySQL connection string
- `Auth:Basic` — username/password for Basic Auth
- `Api:BaseAddress` — base URL the Refit `ISentryApi` client points to
- `Images:BaseAddress` — base URL for the Images microservice
- `GameServer:BaseUrl` + `GameServer:ApiKey` — game server delivery API

### Adding a New Shop Product Type

1. Add entity to `SentrySMP.Domain/Entities/`
2. Add DTOs to `SentrySMP.Shared/DTOs/`
3. Add service interface to `SentrySMP.Shared/Interfaces/`
4. Add `DbSet` to `SentryDbContext` and run a migration
5. Implement service in `SentrySMP.Api/Services/`
6. Register service in both `SentrySMP.App/Program.cs` and `SentrySMP.Admin/Program.cs`
7. Extend `ISentryApi` with new Refit endpoints
8. Add Blazor pages in `SentrySMP.App/Components/Pages/` and `SentrySMP.Admin/Components/Pages/`

### Deployment

Deployment uses Cake (C# Make) scripts in `/build/`. Each script reads a corresponding `.json` config (based on `build-ftp.json.dist`), publishes the project, and uploads via FTP. The `app_offline.htm` technique is used to take the app offline during deployment.
