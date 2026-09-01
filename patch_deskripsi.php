<?php
include "config/database.php";
$query = "ALTER TABLE admin_notifications ADD COLUMN deskripsi TEXT NULL AFTER pesan";
$result = mysqli_query($conn, $query);
if ($result) {
    echo "SUCCESS: Column added.";
} else {
    echo "ERROR: " . mysqli_error($conn);
}
?>
