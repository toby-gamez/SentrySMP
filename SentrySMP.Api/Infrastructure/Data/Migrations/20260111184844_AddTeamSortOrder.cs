using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace SentrySMP.Api.Infrastructure.Data.Migrations
{
    /// <inheritdoc />
    public partial class AddTeamSortOrder : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<int>(
                name: "SortOrder",
                table: "TeamMembers",
                type: "int",
                nullable: false,
                defaultValue: 0);

            migrationBuilder.AddColumn<int>(
                name: "SortOrder",
                table: "TeamCategories",
                type: "int",
                nullable: false,
                defaultValue: 0);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "SortOrder",
                table: "TeamMembers");

            migrationBuilder.DropColumn(
                name: "SortOrder",
                table: "TeamCategories");
        }
    }
}
