const fs = require('fs');
let content = fs.readFileSync('c:/laragon/www/barbershop/admin/layanan.php', 'utf8');

const sToken = "// HANDLE POST: Tambah Layanan";
const eToken = "<?php admin_header('Layanan & Harga', 'layanan'); ?>";

const startIndex = content.indexOf(sToken);
const endIndex = content.indexOf(eToken);

if (startIndex !== -1 && endIndex !== -1) {
    const beforeToken = content.substring(0, startIndex);
    // Find the nearest opening of a comment or just slice from sToken
    // Actually we can simply replace the block with session stuff + picking up GET
    const replacement = `
$modalError = $_SESSION['modalError'] ?? $_GET['error'] ?? '';
$modalSuccess = $_SESSION['modalSuccess'] ?? $_GET['success'] ?? '';
unset($_SESSION['modalError'], $_SESSION['modalSuccess']);

admin_ensure_layanan_image_column($conn);
?>
`;

    // lookback array lines to remove the // ------ box 
    let realStart = content.lastIndexOf('// ', startIndex);
    if (realStart === -1) realStart = startIndex;

    const before = content.substring(0, realStart);
    const after = content.substring(endIndex);

    let newContent = before + replacement + after;

    // Change form actions
    newContent = newContent.replace(/<form method="POST" enctype="multipart\/form-data"/g, '<form method="POST" action="../functions/crud_layanan.php" enctype="multipart/form-data"');

    fs.writeFileSync('c:/laragon/www/barbershop/admin/layanan.php', newContent);
    console.log('Success replacing logic block and forms in layanan.php');
} else {
    console.log('Could not find tokens: start=' + startIndex + ', end=' + endIndex);
}
