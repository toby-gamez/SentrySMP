using System;
using System.Threading;
using System.Threading.Tasks;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;

namespace SentrySMP.App.Services
{
    public class BackgroundTaskQueueHostedService : BackgroundService
    {
        private readonly BackgroundTaskQueue _queue;
        private readonly ILogger<BackgroundTaskQueueHostedService> _logger;

        public BackgroundTaskQueueHostedService(BackgroundTaskQueue queue, ILogger<BackgroundTaskQueueHostedService> logger)
        {
            _queue = queue ?? throw new ArgumentNullException(nameof(queue));
            _logger = logger;
        }

        protected override async Task ExecuteAsync(CancellationToken stoppingToken)
        {
            _logger.LogInformation("BackgroundTaskQueueHostedService starting");
            while (!stoppingToken.IsCancellationRequested)
            {
                try
                {
                    var work = await _queue.DequeueAsync(stoppingToken);
                    if (work == null) continue;
                    try
                    {
                        await work(stoppingToken);
                    }
                    catch (Exception ex)
                    {
                        _logger.LogError(ex, "Error executing background work item");
                    }
                }
                catch (OperationCanceledException) when (stoppingToken.IsCancellationRequested)
                {
                    // shutting down
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "BackgroundTaskQueueHostedService loop error");
                    await Task.Delay(1000, stoppingToken);
                }
            }
            _logger.LogInformation("BackgroundTaskQueueHostedService stopping");
        }
    }
}
