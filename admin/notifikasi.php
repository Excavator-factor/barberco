<?php
include "_bootstrap.php";
include "_chrome.php";

$action = $_POST['action'] ?? '';
if ($action === 'mark_all_read') {
    mysqli_query($conn, "UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
    $_SESSION['modalSuccess'] = "Semua notifikasi telah ditandai dibaca.";
    header("Location: notifikasi.php");
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$limit = 20;
$offset = ($page - 1) * $limit;

// Ambil total data
$resTotal = mysqli_query($conn, "SELECT COUNT(*) as total FROM admin_notifications");
$rowTotal = $resTotal ? mysqli_fetch_assoc($resTotal) : ['total' => 0];
$totalData = $rowTotal['total'] ?? 0;
$totalPages = ceil($totalData / $limit);

// Query notif
$query = "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
$notifications = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
}
?>

<?php admin_header("Notifikasi", "notifikasi"); ?>
<div class="p-md md:p-lg max-w-container-max mx-auto w-full">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-lg mt-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface">Notifikasi Sistem</h1>
                    <p class="text-sm md:text-base text-on-surface-variant mt-1">Pemberitahuan pendaftaran dan log aktivitas.</p>
                </div>
                <div class="self-end md:self-auto">
                    <form method="POST" action="notifikasi.php" class="inline">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="bg-primary text-on-primary px-4 py-3 md:py-2 rounded-lg font-bold hover:bg-primary-container transition-colors flex items-center gap-2 text-sm w-full justify-center md:w-auto">
                            <span class="material-symbols-outlined text-[20px]">done_all</span>
                            <span>Tandai Dibaca</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-surface-container rounded-lg border border-outline-variant p-md">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-10 opacity-50">
                        <span class="material-symbols-outlined text-6xl mb-2">notifications_off</span>
                        <p>Belum ada notifikasi.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($notifications as $n): ?>
                            <a href="<?= htmlspecialchars($n['url'] ?? '#') ?>" class="block border <?= $n['is_read'] == 0 ? 'bg-primary/5 border-primary border-l-4' : 'bg-background border-outline-variant border-l-4 hover:border-primary/50' ?> rounded-lg p-4 transition-all">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $n['is_read'] == 0 ? 'bg-primary text-on-primary' : 'bg-surface-variant text-on-surface' ?>">
                                            <span class="material-symbols-outlined">notifications_active</span>
                                        </div>
                                        <div>
                                            <p class="font-bold <?= $n['is_read'] == 0 ? 'text-primary' : 'text-on-surface' ?>"><?= htmlspecialchars($n['pesan']) ?></p>
                                            <p class="text-xs text-on-surface-variant mt-1"><?= date('d F Y, H:i', strtotime($n['created_at'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="w-8 h-8 flex justify-center items-center rounded-md font-bold text-sm <?= $i == $page ? 'bg-primary text-on-primary' : 'bg-surface-variant text-on-surface hover:bg-outline-variant' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <?php endif; ?>
            </div>
            
</div>

<?php if (isset($_SESSION['modalSuccess'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= htmlspecialchars($_SESSION['modalSuccess']) ?>',
        background: '#1e2020',
        color: '#e2e2e2',
        confirmButtonColor: '#f2ca50',
    });
</script>
<?php unset($_SESSION['modalSuccess']); endif; ?>

<?php admin_footer("notifikasi"); ?>
