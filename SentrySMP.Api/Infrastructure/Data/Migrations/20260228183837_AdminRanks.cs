using Microsoft.EntityFrameworkCore.Metadata;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace SentrySMP.Api.Infrastructure.Data.Migrations
{
    /// <inheritdoc />
    public partial class AdminRanks : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "Role",
                table: "TeamMembers");

            migrationBuilder.AddColumn<int>(
                name: "RankId",
                table: "TeamMembers",
                type: "int",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "TeamRankId",
                table: "TeamMembers",
                type: "int",
                nullable: true);

            migrationBuilder.CreateTable(
                name: "TeamRanks",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("MySql:ValueGenerationStrategy", MySqlValueGenerationStrategy.IdentityColumn),
                    Name = table.Column<string>(type: "varchar(100)", maxLength: 100, nullable: false)
                        .Annotation("MySql:CharSet", "utf8mb4"),
                    HexColor = table.Column<string>(type: "varchar(20)", maxLength: 20, nullable: false)
                        .Annotation("MySql:CharSet", "utf8mb4")
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_TeamRanks", x => x.Id);
                })
                .Annotation("MySql:CharSet", "utf8mb4");

            migrationBuilder.CreateIndex(
                name: "IX_TeamMembers_RankId",
                table: "TeamMembers",
                column: "RankId");

            migrationBuilder.CreateIndex(
                name: "IX_TeamMembers_TeamRankId",
                table: "TeamMembers",
                column: "TeamRankId");

            migrationBuilder.AddForeignKey(
                name: "FK_TeamMembers_TeamRanks_RankId",
                table: "TeamMembers",
                column: "RankId",
                principalTable: "TeamRanks",
                principalColumn: "Id");

            migrationBuilder.AddForeignKey(
                name: "FK_TeamMembers_TeamRanks_TeamRankId",
                table: "TeamMembers",
                column: "TeamRankId",
                principalTable: "TeamRanks",
                principalColumn: "Id",
                onDelete: ReferentialAction.SetNull);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(
                name: "FK_TeamMembers_TeamRanks_RankId",
                table: "TeamMembers");

            migrationBuilder.DropForeignKey(
                name: "FK_TeamMembers_TeamRanks_TeamRankId",
                table: "TeamMembers");

            migrationBuilder.DropTable(
                name: "TeamRanks");

            migrationBuilder.DropIndex(
                name: "IX_TeamMembers_RankId",
                table: "TeamMembers");

            migrationBuilder.DropIndex(
                name: "IX_TeamMembers_TeamRankId",
                table: "TeamMembers");

            migrationBuilder.DropColumn(
                name: "RankId",
                table: "TeamMembers");

            migrationBuilder.DropColumn(
                name: "TeamRankId",
                table: "TeamMembers");

            migrationBuilder.AddColumn<string>(
                name: "Role",
                table: "TeamMembers",
                type: "varchar(100)",
                maxLength: 100,
                nullable: false,
                defaultValue: "")
                .Annotation("MySql:CharSet", "utf8mb4");
        }
    }
}
