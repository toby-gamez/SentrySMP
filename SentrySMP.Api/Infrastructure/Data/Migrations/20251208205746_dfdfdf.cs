using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace SentrySMP.Api.Infrastructure.Data.Migrations
{
    /// <inheritdoc />
    public partial class dfdfdf : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(
                name: "FK_Shards_Servers_ServerId",
                table: "Shards");

            migrationBuilder.DropPrimaryKey(
                name: "PK_Shards",
                table: "Shards");

            migrationBuilder.RenameTable(
                name: "Shards",
                newName: "Coins");

            migrationBuilder.RenameIndex(
                name: "IX_Shards_ServerId",
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
                newName: "Shards");

            migrationBuilder.RenameIndex(
                name: "IX_Coins_ServerId",
                table: "Shards",
                newName: "IX_Shards_ServerId");

            migrationBuilder.AddPrimaryKey(
                name: "PK_Shards",
                table: "Shards",
                column: "Id");

            migrationBuilder.AddForeignKey(
                name: "FK_Shards_Servers_ServerId",
                table: "Shards",
                column: "ServerId",
                principalTable: "Servers",
                principalColumn: "Id",
                onDelete: ReferentialAction.Cascade);
        }
    }
}
