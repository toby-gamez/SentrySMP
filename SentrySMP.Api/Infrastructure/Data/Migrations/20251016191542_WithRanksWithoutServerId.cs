using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace SentrySMP.Api.Infrastructure.Data.Migrations
{
    /// <inheritdoc />
    public partial class WithRanksWithoutServerId : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(
                name: "FK_Ranks_Servers_ServerId",
                table: "Ranks");

            migrationBuilder.DropIndex(
                name: "IX_Ranks_ServerId",
                table: "Ranks");

            migrationBuilder.DropColumn(
                name: "ServerId",
                table: "Ranks");
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<int>(
                name: "ServerId",
                table: "Ranks",
                type: "int",
                nullable: false,
                defaultValue: 0);

            migrationBuilder.CreateIndex(
                name: "IX_Ranks_ServerId",
                table: "Ranks",
                column: "ServerId");

            migrationBuilder.AddForeignKey(
                name: "FK_Ranks_Servers_ServerId",
                table: "Ranks",
                column: "ServerId",
                principalTable: "Servers",
                principalColumn: "Id",
                onDelete: ReferentialAction.Cascade);
        }
    }
}
