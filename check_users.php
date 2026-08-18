<?php
include "config/database.php";
$res = mysqli_query($conn, "SELECT id_user, username, avatar FROM users");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row["id_user"]}, USER: {$row["username"]}, AVATAR: '{$row["avatar"]}'\n";
}
echo "Avatars dir:\n";
print_r(scandir(__DIR__ . "/uploads/avatars/"));
?>
