using Microsoft.EntityFrameworkCore;
using SentrySMP.Domain.Entities;

namespace SentrySMP.Api.Infrastructure.Data;

public class SentryDbContext : DbContext
{
    public SentryDbContext(DbContextOptions<SentryDbContext> options) : base(options)
    {
    }

    public DbSet<Server> Servers { get; set; }
    public DbSet<Key> Keys { get; set; }
    public DbSet<Rank> Ranks { get; set; }
    public DbSet<Bundle> Bundles { get; set; }
    public DbSet<Coin> Coins { get; set; }
    public DbSet<Domain.Entities.TeamCategory> TeamCategories { get; set; }
    public DbSet<Domain.Entities.TeamMember> TeamMembers { get; set; }
    public DbSet<BattlePass> BattlePasses { get; set; }
    public DbSet<Command> Commands { get; set; }
    public DbSet<SentrySMP.Domain.Entities.PaymentTransaction> PaymentTransactions { get; set; }

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        // Command entity config
        modelBuilder.Entity<Command>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.CommandText).IsRequired();
            entity.Property(e => e.Type).IsRequired();
            entity.Property(e => e.TypeId).IsRequired();
        });

        // Coin entity config (if needed)
        modelBuilder.Entity<Coin>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Name).IsRequired().HasMaxLength(100);
            entity.Property(e => e.Description).HasMaxLength(500);
            entity.HasOne(e => e.Server)
                  .WithMany(s => s.Coins)
                  .HasForeignKey(e => e.ServerId)
                  .OnDelete(DeleteBehavior.Cascade);
        });

        // Configure Server entity
        modelBuilder.Entity<Server>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Id)
                .ValueGeneratedOnAdd();
            entity.Property(e => e.Name)
                .IsRequired()
                .HasMaxLength(100);
            entity.Property(e => e.RCONIP)
                .IsRequired()
                .HasMaxLength(50);
            entity.Property(e => e.RCONPort)
                .IsRequired();
            entity.Property(e => e.RCONPassword)
                .IsRequired()
                .HasMaxLength(100);
        });

        // Configure Key entity
        modelBuilder.Entity<Key>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Id)
                .ValueGeneratedOnAdd();
                
            entity.Property(e => e.Name)
                .IsRequired()
                .HasMaxLength(100);
                
            entity.Property(e => e.Description)
                .HasMaxLength(500);
                
            entity.Property(e => e.Price)
                .IsRequired()
                .HasColumnType("float");
                
            entity.Property(e => e.Sale)
                .HasColumnType("float")
                .HasDefaultValue(0);
                
            entity.Property(e => e.Image)
                .HasMaxLength(255);

            // Configure relationship
            entity.HasOne(e => e.Server)
                .WithMany(s => s.Keys)
                .HasForeignKey(e => e.ServerId)
                .OnDelete(DeleteBehavior.Cascade);
        });

        // Configure Rank entity (similar to Key)
        modelBuilder.Entity<Rank>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Id)
                .ValueGeneratedOnAdd();

            entity.Property(e => e.Name)
                .IsRequired()
                .HasMaxLength(100);

            entity.Property(e => e.Description)
                .HasMaxLength(500);

            entity.Property(e => e.Price)
                .IsRequired()
                .HasColumnType("float");

            entity.Property(e => e.Sale)
                .HasColumnType("float")
                .HasDefaultValue(0);

            entity.Property(e => e.Image)
                .HasMaxLength(255);
        });

        // Configure Bundle entity (similar to Key)
        modelBuilder.Entity<Bundle>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Id)
                .ValueGeneratedOnAdd();

            entity.Property(e => e.Name)
                .IsRequired()
                .HasMaxLength(100);

            entity.Property(e => e.Description)
                .HasMaxLength(500);

            entity.Property(e => e.Price)
                .IsRequired()
                .HasColumnType("float");

            entity.Property(e => e.Sale)
                .HasColumnType("float")
                .HasDefaultValue(0);

            entity.Property(e => e.Image)
                .HasMaxLength(255);

            entity.HasOne(e => e.Server)
                .WithMany(s => s.Bundles)
                .HasForeignKey(e => e.ServerId)
                .OnDelete(DeleteBehavior.Cascade);
        });

        // Configure BattlePass entity (similar to Key/Bundle)
        modelBuilder.Entity<BattlePass>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Id)
                .ValueGeneratedOnAdd();

            entity.Property(e => e.Name)
                .IsRequired()
                .HasMaxLength(100);

            entity.Property(e => e.Description)
                .HasMaxLength(500);

            entity.Property(e => e.Price)
                .IsRequired()
                .HasColumnType("float");

            entity.Property(e => e.Sale)
                .HasColumnType("float")
                .HasDefaultValue(0);

            entity.Property(e => e.Image)
                .HasMaxLength(255);

            entity.HasOne(e => e.Server)
                .WithMany(s => s.BattlePasses)
                .HasForeignKey(e => e.ServerId)
                .OnDelete(DeleteBehavior.Cascade);
        });

        // Configure PaymentTransaction
        modelBuilder.Entity<SentrySMP.Domain.Entities.PaymentTransaction>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Provider).IsRequired().HasMaxLength(50);
            entity.Property(e => e.ProviderTransactionId).HasMaxLength(200);
            entity.Property(e => e.Amount).HasColumnType("decimal(18,2)");
            entity.Property(e => e.Currency).HasMaxLength(10).HasDefaultValue("EUR");
            entity.Property(e => e.MinecraftUsername).HasMaxLength(100);
            entity.Property(e => e.ItemsJson).HasColumnType("text");
            entity.Property(e => e.Status).HasMaxLength(50);
            entity.Property(e => e.CreatedAt).IsRequired();
            entity.Property(e => e.RawResponse).HasColumnType("text");
        });

        // Team category / member entities
        modelBuilder.Entity<Domain.Entities.TeamCategory>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Id).HasMaxLength(36);
            entity.Property(e => e.Name).IsRequired().HasMaxLength(100);
            entity.Property(e => e.SortOrder).IsRequired();
            entity.HasMany(e => e.Members)
                  .WithOne(m => m.Category)
                  .HasForeignKey(m => m.TeamCategoryId)
                  .OnDelete(DeleteBehavior.Cascade);
        });

        modelBuilder.Entity<Domain.Entities.TeamMember>(entity =>
        {
            entity.HasKey(e => e.Id);
            entity.Property(e => e.Id).HasMaxLength(36);
            entity.Property(e => e.MinecraftName).IsRequired().HasMaxLength(100);
            entity.Property(e => e.Role).HasMaxLength(100);
            entity.Property(e => e.SkinUrl).HasMaxLength(500);
            entity.Property(e => e.TeamCategoryId).HasMaxLength(36);
            entity.Property(e => e.SortOrder).IsRequired();
        });
    }
}