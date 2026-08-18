<?php
include "../config/database.php";
include "../config/helper.php";
check_login("admin");

/**
 * Menghasilkan dump SQL dari database yang sedang dipakai aplikasi.
 * File ini dapat diimpor kembali melalui phpMyAdmin bila perlu memulihkan data.
 */
function sql_value($conn, $value)
{
    if ($value === null) {
        return "NULL";
    }

    return "'" . mysqli_real_escape_string($conn, $value) . "'";
}

$databaseResult = mysqli_query($conn, "SELECT DATABASE() AS database_name");
$databaseRow = $databaseResult ? mysqli_fetch_assoc($databaseResult) : null;
$databaseName = $databaseRow["database_name"] ?? "barber_db";

header("Content-Type: application/sql; charset=utf-8");
header(
    'Content-Disposition: attachment; filename="barber_db-backup-' .
        date("Ymd-His") .
        '.sql"',
);
header("Cache-Control: no-store, no-cache, must-revalidate");

echo "-- Backup database " . $databaseName . "\n";
echo "-- Dibuat oleh Barber.co pada " . date("Y-m-d H:i:s") . "\n\n";
echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "SET time_zone = \"+00:00\";\n";
echo "SET NAMES utf8mb4;\n\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";
echo "CREATE DATABASE IF NOT EXISTS `" .
    str_replace("`", "``", $databaseName) .
    "`;\n";
echo "USE `" . str_replace("`", "``", $databaseName) . "`;\n\n";

$tables = mysqli_query($conn, "SHOW TABLES");
while ($tableRow = $tables ? mysqli_fetch_row($tables) : null) {
    $table = $tableRow[0];
    $safeTable = "`" . str_replace("`", "``", $table) . "`";
    $createResult = mysqli_query($conn, "SHOW CREATE TABLE " . $safeTable);
    $createRow = $createResult ? mysqli_fetch_assoc($createResult) : null;
    $createSql = $createRow["Create Table"] ?? "";

    echo "-- --------------------------------------------------------\n\n";
    echo "DROP TABLE IF EXISTS " . $safeTable . ";\n";
    echo $createSql . ";\n\n";

    $rows = mysqli_query($conn, "SELECT * FROM " . $safeTable);
    if ($rows && mysqli_num_rows($rows) > 0) {
        $fields = mysqli_fetch_fields($rows);
        $columns = array_map(function ($field) {
            return "`" . str_replace("`", "``", $field->name) . "`";
        }, $fields);

        echo "INSERT INTO " .
            $safeTable .
            " (" .
            implode(", ", $columns) .
            ") VALUES\n";
        $values = [];
        while ($row = mysqli_fetch_assoc($rows)) {
            $items = [];
            foreach ($fields as $field) {
                $items[] = sql_value($conn, $row[$field->name]);
            }
            $values[] = "(" . implode(", ", $items) . ")";
        }
        echo implode(",\n", $values) . ";\n\n";
    }
}

echo "SET FOREIGN_KEY_CHECKS = 1;\n";
echo "COMMIT;\n";
exit();
?>
