<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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

// Ambil data aduan user
$stmt = $pdo->prepare("SELECT * FROM aduan WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$aduan_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Forum Aduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-2xl font-bold text-pink-600">Dashboard Aduan</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Halo, <?php echo $_SESSION['user_nama']; ?></span>
                    <a href="buat_aduan.php" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">Buat Aduan Baru</a>
                    <a href="logout.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <!-- Tombol Kembali -->
        <div class="mb-6">
            <a href="index.php" class="inline-flex items-center text-pink-600 hover:text-pink-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Aduan Saya</h2>
            
            <?php if (empty($aduan_list)): ?>
                <div class="text-center py-8">
                    <p class="text-gray-500 text-lg">Belum ada aduan yang dibuat.</p>
                    <a href="buat_aduan.php" class="inline-block mt-4 bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700">
                        Buat Aduan Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Lokasi</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aduan_list as $aduan): ?>
                            <tr class="border-b hover:bg-gray-50">
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>