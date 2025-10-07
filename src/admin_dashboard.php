<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Koneksi database
$host = 'localhost';
$dbname = 'forum_aduan';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Proses pencarian
$search = '';
$aduan_list = [];
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $pdo->prepare("
        SELECT a.*, u.nama as user_nama 
        FROM aduan a 
        JOIN users u ON a.user_id = u.id 
        WHERE u.nama LIKE ? 
        ORDER BY a.created_at DESC
    ");
    $stmt->execute(["%$search%"]);
    $aduan_list = $stmt->fetchAll();
} else {
    // Ambil semua aduan
    $stmt = $pdo->prepare("
        SELECT a.*, u.nama as user_nama 
        FROM aduan a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $aduan_list = $stmt->fetchAll();
}

// Proses update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $aduan_id = $_POST['aduan_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE aduan SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $aduan_id])) {
        $success = "Status aduan berhasil diupdate!";
        // Refresh halaman
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Gagal mengupdate status aduan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Forum Aduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-2xl font-bold text-blue-600">Admin Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Admin: <?php echo $_SESSION['user_nama']; ?></span>
                    <a href="index.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Beranda</a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <!-- Tombol Kembali -->
        <div class="mb-6">
            <a href="index.php" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <?php if(isset($success)): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Kelola Aduan Masyarakat</h2>
            
            <!-- Form Pencarian -->
            <form method="GET" class="mb-6">
                <div class="flex gap-4">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari berdasarkan nama user..." 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Cari
                    </button>
                    <?php if($search): ?>
                        <a href="admin_dashboard.php" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if (empty($aduan_list)): ?>
                <div class="text-center py-8">
                    <p class="text-gray-500 text-lg">
                        <?php echo $search ? 'Tidak ditemukan aduan untuk user "' . htmlspecialchars($search) . '"' : 'Belum ada aduan yang masuk.'; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left">User</th>
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Lokasi</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aduan_list as $aduan): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($aduan['user_nama']); ?></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium"><?php echo htmlspecialchars($aduan['judul']); ?></div>
                                    <div class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars(substr($aduan['deskripsi'], 0, 100)); ?>...</div>
                                </td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($aduan['lokasi']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($aduan['kategori']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="<?php 
                                        echo $aduan['status'] == 'selesai' ? 'bg-green-100 text-green-800' : 
                                              ($aduan['status'] == 'diproses' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                        ?> px-3 py-1 rounded-full text-sm font-medium">
                                        <?php echo ucfirst($aduan['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?php echo date('d/m/Y H:i', strtotime($aduan['created_at'])); ?></td>
                                <td class="px-4 py-3">
                                    <form method="POST" class="flex gap-2">
                                        <input type="hidden" name="aduan_id" value="<?php echo $aduan['id']; ?>">
                                        <select name="status" class="text-sm border border-gray-300 rounded px-2 py-1">
                                            <option value="menunggu" <?php echo $aduan['status'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                            <option value="diproses" <?php echo $aduan['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                            <option value="selesai" <?php echo $aduan['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        </select>
                                        <button type="submit" name="update_status" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <?php
            $total_aduan = $pdo->query("SELECT COUNT(*) FROM aduan")->fetchColumn();
            $aduan_menunggu = $pdo->query("SELECT COUNT(*) FROM aduan WHERE status = 'menunggu'")->fetchColumn();
            $aduan_diproses = $pdo->query("SELECT COUNT(*) FROM aduan WHERE status = 'diproses'")->fetchColumn();
            $aduan_selesai = $pdo->query("SELECT COUNT(*) FROM aduan WHERE status = 'selesai'")->fetchColumn();
            ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="text-3xl font-bold text-blue-600"><?php echo $total_aduan; ?></div>
                <div class="text-gray-600">Total Aduan</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="text-3xl font-bold text-yellow-600"><?php echo $aduan_menunggu; ?></div>
                <div class="text-gray-600">Menunggu</div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="text-3xl font-bold text-green-600"><?php echo $aduan_selesai; ?></div>
                <div class="text-gray-600">Selesai</div>
            </div>
        </div>
    </div>
</body>
</html>