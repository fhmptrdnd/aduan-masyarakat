<?php
session_start();

// KONEKSI DATABASE LARAGON
$host = 'localhost';
$dbname = 'forum_aduan';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Jika database tidak ada, buat otomatis
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
        $pdo->exec("USE `$dbname`");
        
        // Buat tabel users dengan kolom role
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            telepon VARCHAR(20) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Buat tabel aduan
        $pdo->exec("CREATE TABLE IF NOT EXISTS aduan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            judul VARCHAR(200) NOT NULL,
            deskripsi TEXT NOT NULL,
            lokasi VARCHAR(200),
            kategori VARCHAR(50),
            status ENUM('menunggu', 'diproses', 'selesai') DEFAULT 'menunggu',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Buat user admin default
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT IGNORE INTO users (nama, email, telepon, password, role) VALUES (?, ?, ?, ?, ?)")
            ->execute(['Administrator', 'admin@email.com', '08123456789', $hashed_password, 'admin']);
        
    } catch(PDOException $e2) {
        die("Error setup database: " . $e2->getMessage());
    }
}

// Fungsi untuk membersihkan input
function bersihkan_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = bersihkan_input($_POST['email']);
    $password = bersihkan_input($_POST['password']);
    
    // Cek user di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role']; // Tambah session role
        
        // Redirect berdasarkan role
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $login_error = "Email atau password salah!";
    }
}

// Proses register (default role = user)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    $nama = bersihkan_input($_POST['nama']);
    $email = bersihkan_input($_POST['email']);
    $telepon = bersihkan_input($_POST['telepon']);
    $password = bersihkan_input($_POST['password']);
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Format email tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $register_error = "Email sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru (default role = user)
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, telepon, password) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$nama, $email, $telepon, $hashed_password])) {
                $register_success = "Pendaftaran berhasil! Silakan login.";
                // Auto switch ke modal login setelah registrasi berhasil
                echo "<script>setTimeout(() => { switchModal('login'); }, 1000);</script>";
            } else {
                $register_error = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Aduan Masyarakat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translate(-50%, -40%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .modal.active {
            display: block;
            animation: slideUp 0.4s ease-out;
        }

        .slide {
            transition: opacity 1s ease-in-out;
        }

        .gradient-text {
            background: linear-gradient(135deg, #ff1493, #ff69b4, #ffb6c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .slide::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .overlay-content {
            position: relative;
            z-index: 10;
        }
    </style>
</head>

<body class="overflow-x-hidden">

    <header>
        <nav class="fixed top-0 w-full px-8 py-4 bg-white/10 backdrop-blur-md z-50 border-b border-white/20">
            <div class="flex justify-between items-center max-w-7xl mx-auto">
                <h1 class="text-2xl font-bold text-white drop-shadow-lg">
                    Forum Aduan Kota
                </h1>
                <ul class="flex gap-8 items-center">
                    <li><a href="#" class="text-white font-medium drop-shadow hover:text-pink-300 transition-colors">Beranda</a></li>
                    <li><a href="#" class="text-white font-medium drop-shadow hover:text-pink-300 transition-colors">Tentang</a></li>
                    <li><a href="#" class="text-white font-medium drop-shadow hover:text-pink-300 transition-colors">Kontak</a></li>
                    <li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($_SESSION['user_role'] == 'admin'): ?>
                                <a href="admin_dashboard.php" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-400 text-white font-bold rounded-full hover:scale-105 transition-transform shadow-lg shadow-blue-500/50">
                                    Admin Dashboard
                                </a>
                            <?php else: ?>
                                <a href="dashboard.php" class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-400 text-white font-bold rounded-full hover:scale-105 transition-transform shadow-lg shadow-green-500/50">
                                    Dashboard
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button onclick="openModal('login')" class="px-6 py-2 bg-gradient-to-r from-pink-600 to-pink-400 text-white font-bold rounded-full hover:scale-105 transition-transform shadow-lg shadow-pink-500/50">
                                Masuk / Daftar
                            </button>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <section id="slideshow" class="fixed w-full h-screen overflow-hidden transition-all duration-500">
            <article class="slide absolute w-full h-full opacity-0">
                <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&h=1080&fit=crop" 
                     alt="Pemandangan Kota Modern" 
                     class="w-full h-full object-cover">
            </article>
            
            <article class="slide absolute w-full h-full opacity-0">
                <img src="https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=1920&h=1080&fit=crop" 
                     alt="Komunitas Masyarakat" 
                     class="w-full h-full object-cover">
            </article>
            
            <article class="slide absolute w-full h-full opacity-0">
                <img src="https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=1920&h=1080&fit=crop" 
                     alt="Pembangunan Kota" 
                     class="w-full h-full object-cover">
            </article>

            <div class="overlay-content absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white w-11/12 max-w-4xl">
                <h2 class="text-6xl font-bold mb-4 drop-shadow-2xl gradient-text">
                    Suara Anda, Perubahan Kota
                </h2>
                <p class="text-2xl mb-8 drop-shadow-lg">
                    Platform aduan masyarakat untuk kota yang lebih baik
                </p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <a href="admin_dashboard.php" class="px-10 py-4 bg-gradient-to-r from-blue-600 to-blue-400 text-white text-xl font-bold rounded-full hover:scale-110 transition-transform shadow-2xl shadow-blue-500/50 inline-block">
                            Admin Dashboard
                        </a>
                    <?php else: ?>
                        <a href="buat_aduan.php" class="px-10 py-4 bg-gradient-to-r from-green-600 to-green-400 text-white text-xl font-bold rounded-full hover:scale-110 transition-transform shadow-2xl shadow-green-500/50 inline-block">
                            Buat Aduan Baru
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <button onclick="openModal('login')" class="px-10 py-4 bg-gradient-to-r from-pink-600 to-pink-400 text-white text-xl font-bold rounded-full hover:scale-110 transition-transform shadow-2xl shadow-pink-500/50">
                        Sampaikan Aduan Anda
                    </button>
                <?php endif; ?>
            </div>
        </section>

        <!-- Modal Login -->
        <aside id="loginModal" class="modal hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl z-50 w-11/12 max-w-md p-8">
            <header class="flex justify-between items-center mb-6">
                <h3 class="text-3xl font-bold text-pink-600">Masuk</h3>
                <button onclick="closeModal('login')" class="text-gray-400 hover:text-gray-600 text-3xl font-bold" aria-label="Tutup">&times;</button>
            </header>
            
            <?php if(isset($login_error)): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-xl">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="mb-6">
                    <label for="login-email" class="block mb-2 text-pink-700 font-semibold">Email</label>
                    <input type="email" id="login-email" name="email" required 
                           class="w-full px-4 py-3 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="mb-6">
                    <label for="login-password" class="block mb-2 text-pink-700 font-semibold">Password</label>
                    <input type="password" id="login-password" name="password" required 
                           class="w-full px-4 py-3 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors">
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-pink-600 to-pink-400 text-white font-bold rounded-xl hover:scale-105 transition-transform shadow-lg shadow-pink-500/50">
                    Masuk
                </button>
            </form>
            
            <div class="text-center mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-700 font-semibold">Login Demo:</p>
                <p class="text-sm text-blue-600">Admin: admin@email.com / admin123</p>
                <p class="text-sm text-blue-600">User: Daftar baru atau gunakan email lain</p>
            </div>
            
            <footer class="text-center mt-6">
                <p class="text-gray-600">Belum punya akun? 
                    <button onclick="switchModal('register')" class="text-pink-600 font-semibold hover:underline">Daftar di sini</button>
                </p>
            </footer>
        </aside>

        <!-- Modal Register -->
        <aside id="registerModal" class="modal hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl z-50 w-11/12 max-w-md p-8">
            <header class="flex justify-between items-center mb-6">
                <h3 class="text-3xl font-bold text-pink-600">Daftar</h3>
                <button onclick="closeModal('register')" class="text-gray-400 hover:text-gray-600 text-3xl font-bold" aria-label="Tutup">&times;</button>
            </header>
            
            <?php if(isset($register_error)): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-xl">
                    <?php echo $register_error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($register_success)): ?>
                <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-xl">
                    <?php echo $register_success; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="mb-4">
                    <label for="register-nama" class="block mb-2 text-pink-700 font-semibold">Nama Lengkap</label>
                    <input type="text" id="register-nama" name="nama" required 
                           class="w-full px-4 py-3 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors"
                           value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                </div>
                
                <div class="mb-4">
                    <label for="register-email" class="block mb-2 text-pink-700 font-semibold">Email</label>
                    <input type="email" id="register-email" name="email" required 
                           class="w-full px-4 py-3 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="mb-4">
                    <label for="register-telepon" class="block mb-2 text-pink-700 font-semibold">No. Telepon</label>
                    <input type="tel" id="register-telepon" name="telepon" required 
                           class="w-full px-4 py-3 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors"
                           value="<?php echo isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>">
                </div>
                
                <div class="mb-6">
                    <label for="register-password" class="block mb-2 text-pink-700 font-semibold">Password</label>
                    <input type="password" id="register-password" name="password" required 
                           class="w-full px-4 py-3 border-2 border-pink-200 rounded-xl focus:outline-none focus:border-pink-500 transition-colors">
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-pink-600 to-pink-400 text-white font-bold rounded-xl hover:scale-105 transition-transform shadow-lg shadow-pink-500/50">
                    Daftar
                </button>
            </form>
            
            <footer class="text-center mt-6">
                <p class="text-gray-600">Sudah punya akun? 
                    <button onclick="switchModal('login')" class="text-pink-600 font-semibold hover:underline">Masuk di sini</button>
                </p>
            </footer>
        </aside>
    </main>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        
        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('opacity-100'));
            slides.forEach(slide => slide.classList.add('opacity-0'));
            
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].classList.remove('opacity-0');
            slides[currentSlide].classList.add('opacity-100');
        }
        
        function nextSlide() {
            showSlide(currentSlide + 1);
        }
        
        showSlide(0);
        setInterval(nextSlide, 3000);
        
        function openModal(type) {
            const slideshow = document.getElementById('slideshow');
            const modal = document.getElementById(type + 'Modal');
            
            slideshow.classList.add('blur-lg', 'brightness-50');
            modal.classList.add('active');
        }
        
        function closeModal(type) {
            const slideshow = document.getElementById('slideshow');
            const modal = document.getElementById(type + 'Modal');
            
            slideshow.classList.remove('blur-lg', 'brightness-50');
            modal.classList.remove('active');
        }
        
        function switchModal(type) {
            closeModal(type === 'login' ? 'register' : 'login');
            setTimeout(() => openModal(type), 300);
        }
        
        // Auto open modal jika ada error atau success message
        <?php if(isset($login_error) || (isset($_POST['action']) && $_POST['action'] == 'login')): ?>
            window.onload = function() {
                openModal('login');
            };
        <?php elseif(isset($register_error) || isset($register_success) || (isset($_POST['action']) && $_POST['action'] == 'register')): ?>
            window.onload = function() {
                openModal('register');
            };
        <?php endif; ?>

        // Close modal dengan ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('login');
                closeModal('register');
            }
        });
    </script>

</body>
</html>