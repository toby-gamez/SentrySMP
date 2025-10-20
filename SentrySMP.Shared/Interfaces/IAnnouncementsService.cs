using SentrySMP.Shared.DTOs;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace SentrySMP.Shared.Interfaces
{
    public interface IAnnouncementsService
    {
        Task<IEnumerable<AnnouncementDto>> GetLatestAnnouncementsAsync();
    }
}
