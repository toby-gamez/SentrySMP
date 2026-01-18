using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace SentrySMP.Api.Infrastructure.Data.Migrations
{
    /// <inheritdoc />
    public partial class AddGlobalMaxOrder : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<int>(
                name: "GlobalMaxOrder",
                table: "Ranks",
                type: "int",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "GlobalMaxOrder",
                table: "Keys",
                type: "int",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "GlobalMaxOrder",
                table: "Coins",
                type: "int",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "GlobalMaxOrder",
                table: "Bundles",
                type: "int",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "GlobalMaxOrder",
                table: "BattlePasses",
                type: "int",
                nullable: true);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "GlobalMaxOrder",
                table: "Ranks");

            migrationBuilder.DropColumn(
                name: "GlobalMaxOrder",
                table: "Keys");

            migrationBuilder.DropColumn(
                name: "GlobalMaxOrder",
                table: "Coins");

            migrationBuilder.DropColumn(
                name: "GlobalMaxOrder",
                table: "Bundles");

            migrationBuilder.DropColumn(
                name: "GlobalMaxOrder",
                table: "BattlePasses");
        }
    }
}
