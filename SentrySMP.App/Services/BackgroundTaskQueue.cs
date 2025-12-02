using System;
using System.Threading;
using System.Threading.Channels;
using System.Threading.Tasks;

namespace SentrySMP.App.Services
{
    public interface IBackgroundTaskQueue
    {
        void QueueBackgroundWorkItem(Func<CancellationToken, Task> workItem);
    }

    public class BackgroundTaskQueue : IBackgroundTaskQueue, IDisposable
    {
        private readonly Channel<Func<CancellationToken, Task>> _queue;

        public BackgroundTaskQueue(int capacity = 100)
        {
            var options = new BoundedChannelOptions(capacity)
            {
                SingleReader = true,
                SingleWriter = false
            };
            _queue = Channel.CreateBounded<Func<CancellationToken, Task>>(options);
        }

        public void QueueBackgroundWorkItem(Func<CancellationToken, Task> workItem)
        {
            if (workItem == null) throw new ArgumentNullException(nameof(workItem));
            // Fire-and-forget safe enqueue; if channel is full this will throw
            var written = _queue.Writer.TryWrite(workItem);
            if (!written)
            {
                // fallback: try write asynchronously (should rarely be needed)
                _queue.Writer.WriteAsync(workItem).AsTask().GetAwaiter().GetResult();
            }
        }

        public async Task<Func<CancellationToken, Task>?> DequeueAsync(CancellationToken cancellationToken)
        {
            try
            {
                var item = await _queue.Reader.ReadAsync(cancellationToken);
                return item;
            }
            catch
            {
                return null;
            }
        }

        public void Dispose()
        {
            try { _queue.Writer.TryComplete(); } catch { }
        }
    }
}
