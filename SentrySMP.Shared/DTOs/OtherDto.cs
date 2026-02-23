using SentrySMP.Shared.DTOs;
namespace SentrySMP.Shared.DTOs
{
    public class OtherResponse : ProductResponse
    {
        public List<CommandDto>? Commands { get; set; }

        public OtherResponse()
        {
            Type = "Other";
        }
    }

    public class CreateOtherDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int? GlobalMaxOrder { get; set; }
        public int ServerId { get; set; }
        public List<CreateCommandDto>? Commands { get; set; }
    }

    public class UpdateOtherDto
    {
        public string Name { get; set; } = string.Empty;
        public string? Description { get; set; }
        public double Price { get; set; }
        public int Sale { get; set; }
        public string? Image { get; set; }
        public int? GlobalMaxOrder { get; set; }
        public int ServerId { get; set; }
        public List<CreateCommandDto>? Commands { get; set; }
    }
}
