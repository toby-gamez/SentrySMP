# SentrySMP - Server Keys Management System

Systém pro správu klíčů serverů s tabulkami Server a Keys.

## Struktura databáze

### Tabulka `Servers`
- `Id` (int, PK, auto-increment)
- `Name` (string, max 100)
- `RCONIP` (string, max 50)
- `RCONPort` (int)
- `RCONPassword` (string, max 100)

### Tabulka `Keys`
- `Id` (int, PK, auto-increment)
- `Name` (string, max 100)
- `Description` (string, max 500)
- `Price` (double)
- `ServerId` (int, FK na Servers)
- `Sale` (double, 0-1 jako procenta slevy)
- `Image` (string, cesta k obrázku)

## Architektura projektu

### SentrySMP.Domain
- **Entities**: `Server.cs`, `Key.cs`

### SentrySMP.Api
- **Infrastructure/Data**: `SentryDbContext.cs`
- **Services**: `ServerService.cs`, `KeyService.cs`

### SentrySMP.Admin
- **Controllers**: `ServersController.cs`, `KeysController.cs`
- **Migrations**: Database migrace

### SentrySMP.Shared
- **DTOs**: `ServerResponse`, `CreateServerDto`, `UpdateServerDto`, `KeyResponse`, `CreateKeyDto`, `UpdateKeyDto`
- **Interfaces**: `IServerService`, `IKeyService`, `ISentryApi`

## API Endpoints

### Servers
- `GET /api/servers` - Získat všechny servery
- `GET /api/servers/{id}` - Získat server podle ID
- `POST /api/servers` - Vytvořit nový server
- `PUT /api/servers/{id}` - Aktualizovat server
- `DELETE /api/servers/{id}` - Smazat server

### Keys
- `GET /api/keys` - Získat všechny klíče
- `GET /api/keys/server/{serverId}` - Získat klíče podle serveru
- `GET /api/keys/{id}` - Získat klíč podle ID
- `POST /api/keys` - Vytvořit nový klíč
- `PUT /api/keys/{id}` - Aktualizovat klíč
- `DELETE /api/keys/{id}` - Smazat klíč

## Spuštění

1. Zkompilovat projekt:
```bash
dotnet build
```

2. Aplikovat migrace na databázi:
```bash
cd SentrySMP.Admin
dotnet ef database update --context SentryDbContext
```

3. Spustit Admin aplikaci:
```bash
cd SentrySMP.Admin
dotnet run
```

## Poznámky k obrázků
Pro pole `Image` v tabulce Keys doporučuji uložit cestu k souboru nebo URL. Můžeš implementovat upload endpoint pro nahrávání obrázků do složky `wwwroot/images/keys/` nebo použít externí úložiště jako Azure Blob Storage.

## Authentication
Systém používá Basic Authentication podle existující konfigurace.