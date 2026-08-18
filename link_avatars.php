<?php
include "config/database.php";

$dir = __DIR__ . "/uploads/avatars/";
if (is_dir($dir)) {
    $files = scandir($dir);
    $latest_avatars = [];

    // Find the latest avatar for each user ID
    foreach ($files as $file) {
        if (preg_match('/^avatar_(\d+)_(\d+)\.\w+$/', $file, $matches)) {
            $user_id = (int) $matches[1];
            $timestamp = (int) $matches[2];

            if (
                !isset($latest_avatars[$user_id]) ||
                $timestamp > $latest_avatars[$user_id]["time"]
            ) {
                $latest_avatars[$user_id] = [
                    "file" => $file,
                    "time" => $timestamp,
                ];
            }
        }
    }

    // Update the database
    foreach ($latest_avatars as $user_id => $data) {
        $filename = $data["file"];

        // Only update if current avatar is empty
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users SET avatar = ? WHERE id_user = ?",
        );
        mysqli_stmt_bind_param($stmt, "si", $filename, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "Linked {$filename} to user ID {$user_id}\n";
        } else {
            echo "Failed to link {$filename} to user ID {$user_id}: " .
                mysqli_error($conn) .
                "\n";
        }
        mysqli_stmt_close($stmt);
    }
} else {
    echo "Avatars directory not found.\n";
}
?>
