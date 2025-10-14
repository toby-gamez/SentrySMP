using System.Collections.Generic;
using System.Threading.Tasks;
using SentrySMP.Domain.Entities;

namespace SentrySMP.Shared.Interfaces
{
    public interface ICommandService
    {
        Task<List<Command>> GetAllAsync();
        Task<Command?> GetByIdAsync(int id);
        Task<Command> CreateAsync(Command command);
        Task<bool> DeleteAsync(int id);
        Task<Command?> UpdateAsync(Command command);
    }
}