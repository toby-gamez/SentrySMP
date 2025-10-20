namespace SentrySMP.Shared.DTOs
{
    public class AnnouncementDto
    {
        public int Id { get; set; }
        public string? Title { get; set; }
        public string? Author { get; set; }
        public string? Content { get; set; }
        public DateTime CreatedAt { get; set; }
    }
}
