using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.Interfaces;
using SentrySMP.Domain.Entities;

namespace SentrySMP.App.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class CommandsController : ControllerBase
    {
        private readonly ICommandService _service;

        public CommandsController(ICommandService service)
        {
            _service = service;
        }

        [HttpGet]
        [AllowAnonymous]
        public async Task<IActionResult> GetAll()
        {
            var commands = await _service.GetAllAsync();
            return Ok(commands);
        }

        [HttpGet("{id}")]
        [AllowAnonymous]
        public async Task<IActionResult> GetById(int id)
        {
            var command = await _service.GetByIdAsync(id);
            if (command == null) return NotFound();
            return Ok(command);
        }

        [HttpPost]
        public async Task<IActionResult> Create([FromBody] Command command)
        {
            var created = await _service.CreateAsync(command);
            return CreatedAtAction(nameof(GetById), new { id = created.Id }, created);
        }

        [HttpPut("{id}")]
        public async Task<IActionResult> Update(int id, [FromBody] Command command)
        {
            if (id != command.Id) return BadRequest();
            var updated = await _service.UpdateAsync(command);
            if (updated == null) return NotFound();
            return Ok(updated);
        }

        [HttpDelete("{id}")]
        public async Task<IActionResult> Delete(int id)
        {
            var deleted = await _service.DeleteAsync(id);
            if (!deleted) return NotFound();
            return NoContent();
        }
    }
}