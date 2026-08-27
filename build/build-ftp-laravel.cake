#addin nuget:?package=FluentFTP&version=34.0.0
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

var excludedTopDirs = new HashSet<string>(StringComparer.OrdinalIgnoreCase)
{
    ".git", "node_modules", ".idea", ".vscode", "build", "tools"
};

var excludedRelativePaths = new[]
{
    "storage/logs",
    "storage/framework/cache",
    "storage/framework/sessions",
    "storage/framework/testing",
    "storage/framework/views",
    "bootstrap/cache",
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

            var topDir = normalizedRelative.Split('/')[0];
            if (excludedTopDirs.Contains(topDir))
                continue;

            if (excludedRelativePaths.Any(p => normalizedRelative.StartsWith(p + "/") || normalizedRelative == p))
                continue;

            var fileName = System.IO.Path.GetFileName(file);
            if (excludedFileNames.Contains(fileName))
                continue;

            var destFile = System.IO.Path.Combine(config.StagingDirectory, relativePath);
            var destDir = System.IO.Path.GetDirectoryName(destFile);
            System.IO.Directory.CreateDirectory(destDir);
            System.IO.File.Copy(file, destFile, overwrite: true);
            copied++;
        }

        LogInformation($"Copied {copied} files to staging.");

        LogInformation("Running composer install --no-dev --optimize-autoloader...");
        var exitCode = StartProcess("composer", new ProcessSettings
        {
            Arguments = "install --no-dev --optimize-autoloader",
            WorkingDirectory = config.StagingDirectory
        });

        if (exitCode != 0)
            throw new Exception($"composer install failed with exit code {exitCode}");

        LogInformation("composer install completed.");
    });

Task("CleanRemote")
    .IsDependentOn("PrepareFiles")
    .Does(async () =>
    {
        if (string.IsNullOrEmpty(config.FtpPassword))
            throw new ArgumentException("FTP password must be provided in the config file.");

        var remoteLower = config.FtpRemoteDirectory.ToLowerInvariant();
        if (!remoteLower.Contains("public_html") && !remoteLower.Contains("www") &&
            !remoteLower.Contains("htdocs") && !remoteLower.Contains("site"))
        {
            throw new InvalidOperationException("Remote directory path seems unsafe to clean. Please double-check.");
        }

        using var client = new FluentFTP.FtpClient(config.FtpHost, new NetworkCredential(config.FtpUsername, config.FtpPassword));
        await client.ConnectAsync();
        LogInformation($"Connected to FTP server {config.FtpHost}");
        LogInformation($"Cleaning remote directory: {config.FtpRemoteDirectory}");

        var remoteItems = await client.GetListingAsync(config.FtpRemoteDirectory, FluentFTP.FtpListOption.Recursive);

        foreach (var item in remoteItems.OrderByDescending(i => i.FullName))
        {
            try
            {
                if (item.Type.ToString().ToLowerInvariant().Contains("file"))
                {
                    await client.DeleteFileAsync(item.FullName);
                    LogInformation($"Deleted file: {item.FullName}");
                }
                else if (item.Type.ToString().ToLowerInvariant().Contains("dir"))
                {
                    await client.DeleteDirectoryAsync(item.FullName);
                    LogInformation($"Deleted directory: {item.FullName}");
                }
            }
            catch (Exception ex)
            {
                LogError($"Failed to delete {item.FullName}: {ex.Message}");
            }
        }

        LogInformation("Remote directory cleaned.");
    });

Task("UploadToFTP")
    .IsDependentOn("CleanRemote")
    .Does(async () =>
    {
        if (string.IsNullOrEmpty(config.FtpPassword))
            throw new ArgumentException("FTP password must be provided in the config file.");

        using var client = new FtpClient(config.FtpHost, new NetworkCredential(config.FtpUsername, config.FtpPassword));
        await client.ConnectAsync();
        LogInformation($"Connected to FTP server {config.FtpHost}");

        var files = System.IO.Directory.GetFiles(config.StagingDirectory, "*", System.IO.SearchOption.AllDirectories);
        int totalFiles = files.Length;
        int filesUploaded = 0;
        LogInformation($"Total files to upload: {totalFiles}");

        foreach (var file in files)
        {
            var relativePath = file.Substring(config.StagingDirectory.Length).TrimStart(System.IO.Path.DirectorySeparatorChar, '/');
            var remotePath = config.FtpRemoteDirectory + "/" + relativePath.Replace("\\", "/");
            var remoteDirectory = System.IO.Path.GetDirectoryName(remotePath).Replace("\\", "/");

            await client.CreateDirectoryAsync(remoteDirectory);

            if (await client.FileExistsAsync(remotePath))
                await client.DeleteFileAsync(remotePath);

            try
            {
                var uploadResult = await client.UploadFileAsync(file, remotePath);
                if (uploadResult != FtpStatus.Success)
                    throw new Exception($"Upload returned non-success status for {file}");

                LogInformation($"Uploaded: {relativePath.Replace("\\", "/")}");
            }
            catch (Exception ex)
            {
                LogError($"Error uploading {file}: {ex.Message}");
                if (ex.InnerException != null)
                    LogError($"Inner Exception: {ex.InnerException.Message}");
            }

            filesUploaded++;
            UpdateProgressBar(totalFiles, filesUploaded);
        }

        LogInformation($"Upload complete. {filesUploaded}/{totalFiles} files uploaded.");
    });

Task("Cleanup")
    .IsDependentOn("UploadToFTP")
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
    int progressBarWidth = 50;
    int progress = (int)((double)current / total * progressBarWidth);
    string progressBar = "[" + new string('#', progress) + new string(' ', progressBarWidth - progress) + $"] {current}/{total}";
    ClearConsoleLine();
    Console.Write(progressBar);
    Console.SetCursorPosition(0, Console.CursorTop);
}

void ClearConsoleLine()
{
    Console.SetCursorPosition(0, Console.CursorTop);
    Console.Write(new string(' ', Console.WindowWidth));
    Console.SetCursorPosition(0, Console.CursorTop);
}

void LogInformation(string message)
{
    ClearConsoleLine();
    Console.WriteLine(message);
    UpdateProgressBar(1, 0);
}

void LogError(string message)
{
    ClearConsoleLine();
    Console.WriteLine(message);
    UpdateProgressBar(1, 0);
}
