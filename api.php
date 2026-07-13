<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

// ==========================================
// KONFIGURASI DATABASE
// ==========================================
$host = "localhost";
$db_name = "bald6243_db_reuni2026";
$username = "bald6243_reuniakbar2026"; // Sesuaikan dengan username database Anda (default XAMPP: root)
$password = "m[Ucp__.QtpJF2Fu";     // Sesuaikan dengan password database Anda (default XAMPP: kosong)
try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Auto-create admin jika belum ada (Password: admin123)
    $cekAdmin = $conn->query("SELECT * FROM admin_users WHERE username='admin'");
    if($cekAdmin->rowCount() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admin_users (username, password) VALUES ('admin', '$hash')");
    }
} catch(PDOException $exception) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal."]);
    exit;
}

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents("php://input"), true);

// ==========================================
// 1. LOGIN & LOGOUT ADMIN
// ==========================================
if ($action == 'login') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :user");
    $stmt->execute(['user' => $user]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        echo json_encode(["status" => "success", "message" => "Login berhasil"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Username atau Password salah!"]);
    }
    exit;
}

if ($action == 'logout') {
    session_destroy();
    echo json_encode(["status" => "success"]);
    exit;
}

// ==========================================
// 2. AMBIL PENGATURAN (Untuk ditampilkan di index.html & admin)
// ==========================================
if ($action == 'get_pengaturan') {
    $stmt = $conn->query("SELECT * FROM pengaturan_website");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['kunci_pengaturan']] = $row['nilai_pengaturan'];
    }
    echo json_encode($settings);
    exit;
}

// ==========================================
// 3. PROSES SUBMIT PENDAFTARAN DARI INDEX.HTML
// ==========================================
if ($action == 'submit_daftar' && !empty($data['tipe_pendaftaran'])) {
    $tipe = $data['tipe_pendaftaran'];
    $berdonasi = $data['berdonasi'] ?? 'Tidak';

    if ($tipe === 'individu') {
        $stmt = $conn->prepare("INSERT INTO pendaftaran_individu (nama_lengkap, asal_sekolah, tahun_lulus, no_wa, domisili, jumlah_pendamping, berdonasi) VALUES (:nama, :asal, :tahun, :wa, :domisili, :jml, :berdonasi)");
        $berhasil = $stmt->execute([
            'nama' => $data['nama'], 'asal' => $data['asal'], 'tahun' => $data['tahun'], 
            'wa' => $data['wa'], 'domisili' => $data['domisili'], 'jml' => $data['jmlPendamping'], 'berdonasi' => $berdonasi
        ]);
    } else {
        // Asal sekolah dan angkatan dipisah
        $parts = explode(" - Angkatan ", $data['sekolahAngkatan']);
        $asalSekolah = $parts[0] ?? '';
        $angkatan = $parts[1] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO pendaftaran_kelompok (nama_perwakilan, no_wa_kelompok, asal_sekolah, angkatan, jumlah_anggota, daftar_anggota, berdonasi) VALUES (:nama, :wa, :asal, :angkatan, :jml, :daftar, :berdonasi)");
        $berhasil = $stmt->execute([
            'nama' => $data['namaPerwakilan'], 'wa' => $data['waKelompok'], 'asal' => $asalSekolah,
            'angkatan' => $angkatan, 'jml' => $data['jumlahAnggota'], 'daftar' => $data['daftarNama'], 'berdonasi' => $berdonasi
        ]);
    }

    echo json_encode(["status" => $berhasil ? "success" : "error"]);
    exit;
}

// ==========================================
// 4. CEK OTORISASI ADMIN UNTUK FITUR DI BAWAH INI
// ==========================================
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// ==========================================
// 5. AMBIL DATA PESERTA (Untuk Dashboard Admin)
// ==========================================
if ($action == 'get_peserta') {
    $individu = $conn->query("SELECT * FROM pendaftaran_individu ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $kelompok = $conn->query("SELECT * FROM pendaftaran_kelompok ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["individu" => $individu, "kelompok" => $kelompok]);
    exit;
}

// ==========================================
// 6. SIMPAN PENGATURAN (Upload Base64 dari Admin)
// ==========================================
if ($action == 'simpan_pengaturan') {
    $kunci = $data['kunci'] ?? '';
    $nilai = $data['nilai'] ?? ''; // Format Base64

    if ($kunci != '') {
        $stmt = $conn->prepare("UPDATE pengaturan_website SET nilai_pengaturan = :nilai WHERE kunci_pengaturan = :kunci");
        $berhasil = $stmt->execute(['nilai' => $nilai, 'kunci' => $kunci]);
        echo json_encode(["status" => $berhasil ? "success" : "error"]);
    }
    exit;
}
?>
