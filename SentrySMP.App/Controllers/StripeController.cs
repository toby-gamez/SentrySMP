using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class StripeController : ControllerBase
    {
        private readonly IHttpClientFactory _httpFactory;
        private readonly IConfiguration _config;

        public StripeController(IHttpClientFactory httpFactory, IConfiguration config)
        {
            _httpFactory = httpFactory;
            _config = config;
        }

        [HttpPost("create-session")]
        public async Task<IActionResult> CreateSession([FromBody] CreateStripeSessionRequest request)
        {
            var secret = _config["STRIPE_SECRET_KEY"] ?? Environment.GetEnvironmentVariable("STRIPE_SECRET_KEY");
            if (string.IsNullOrWhiteSpace(secret))
            {
                return BadRequest("Stripe secret (STRIPE_SECRET_KEY) is not configured on the server.");
            }

            // Normalize decimal separator before parsing (handle both comma and dot)
            var normalizedAmount = request.Amount?.Replace(",", ".");
            if (string.IsNullOrWhiteSpace(normalizedAmount) || !decimal.TryParse(normalizedAmount, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var amountDecimal) || amountDecimal <= 0)
            {
                return BadRequest("Invalid amount");
            }

            var amountCents = (long)Math.Round(amountDecimal * 100);

            var http = _httpFactory.CreateClient();
            http.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", secret);

            // Build form content for Stripe Checkout Session
            var baseUrl = $"{Request.Scheme}://{Request.Host}";
            // Point success_url to server return endpoint so server can persist transaction before redirecting to frontend
            var successUrl = baseUrl + "/api/stripe/return?session_id={CHECKOUT_SESSION_ID}";
            var cancelUrl = baseUrl + "/checkout?status=cancelled";

            // Let Stripe dynamically select payment methods based on your dashboard settings
            // This avoids 400 errors from unsupported payment method types
            var form = new List<KeyValuePair<string, string>>
            {
                new KeyValuePair<string,string>("mode","payment"),
                new KeyValuePair<string,string>("success_url", successUrl),
                new KeyValuePair<string,string>("cancel_url", cancelUrl),
                // line_items[0][price_data][currency]=eur
                new KeyValuePair<string,string>("line_items[0][price_data][currency]","eur"),
                new KeyValuePair<string,string>("line_items[0][price_data][product_data][name]", string.IsNullOrWhiteSpace(request.Description) ? "SentrySMP Purchase" : request.Description),
                new KeyValuePair<string,string>("line_items[0][price_data][unit_amount]", amountCents.ToString()),
                new KeyValuePair<string,string>("line_items[0][quantity]","1")
            };

            var content = new FormUrlEncodedContent(form);

            var resp = await http.PostAsync("https://api.stripe.com/v1/checkout/sessions", content);
            var txt = await resp.Content.ReadAsStringAsync();
            if (!resp.IsSuccessStatusCode)
            {
                return StatusCode((int)resp.StatusCode, txt);
            }

            using var doc = JsonDocument.Parse(txt);
            var root = doc.RootElement;
            var url = root.GetProperty("url").GetString() ?? string.Empty;
            var id = root.GetProperty("id").GetString() ?? string.Empty;

            return Ok(new CreateStripeSessionResponse { Url = url, SessionId = id });
        }

        // This endpoint is called by Stripe via success redirect. Server will fetch session details,
        // persist a PaymentTransaction and then redirect to frontend checkout success page.
        [HttpGet("return")]
        public async Task<IActionResult> Return([FromQuery(Name = "session_id")] string? sessionId)
        {
            if (string.IsNullOrWhiteSpace(sessionId)) return Redirect("/checkout?status=error");

            var secret = _config["STRIPE_SECRET_KEY"] ?? Environment.GetEnvironmentVariable("STRIPE_SECRET_KEY");
            if (string.IsNullOrWhiteSpace(secret)) return Redirect("/checkout?status=error");

            var http = _httpFactory.CreateClient();
            http.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", secret);

            // Get the session details from Stripe
            var resp = await http.GetAsync($"https://api.stripe.com/v1/checkout/sessions/{Uri.EscapeDataString(sessionId)}?expand[]=payment_intent");
            var txt = await resp.Content.ReadAsStringAsync();
            if (!resp.IsSuccessStatusCode)
            {
                return Redirect("/checkout?status=error");
            }

            try
            {
                using var doc = JsonDocument.Parse(txt);
                var root = doc.RootElement;
                decimal amount = 0;
                string currency = "EUR";
                string username = string.Empty;
                string itemsJson = string.Empty;

                if (root.TryGetProperty("amount_total", out var amt) && amt.ValueKind != JsonValueKind.Null)
                {
                    // amount in cents
                    long cents = amt.GetInt64();
                    amount = cents / 100m;
                }
                if (root.TryGetProperty("currency", out var cur)) currency = cur.GetString() ?? currency;

                // Try to read metadata if set
                if (root.TryGetProperty("metadata", out var md))
                {
                    if (md.TryGetProperty("username", out var u)) username = u.GetString() ?? string.Empty;
                    if (md.TryGetProperty("items", out var it)) itemsJson = it.GetRawText();
                }

                var tx = new SentrySMP.Domain.Entities.PaymentTransaction
                {
                    Provider = "Stripe",
                    ProviderTransactionId = sessionId,
                    Amount = amount,
                    Currency = currency.ToUpperInvariant(),
                    MinecraftUsername = username,
                    ItemsJson = itemsJson,
                    Status = "succeeded",
                    RawResponse = txt,
                    CreatedAt = DateTime.UtcNow
                };

                // Save to DB if DbContext is available
                try
                {
                    // resolve db from request services to avoid taking dependency in constructor
                    var db = HttpContext.RequestServices.GetService(typeof(SentrySMP.Api.Infrastructure.Data.SentryDbContext)) as SentrySMP.Api.Infrastructure.Data.SentryDbContext;
                    if (db != null)
                    {
                        db.PaymentTransactions.Add(tx);
                        await db.SaveChangesAsync();
                    }
                }
                catch { }
            }
            catch { }

            // Redirect to frontend success page (Blazor will clear cart)
            return Redirect($"/checkout?status=success&session_id={Uri.EscapeDataString(sessionId)}");
        }
    }
}
