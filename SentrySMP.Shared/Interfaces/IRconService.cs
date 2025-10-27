using System.Collections.Generic;
using System.Threading.Tasks;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface IRconService
    {
        /// <summary>
        /// Execute RCON commands for the supplied products and quantities. The product's Server property
        /// will be used to target a specific server; if null the service may target all servers.
        /// Quantity controls how many times the product's commands will be executed.
        /// Returns a result containing per-command success/failure info.
        /// </summary>
        Task<RconExecutionResult> ExecuteCommandsForProductsAsync(List<DTOs.ProductQuantityDto> productsWithQuantity, string? username);
    }
}
