using Microsoft.AspNetCore.Authentication;
using Microsoft.Extensions.FileProviders;
using Microsoft.OpenApi.Models;
using SentrySMP.Images.Authentication;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen(c =>
{
    c.AddSecurityDefinition("basic", new OpenApiSecurityScheme
    {
        Type = SecuritySchemeType.Http,
        Scheme = "basic",
        Description = "Basic authentication for protected endpoints"
    });

    c.AddSecurityRequirement(new OpenApiSecurityRequirement
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
            new string[] {}
        }
    });
});

builder.Services.AddAuthentication(BasicAuthConstants.Scheme)
    .AddScheme<AuthenticationSchemeOptions, BasicAuthHandler>(BasicAuthConstants.Scheme, null);

builder.Services.AddAuthorization();

builder.Services.AddCors(options =>
{
    options.AddPolicy("LocalDev", b => b
        .WithOrigins("http://localhost:5000", "https://localhost:5001", "http://localhost:7270", "https://localhost:7270")
        .AllowAnyMethod()
        .AllowAnyHeader()
        .AllowCredentials());
});

var app = builder.Build();

// Ensure folders exist
var uploadsRoot = Path.Combine(app.Environment.ContentRootPath, "wwwroot", "uploads");
Directory.CreateDirectory(uploadsRoot);

// Serve static files from wwwroot (uploads available at /uploads/...)
app.UseStaticFiles(new StaticFileOptions
{
    FileProvider = new PhysicalFileProvider(Path.Combine(app.Environment.ContentRootPath, "wwwroot")),
    RequestPath = ""
});

app.UseCors("LocalDev");
app.UseAuthentication();
app.UseAuthorization();

app.MapGet("/", () => Results.Redirect("/swagger"));
app.UseSwagger();
app.UseSwaggerUI(c =>
{
    c.SwaggerEndpoint("/swagger/v1/swagger.json", "SentrySMP.Images v1");
    c.RoutePrefix = "swagger";
});

app.MapControllers();

app.Run();
