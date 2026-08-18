const fs = require('fs');
let content = fs.readFileSync('c:/laragon/www/barbershop/admin/pengguna.php', 'utf8');

const sToken = "// HANDLE POST: Tambah Kapster";
const eToken = "<?php admin_header('Pengguna (Users)', 'pengguna'); ?>";

const startIndex = content.indexOf(sToken);
const endIndex = content.indexOf(eToken);

if (startIndex !== -1 && endIndex !== -1) {
    const replacement = `
$modalError = $_SESSION['modalError'] ?? $_GET['error'] ?? '';
$modalSuccess = $_SESSION['modalSuccess'] ?? $_GET['success'] ?? '';
unset($_SESSION['modalError'], $_SESSION['modalSuccess']);
?>
`;

    let realStart = content.lastIndexOf('// ', startIndex);
    if (realStart === -1) realStart = startIndex;
    const before = content.substring(0, realStart);
    const after = content.substring(endIndex);

    let newContent = before + replacement + after;

    // Replace forms: there are add_barber, add_user, edit_barber, edit_user logic.
    // In admin/pengguna.php, they are `<form method="POST" class="p-5 space-y-4">` etc.
    // And `<input type="hidden" name="action" value="add_barber">`.
    // I can just intercept POST methods directly since the only forms here are these additions and edits.
    // Wait, some point to Barber, some to Pengguna. 
    // They have <input type="hidden" name="action" value="xxx_yyy"> 
    // Actually, setting `<form method="POST" action="">` might be what it looks like now. Let's just find and replace `<form method="POST"` with a generic redirect? No, because we have TWO targets: crud_barber and crud_pengguna.
    // Let's replace chunks carefully.

    newContent = newContent.replace(
        /<form method="POST"([^>]*?)>\s*<input type="hidden" name="action" value="(add_barber|edit_barber)"/g,
        '<form method="POST" action="../functions/crud_barber.php"$1>\n            <input type="hidden" name="action" value="$2"'
    );
    newContent = newContent.replace(
        /<form method="POST"([^>]*?)>\s*<input type="hidden" name="action" value="(add_user|edit_user)"/g,
        '<form method="POST" action="../functions/crud_pengguna.php"$1>\n            <input type="hidden" name="action" value="$2"'
    );

    // Update delete URLs
    newContent = newContent.replace(/hapus_barber\.php\?id=/g, '../functions/crud_barber.php?action=delete_barber&id=');
    newContent = newContent.replace(/update_barber_status\.php\?id=/g, '../functions/crud_barber.php?action=update_status&id=');
    newContent = newContent.replace(/hapus_pengguna\.php\?id=/g, '../functions/crud_pengguna.php?action=delete_user&id=');

    fs.writeFileSync('c:/laragon/www/barbershop/admin/pengguna.php', newContent);
    console.log('Success replacing logic block and forms in pengguna.php');
} else {
    console.log('Could not find tokens: start=' + startIndex + ', end=' + endIndex);
}
