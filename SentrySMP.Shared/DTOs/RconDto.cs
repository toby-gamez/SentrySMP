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
        public bool Succeeded { get; set; }
        public string? ErrorMessage { get; set; }
    }
}
