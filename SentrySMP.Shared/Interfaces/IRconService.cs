using System.Collections.Generic;
using System.Threading.Tasks;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface IRconService
    {
        /// <summary>
        /// Execute RCON commands for the supplied products. The product's Server property
        /// will be used to target a specific server; if null the service may target all servers.
        /// </summary>
        Task ExecuteCommandsForProductsAsync(List<ProductResponse> products, string? username);
    }
}
