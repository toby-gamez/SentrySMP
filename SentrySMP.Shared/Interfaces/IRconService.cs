using System.Collections.Generic;
using System.Threading.Tasks;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface IRconService
    {
        /// <summary>
        /// Execute delivery commands for the supplied products and quantities via the command delivery API.
        /// voucherCode: optional voucher applied to the order.
        /// paidAmount: the final amount paid (after discount); if null the service computes it from products.
        /// Returns a result containing per-command success/failure info.
        /// </summary>
        Task<RconExecutionResult> ExecuteCommandsForProductsAsync(List<DTOs.ProductQuantityDto> productsWithQuantity, string? username, string? voucherCode = null, double? paidAmount = null);
    }
}
