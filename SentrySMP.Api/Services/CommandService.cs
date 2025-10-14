using SentrySMP.Domain.Entities;
using SentrySMP.Shared.Interfaces;
using SentrySMP.Api.Infrastructure.Data;
using Microsoft.EntityFrameworkCore;

namespace SentrySMP.Api.Services
{
    public class CommandService : ICommandService
    {
        private readonly SentryDbContext _context;

        public CommandService(SentryDbContext context)
        {
            _context = context;
        }

        public async Task<List<Command>> GetAllAsync()
        {
            return await _context.Commands.ToListAsync();
        }

        public async Task<Command?> GetByIdAsync(int id)
        {
            return await _context.Commands.FindAsync(id);
        }

        public async Task<Command> CreateAsync(Command command)
        {
            _context.Commands.Add(command);
            await _context.SaveChangesAsync();
            return command;
        }

        public async Task<bool> DeleteAsync(int id)
        {
            var command = await _context.Commands.FindAsync(id);
            if (command == null) return false;
            _context.Commands.Remove(command);
            await _context.SaveChangesAsync();
            return true;
        }

        public async Task<Command?> UpdateAsync(Command command)
        {
            var existing = await _context.Commands.FindAsync(command.Id);
            if (existing == null) return null;
            existing.CommandText = command.CommandText;
            existing.Type = command.Type;
            existing.TypeId = command.TypeId;
            await _context.SaveChangesAsync();
            return existing;
        }
    }
}