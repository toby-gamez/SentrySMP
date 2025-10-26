using SentrySMP.App.Components.State;
using System.IO;
using DotNetEnv;
using SentrySMP.Shared.Interfaces;
using SentrySMP.Api.Services;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Components.Authorization;
using Microsoft.EntityFrameworkCore;
using Microsoft.OpenApi.Models;
using Refit;
using Serilog;
using SentrySMP.Api.Authentication;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.App.Authentication;
using SentrySMP.App.Components;
using SentrySMP.App.Handlers;


var builder = WebApplication.CreateBuilder(args);

// Load .env if present (development and simple production setups)
try
{
    DotNetEnv.Env.Load(Path.Combine(builder.Environment.ContentRootPath, ".env"));
}
catch { }

Log.Logger = new LoggerConfiguration()
    .ReadFrom.Configuration(builder.Configuration)
    .Enrich.FromLogContext()
    .CreateLogger();

builder.Services.AddControllers();
builder.Host.UseSerilog();


var services = builder.Services;

// Add services
services.Configure<BasicAuthOptions>(builder.Configuration.GetSection("Auth:Basic"));
services.AddScoped(sp => new HttpClient { BaseAddress = new Uri(builder.Configuration["Api:BaseAddress"] ?? "https://localhost:7270") });
services.AddHttpContextAccessor();

services.AddDbContext<SentryDbContext>(options =>
{
    options.UseMySql(builder.Configuration.GetConnectionString("DefaultConnection"), 
        ServerVersion.AutoDetect(builder.Configuration.GetConnectionString("DefaultConnection")),
        b => b.MigrationsAssembly("SentrySMP.Api"));
});

// Add Authentication and Authorization
services.AddAuthentication(SentrySMP.App.Authentication.BasicAuthConstants.Scheme).AddScheme<AuthenticationSchemeOptions, BasicAuthHandler>(
        SentrySMP.App.Authentication.BasicAuthConstants.Scheme, null);

services.AddAuthorization();

services.AddRazorComponents().AddInteractiveServerComponents();

// Register our services
services.AddScoped<IServerService, ServerService>();
services.AddScoped<IShardService, ShardService>();
services.AddScoped<IKeyService, KeyService>();
services.AddScoped<IBundleService, BundleService>();
services.AddScoped<IRankService, RankService>();
services.AddScoped<IShardService, ShardService>();
// Team service (file-backed)
services.AddScoped<ITeamService, TeamService>();
services.AddScoped<IBattlePassService, BattlePassService>();
services.AddScoped<ICommandService, CommandService>();
services.AddScoped<IStatusService, StatusService>();
services.AddScoped<IAnnouncementsService, AnnouncementsService>();
services.AddScoped<ITransactionsService, TransactionsService>();
// IRconService is implemented in the API project (server-side service)
services.AddScoped<SentrySMP.Shared.Interfaces.IRconService, SentrySMP.Api.Services.RconService>();
services.AddScoped<CartState>(sp => new CartState(sp.GetRequiredService<Microsoft.JSInterop.IJSRuntime>()));
services.AddControllers()
    .AddJsonOptions(options =>
    {
        options.JsonSerializerOptions.Converters.Add(new System.Text.Json.Serialization.JsonStringEnumConverter());
    });
services.AddEndpointsApiExplorer();

services.AddSingleton<CredentialStore>();
services.AddTransient<HttpLoggingHandler>();
services.AddScoped<UserService>();
// App status service to fetch Discord and Minecraft counts
services.AddScoped<SentrySMP.App.Services.StatusService>();

services.AddRefitClient<ISentryApi>()
    .ConfigureHttpClient(c =>
    {
        var baseAddress = builder.Configuration["Api:BaseAddress"];
        if (string.IsNullOrEmpty(baseAddress))
        {
            throw new InvalidOperationException("API base address is not configured.");
        }
        c.BaseAddress = new Uri(baseAddress);
    })
    .ConfigurePrimaryHttpMessageHandler(() =>
    {
        var handler = new HttpClientHandler();
        if (builder.Environment.IsDevelopment())
        {
            // Ignore SSL certificate errors in development
            handler.ServerCertificateCustomValidationCallback = (message, cert, chain, errors) => true;
        }
        return handler;
    })
    .AddHttpMessageHandler<HttpLoggingHandler>();

// Refit client for checkout API (PayPal create-order)
services.AddRefitClient<SentrySMP.Shared.Interfaces.ICheckoutApi>()
    .ConfigureHttpClient(c =>
    {
        var baseAddress = builder.Configuration["Api:BaseAddress"];
        if (string.IsNullOrEmpty(baseAddress))
        {
            throw new InvalidOperationException("API base address is not configured.");
        }
        c.BaseAddress = new Uri(baseAddress);
    })
    .ConfigurePrimaryHttpMessageHandler(() =>
    {
        var handler = new HttpClientHandler();
        if (builder.Environment.IsDevelopment())
        {
            handler.ServerCertificateCustomValidationCallback = (message, cert, chain, errors) => true;
        }
        return handler;
    })
    .AddHttpMessageHandler<HttpLoggingHandler>();

services.AddSwaggerGen(options =>
{
    options.SwaggerDoc("v1", new OpenApiInfo
    {
        Title = "Sentry API",
        Version = "v1"
    });

    options.AddSecurityDefinition("basic", new OpenApiSecurityScheme
    {
        Name = "Authorization",
        Type = SecuritySchemeType.Http,
        Scheme = "basic",
        In = ParameterLocation.Header,
        Description = "Enter your username and password for Basic Authentication"
    });

    options.AddSecurityRequirement(new OpenApiSecurityRequirement
    {
        {
            new OpenApiSecurityScheme
            {
                Reference = new OpenApiReference
                {
                    Type = ReferenceType.SecurityScheme,
                    Id = "basic"
                }
            },
            Array.Empty<string>()
        }
    });
});

var app = builder.Build();

//if (!app.Environment.IsDevelopment())
//{
  //  app.UseExceptionHandler("/Error", createScopeForErrors: true);
    //app.UseHsts();
//}

app.UseSwagger();
app.UseSwaggerUI();

app.UseHttpsRedirection();

app.UseStaticFiles();
app.UseRouting();
// Add Authentication and Authorization middleware
app.UseAuthentication();
app.UseAuthorization();

app.UseAntiforgery();
app.MapControllers();

app.MapRazorComponents<App>().AddInteractiveServerRenderMode();

app.Run();