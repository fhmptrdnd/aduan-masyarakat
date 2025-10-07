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

$success = '';
$error = '';

// Proses form aduan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = htmlspecialchars($_POST['judul']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $lokasi = htmlspecialchars($_POST['lokasi']);
    $kategori = htmlspecialchars($_POST['kategori']);
    
    if (empty($judul) || empty($deskripsi) || empty($lokasi) || empty($kategori)) {
        $error = "Semua field harus diisi!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO aduan (user_id, judul, deskripsi, lokasi, kategori) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $judul, $deskripsi, $lokasi, $kategori])) {
            $success = "Aduan berhasil dikirim!";
            // Reset form
            $_POST = array();
        } else {
            $error = "Terjadi kesalahan saat mengirim aduan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Aduan - Forum Aduan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-2xl font-bold text-pink-600">Buat Aduan Baru</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700"><?php echo $_SESSION['user_nama']; ?></span>
                    <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Kembali</a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Tombol Kembali -->
        <div class="mb-6">
            <a href="dashboard.php" class="inline-flex items-center text-pink-600 hover:text-pink-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Pengaduan</h2>
            
            <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">Judul Aduan *</label>
                    <input type="text" id="judul" name="judul" required 
                           value="<?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                           placeholder="Contoh: Jalan Rusak di Depan Pasar">
                </div>
                
                <div>
                    <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                    <select id="kategori" name="kategori" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="">Pilih Kategori</option>
                        <option value="Infrastruktur" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Infrastruktur') ? 'selected' : ''; ?>>Infrastruktur</option>
                        <option value="Kebersihan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Kebersihan') ? 'selected' : ''; ?>>Kebersihan</option>
                        <option value="Keamanan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Keamanan') ? 'selected' : ''; ?>>Keamanan</option>
                        <option value="Kesehatan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Kesehatan') ? 'selected' : ''; ?>>Kesehatan</option>
                        <option value="Lainnya" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-2">Lokasi *</label>
                    <input type="text" id="lokasi" name="lokasi" required 
                           value="<?php echo isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                           placeholder="Contoh: Jl. Merdeka No. 15, Kelurahan Sukajadi">
                </div>
                
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Lengkap *</label>
                    <textarea id="deskripsi" name="deskripsi" required rows="6"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                              placeholder="Jelaskan secara detail masalah yang Anda laporkan, termasuk waktu kejadian, dampak yang ditimbulkan, dan harapan penyelesaian..."><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="dashboard.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 focus:ring-2 focus:ring-pink-500 transition-colors">
                        Kirim Aduan
                    </button>
                </div>
            </form>
        </div>

        <!-- Informasi Tambahan -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">📝 Tips Mengisi Aduan</h3>
            <ul class="text-blue-700 space-y-2">
                <li>• Jelaskan masalah dengan jelas dan detail</li>
                <li>• Sertakan lokasi yang spesifik</li>
                <li>• Cantumkan waktu kejadian jika memungkinkan</li>
                <li>• Upload foto pendukung jika ada</li>
                <li>• Aduan Anda akan diproses dalam 1-3 hari kerja</li>
            </ul>
        </div>
    </div>
</body>
</html>