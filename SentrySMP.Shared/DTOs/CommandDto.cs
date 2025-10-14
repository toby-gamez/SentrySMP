namespace SentrySMP.Shared.DTOs
{
    public class CommandDto
    {
        public int Id { get; set; }
        public string CommandText { get; set; } = string.Empty;
        public string Type { get; set; } = string.Empty;
        public int TypeId { get; set; }
    }
}