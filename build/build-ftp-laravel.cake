#addin nuget:?package=FluentFTP&version=50.0.0
#addin nuget:?package=Newtonsoft.Json&version=13.0.1

using FluentFTP;
using Newtonsoft.Json;
using System.Net;
using System.IO;

public class Config
{
    public string FtpHost { get; set; }
    public string FtpUsername { get; set; }
    public string FtpPassword { get; set; }
    public string FtpRemoteDirectory { get; set; }
    public string SourceDirectory { get; set; }
    public string StagingDirectory { get; set; }
}

var configFilePath = "./build-ftp-laravel.json";
var configJson = System.IO.File.ReadAllText(configFilePath);
var config = JsonConvert.DeserializeObject<Config>(configJson);

config.SourceDirectory = MakeAbsolute(Directory(config.SourceDirectory)).FullPath;

// Whitelist: only these top-level paths from the Laravel app are staged and uploaded.
// vendor/ is excluded here — composer install generates it fresh in staging.
var includedTopLevelPaths = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
{
    "app", "artisan", "bootstrap", "composer.json", "composer.lock",
    "config", "database", "public", "resources", "routes", "storage"
};

var excludedRelativePaths = new[]
{
    "storage/framework/cache",
    "storage/framework/sessions",
    "storage/framework/testing",
    "storage/framework/views",
    "bootstrap/cache",
};

var excludedFileExtensions = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
{
    ".log", ".cache"
};

var excludedFileNames = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
{
    ".env", ".env.example", ".phpunit.result.cache"
};

Task("PrepareFiles")
    .Does(() =>
    {
        if (System.IO.Directory.Exists(config.StagingDirectory))
        {
            LogInformation($"Cleaning staging directory: {config.StagingDirectory}");
            System.IO.Directory.Delete(config.StagingDirectory, true);
        }

        System.IO.Directory.CreateDirectory(config.StagingDirectory);
        LogInformation($"Copying files from {config.SourceDirectory} to {config.StagingDirectory}");

        var allFiles = System.IO.Directory.GetFiles(config.SourceDirectory, "*", System.IO.SearchOption.AllDirectories);
        int copied = 0;

        foreach (var file in allFiles)
        {
            var relativePath = file.Substring(config.SourceDirectory.Length).TrimStart(System.IO.Path.DirectorySeparatorChar, '/');
            var normalizedRelative = relativePath.Replace("\\", "/");

            var topLevel = normalizedRelative.Split('/')[0];
            if (!includedTopLevelPaths.Contains(topLevel))
                continue;

            if (excludedRelativePaths.Any(p => normalizedRelative.StartsWith(p + "/") || normalizedRelative == p))
                continue;

            var fileName = System.IO.Path.GetFileName(file);
            if (excludedFileNames.Contains(fileName))
                continue;

            var ext = System.IO.Path.GetExtension(file);
            if (excludedFileExtensions.Contains(ext))
                continue;

            var destFile = System.IO.Path.Combine(config.StagingDirectory, relativePath);
            var destDir = System.IO.Path.GetDirectoryName(destFile);
            System.IO.Directory.CreateDirectory(destDir);
            System.IO.File.Copy(file, destFile, overwrite: true);
            copied++;
        }

        LogInformation($"Copied {copied} files to staging.");

        // Required by composer post-install scripts (package:discover)
        System.IO.Directory.CreateDirectory(System.IO.Path.Combine(config.StagingDirectory, "bootstrap", "cache"));

        // Laravel requires these directories to exist on the server (they are gitignored).
        // A .gitkeep ensures FTP uploads them as non-empty entries.
        var requiredDirs = new[]
        {
            "storage/app/public",
            "storage/framework/cache/data",
            "storage/framework/sessions",
            "storage/framework/testing",
            "storage/framework/views",
            "storage/logs",
        };
        foreach (var dir in requiredDirs)
        {
            var path = System.IO.Path.Combine(config.StagingDirectory, dir.Replace("/", System.IO.Path.DirectorySeparatorChar.ToString()));
            System.IO.Directory.CreateDirectory(path);
            System.IO.File.WriteAllText(System.IO.Path.Combine(path, ".gitkeep"), "");
        }

        LogInformation("Running composer install --no-dev --optimize-autoloader...");
        var exitCode = StartProcess("composer", new ProcessSettings
        {
            Arguments = "install --no-dev --optimize-autoloader",
            WorkingDirectory = config.StagingDirectory
        });

        if (exitCode != 0)
            throw new Exception($"composer install failed with exit code {exitCode}");

        LogInformation("composer install completed.");

        // Place maintenance file in staging so it is synced first and activates maintenance mode.
        var maintenanceDir = System.IO.Path.Combine(config.StagingDirectory, "storage", "framework");
        System.IO.Directory.CreateDirectory(maintenanceDir);
        System.IO.File.WriteAllText(
            System.IO.Path.Combine(maintenanceDir, "maintenance.php"),
            "<?php return ['except'=>[],'redirect'=>null,'retry'=>null,'refresh'=>null,'secret'=>null,'status'=>503,'template'=>null];"
        );
    });

Task("Sync")
    .IsDependentOn("PrepareFiles")
    .Does(async () =>
    {
        if (string.IsNullOrEmpty(config.FtpPassword))
            throw new ArgumentException("FTP password must be provided in the config file.");

        // Hash-based manifest persisted next to the config file so it survives between runs.
        // Timestamp comparison is unreliable because staging is always recreated fresh (files
        // get "now" as their LastWriteTime), so every file would look newer than the remote.
        var manifestPath = "./build-ftp-laravel.manifest.json";
        var manifest = System.IO.File.Exists(manifestPath)
            ? JsonConvert.DeserializeObject<Dictionary<string, string>>(System.IO.File.ReadAllText(manifestPath)) ?? new()
            : new Dictionary<string, string>();

        using var client = new AsyncFtpClient(config.FtpHost, config.FtpUsername, config.FtpPassword);
        await client.Connect();
        LogInformation($"Connected to FTP server {config.FtpHost}");
        LogInformation($"Syncing {config.StagingDirectory} → {config.FtpRemoteDirectory}");
        LogInformation("Skipping unchanged files (hash-based delta-sync). First deploy will upload everything.");

        LogInformation("Fetching remote file listing...");
        var remoteListing = await client.GetListing(config.FtpRemoteDirectory, FtpListOption.Recursive);
        var remoteFilePaths = remoteListing
            .Where(i => i.Type == FtpObjectType.File)
            .Select(i => i.FullName)
            .ToHashSet(StringComparer.OrdinalIgnoreCase);

        var localFiles = System.IO.Directory.GetFiles(config.StagingDirectory, "*", System.IO.SearchOption.AllDirectories);
        var localRemotePaths = new HashSet<string>(StringComparer.OrdinalIgnoreCase);
        int uploaded = 0, skipped = 0, failed = 0;
        int total = localFiles.Length;
        int processed = 0;

        foreach (var localFile in localFiles)
        {
            var rel = localFile.Substring(config.StagingDirectory.Length).TrimStart(System.IO.Path.DirectorySeparatorChar, '/').Replace("\\", "/");
            var remotePath = config.FtpRemoteDirectory.TrimEnd('/') + "/" + rel;
            localRemotePaths.Add(remotePath);

            var hash = ComputeFileMd5(localFile);
            var existsOnRemote = remoteFilePaths.Contains(remotePath);

            if (existsOnRemote && manifest.TryGetValue(rel, out var cachedHash) && cachedHash == hash)
            {
                skipped++;
            }
            else
            {
                try
                {
                    await client.UploadFile(localFile, remotePath, FtpRemoteExists.Overwrite, true);
                    manifest[rel] = hash;
                    LogInformation($"Uploaded: {rel}");
                    uploaded++;
                }
                catch (Exception ex)
                {
                    LogError($"Failed: {rel} — {ex.Message}");
                    failed++;
                }
            }

            processed++;
            UpdateProgressBar(total, processed);
        }

        // Files that live only on the server and must never be deleted by the sync.
        var neverDelete = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
        {
            ".env", ".env.production", ".env.local", "web.config"
        };

        // Mirror: delete remote files not present in staging
        int deleted = 0;
        foreach (var remoteFile in remoteListing.Where(i => i.Type == FtpObjectType.File))
        {
            var fileName = System.IO.Path.GetFileName(remoteFile.FullName);
            if (neverDelete.Contains(fileName))
                continue;

            if (!localRemotePaths.Contains(remoteFile.FullName))
            {
                await client.DeleteFile(remoteFile.FullName);
                var relDeleted = remoteFile.FullName.Substring(config.FtpRemoteDirectory.Length).TrimStart('/');
                manifest.Remove(relDeleted);
                LogInformation($"Deleted: {remoteFile.FullName}");
                deleted++;
            }
        }

        System.IO.File.WriteAllText(manifestPath, JsonConvert.SerializeObject(manifest, Formatting.Indented));
        LogInformation($"Sync complete — uploaded: {uploaded}, skipped: {skipped}, deleted: {deleted}, failed: {failed}");
    });

Task("RemoveMaintenanceFile")
    .IsDependentOn("Sync")
    .Does(async () =>
    {
        using var client = new AsyncFtpClient(config.FtpHost, config.FtpUsername, config.FtpPassword);
        await client.Connect();

        var remotePath = config.FtpRemoteDirectory + "/storage/framework/maintenance.php";
        if (await client.FileExists(remotePath))
        {
            await client.DeleteFile(remotePath);
            LogInformation("Maintenance mode disabled. Site is back online.");
        }
    });

Task("Cleanup")
    .IsDependentOn("RemoveMaintenanceFile")
    .Does(() =>
    {
        if (System.IO.Directory.Exists(config.StagingDirectory))
        {
            System.IO.Directory.Delete(config.StagingDirectory, true);
            Information($"Deleted local staging directory: {config.StagingDirectory}");
        }
    });

Task("Default")
    .IsDependentOn("Cleanup");

RunTarget("Default");

void UpdateProgressBar(int total, int current)
{
    int width = 50;
    int progress = total == 0 ? 0 : (int)((double)current / total * width);
    string bar = "[" + new string('#', progress) + new string(' ', width - progress) + $"] {current}/{total}";
    Console.SetCursorPosition(0, Console.CursorTop);
    Console.Write(new string(' ', Console.WindowWidth));
    Console.SetCursorPosition(0, Console.CursorTop);
    Console.Write(bar);
}

void LogInformation(string message)
{
    Console.SetCursorPosition(0, Console.CursorTop);
    Console.Write(new string(' ', Console.WindowWidth));
    Console.SetCursorPosition(0, Console.CursorTop);
    Console.WriteLine(message);
}

void LogError(string message)
{
    Console.SetCursorPosition(0, Console.CursorTop);
    Console.Write(new string(' ', Console.WindowWidth));
    Console.SetCursorPosition(0, Console.CursorTop);
    Console.WriteLine($"ERROR: {message}");
}

string ComputeFileMd5(string filePath)
{
    using var md5 = System.Security.Cryptography.MD5.Create();
    using var stream = System.IO.File.OpenRead(filePath);
    return BitConverter.ToString(md5.ComputeHash(stream)).Replace("-", "").ToLowerInvariant();
}
