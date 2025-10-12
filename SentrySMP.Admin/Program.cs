using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Components.Authorization;
using Microsoft.EntityFrameworkCore;
using Microsoft.OpenApi.Models;
using Refit;
using SentrySMP.Api.Authentication;
using SentrySMP.Api.Infrastructure.Data;
using SentrySMP.Api.Services;
using SentrySMP.Admin.Authentication;
using SentrySMP.Admin.Components;
using SentrySMP.Admin.Handlers;
using SentrySMP.Shared.Interfaces;
using Serilog;

var builder = WebApplication.CreateBuilder(args);

Log.Logger = new LoggerConfiguration()
    .ReadFrom.Configuration(builder.Configuration)
    .Enrich.FromLogContext()
    .CreateLogger();

builder.Host.UseSerilog();

var services = builder.Services;

// Add services
services.Configure<BasicAuthOptions>(builder.Configuration.GetSection("Auth:Basic"));
services.AddScoped(sp =>
{
    var httpClientHandler = new HttpClientHandler();
    
    // In development, bypass SSL certificate validation
    if (builder.Environment.IsDevelopment())
    {
        httpClientHandler.ServerCertificateCustomValidationCallback = (message, cert, chain, errors) => true;
    }
    
    return new HttpClient(httpClientHandler)
    {
        BaseAddress = new Uri(builder.Configuration["Api:BaseAddress"] ?? "https://localhost:7270"),
    };
});
services.AddHttpContextAccessor();

// Admin uses only Refit API client - connects to App API endpoints
services
    .AddAuthentication(BasicAuthConstants.Scheme)
    .AddScheme<AuthenticationSchemeOptions, BasicAuthHandler>(BasicAuthConstants.Scheme, null);

services.AddAuthorization();

// Add Blazor authentication
services.AddCascadingAuthenticationState();
services.AddScoped<AuthenticationStateProvider, BasicAuthenticationStateProvider>();

services.AddRazorComponents().AddInteractiveServerComponents();

// Admin only needs controllers for admin-specific endpoints if any
services.AddControllers();

services.AddSingleton<CredentialStore>();
services.AddTransient<AuthenticationHeaderHandler>();
services.AddTransient<HttpLoggingHandler>();

services
    .AddRefitClient<ISentryApi>()
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
        
        // In development, bypass SSL certificate validation
        if (builder.Environment.IsDevelopment())
        {
            handler.ServerCertificateCustomValidationCallback = (message, cert, chain, errors) => true;
        }
        
        return handler;
    })
    .AddHttpMessageHandler<AuthenticationHeaderHandler>()
    .AddHttpMessageHandler<HttpLoggingHandler>();

services.AddSwaggerGen(options =>
{
    options.SwaggerDoc("v1", new OpenApiInfo { Title = "Sentry API", Version = "v1" });

    options.AddSecurityDefinition(
        "basic",
        new OpenApiSecurityScheme
        {
            Name = "Authorization",
            Type = SecuritySchemeType.Http,
            Scheme = "basic",
            In = ParameterLocation.Header,
            Description = "Enter your username and password for Basic Authentication",
        }
    );

    options.AddSecurityRequirement(
        new OpenApiSecurityRequirement
        {
            {
                new OpenApiSecurityScheme
                {
                    Reference = new OpenApiReference
                    {
                        Type = ReferenceType.SecurityScheme,
                        Id = "basic",
                    },
                },
                Array.Empty<string>()
            },
        }
    );
});

var app = builder.Build();

if (!app.Environment.IsDevelopment())
{
    app.UseExceptionHandler("/Error", createScopeForErrors: true);
    app.UseHsts();
}
else { }
app.UseSwagger();
app.UseSwaggerUI();

app.UseHttpsRedirection();

app.UseStaticFiles();
app.UseRouting();
app.UseAntiforgery();

app.UseAuthentication();
app.UseAuthorization();
app.MapControllers();

app.MapRazorComponents<App>()
    .AddInteractiveServerRenderMode();

app.Run();
