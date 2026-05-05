namespace SentrySMP.Shared.DTOs
{
    public class RconExecutionResult
    {
        // True when every command had at least one successful send (no RCON/command error)
        public bool AllSucceeded { get; set; }

        // Mapping of command text to error message when it failed on all targets
        public System.Collections.Generic.List<RconCommandResult> CommandResults { get; set; } = new();
    }

    public class RconCommandResult
    {
        public string CommandText { get; set; } = string.Empty;
        // Human-readable product name shown in the UI status table
        public string? ProductName { get; set; }
        public bool Succeeded { get; set; }
        public string? ErrorMessage { get; set; }
        // Raw response returned by the RCON server (if any)
        public string? Response { get; set; }

        // Optional debug entries (one per server/attempt) with extra info like server name, exceptions, etc.
        public System.Collections.Generic.List<string> Debug { get; set; } = new();
    }
}
