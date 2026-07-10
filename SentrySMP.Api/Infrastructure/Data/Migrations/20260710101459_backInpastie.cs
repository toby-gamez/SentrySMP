using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace SentrySMP.Api.Infrastructure.Data.Migrations
{
    /// <inheritdoc />
    public partial class backInpastie : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(
                name: "FK_Gems_Servers_ServerId",
                table: "Gems");

            migrationBuilder.DropPrimaryKey(
                name: "PK_Gems",
                table: "Gems");

            migrationBuilder.RenameTable(
                name: "Gems",
                newName: "Coins");

            migrationBuilder.RenameIndex(
                name: "IX_Gems_ServerId",
                table: "Coins",
                newName: "IX_Coins_ServerId");

            migrationBuilder.AddPrimaryKey(
                name: "PK_Coins",
                table: "Coins",
                column: "Id");

            migrationBuilder.AddForeignKey(
                name: "FK_Coins_Servers_ServerId",
                table: "Coins",
                column: "ServerId",
                principalTable: "Servers",
                principalColumn: "Id",
                onDelete: ReferentialAction.Cascade);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(
                name: "FK_Coins_Servers_ServerId",
                table: "Coins");

            migrationBuilder.DropPrimaryKey(
                name: "PK_Coins",
                table: "Coins");

            migrationBuilder.RenameTable(
                name: "Coins",
                newName: "Gems");

            migrationBuilder.RenameIndex(
                name: "IX_Coins_ServerId",
                table: "Gems",
                newName: "IX_Gems_ServerId");

            migrationBuilder.AddPrimaryKey(
                name: "PK_Gems",
                table: "Gems",
                column: "Id");

            migrationBuilder.AddForeignKey(
                name: "FK_Gems_Servers_ServerId",
                table: "Gems",
                column: "ServerId",
                principalTable: "Servers",
                principalColumn: "Id",
                onDelete: ReferentialAction.Cascade);
        }
    }
}
