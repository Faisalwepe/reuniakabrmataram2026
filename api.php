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
    
    // Auto-fix tabel admin jika kolom role belum ada (mencegah error jika lupa update SQL)
    $conn->query("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS role ENUM('administrator', 'admin') DEFAULT 'admin'");
} catch(PDOException $exception) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $exception->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents("php://input"), true);

// ==========================================
// 1. LOGIN & LOGOUT ADMIN
// ==========================================
if ($action == 'login') {
    $user = $_POST['username'] ?? $data['username'] ?? '';
    $pass = $_POST['password'] ?? $data['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :user");
    $stmt->execute(['user' => $user]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        echo json_encode(["status" => "success", "message" => "Login berhasil"]);
    } else {
        // Auto-fix khusus username 'admin' dan pass 'faisal' jika error hash
        if ($user === 'admin' && $pass === 'faisal') {
            $newHash = password_hash('faisal', PASSWORD_DEFAULT);
            $conn->query("UPDATE admin_users SET password = '$newHash', role='administrator' WHERE username = 'admin'");
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_role'] = 'administrator';
            echo json_encode(["status" => "success", "message" => "Login berhasil (Auto-fixed)"]);
            exit;
        }
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
// 2. AMBIL PENGATURAN (PUBLIK) & SUBMIT FORM
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

if ($action == 'submit_daftar' && !empty($data['tipe_pendaftaran'])) {
    $tipe = $data['tipe_pendaftaran'];
    $berdonasi = $data['berdonasi'] ?? 'Tidak';
    // Tangkap nominal donasi (ubah ke format integer murni tanpa titik)
    $nominal = isset($data['nominalDonasi']) ? preg_replace("/[^0-9]/", "", $data['nominalDonasi']) : 0;
    if (empty($nominal)) $nominal = 0;

    try {
        if ($tipe === 'individu') {
            $stmt = $conn->prepare("INSERT INTO pendaftaran_individu (nama_lengkap, asal_sekolah, tahun_lulus, no_wa, domisili, jumlah_pendamping, berdonasi, nominal_donasi) VALUES (:nama, :asal, :tahun, :wa, :domisili, :jml, :berdonasi, :nominal)");
            $berhasil = $stmt->execute([
                'nama' => $data['nama'], 'asal' => $data['asal'], 'tahun' => $data['tahun'], 
                'wa' => $data['wa'], 'domisili' => $data['domisili'], 'jml' => $data['jmlPendamping'], 'berdonasi' => $berdonasi, 'nominal' => $nominal
            ]);
        } else {
            $parts = explode(" - Angkatan ", $data['sekolahAngkatan']);
            $stmt = $conn->prepare("INSERT INTO pendaftaran_kelompok (nama_perwakilan, no_wa_kelompok, asal_sekolah, angkatan, jumlah_anggota, daftar_anggota, berdonasi, nominal_donasi) VALUES (:nama, :wa, :asal, :angkatan, :jml, :daftar, :berdonasi, :nominal)");
            $berhasil = $stmt->execute([
                'nama' => $data['namaPerwakilan'], 'wa' => $data['waKelompok'], 'asal' => $parts[0] ?? '-',
                'angkatan' => $parts[1] ?? '-', 'jml' => $data['jumlahAnggota'], 'daftar' => $data['daftarNama'], 'berdonasi' => $berdonasi, 'nominal' => $nominal
            ]);
        }
        echo json_encode(["status" => $berhasil ? "success" : "error"]);
    } catch (PDOException $e) { echo json_encode(["status" => "error", "message" => $e->getMessage()]); }
    exit;
}

// ==========================================
// 3. PROTEKSI AREA ADMIN
// ==========================================
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}
$is_administrator = ($_SESSION['admin_role'] === 'administrator');

// ==========================================
// 4. API UNTUK DASHBOARD ADMIN
// ==========================================
if ($action == 'get_peserta') {
    $individu = $conn->query("SELECT * FROM pendaftaran_individu ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $kelompok = $conn->query("SELECT * FROM pendaftaran_kelompok ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["individu" => $individu, "kelompok" => $kelompok]);
    exit;
}

if ($action == 'simpan_pengaturan' && $is_administrator) {
    $kunci = $data['kunci'] ?? '';
    $nilai = $data['nilai'] ?? '';
    if ($kunci != '') {
        $stmt = $conn->prepare("UPDATE pengaturan_website SET nilai_pengaturan = :nilai WHERE kunci_pengaturan = :kunci");
        $stmt->execute(['nilai' => $nilai, 'kunci' => $kunci]);
        echo json_encode(["status" => "success"]);
    }
    exit;
}

// --- MANAJEMEN USER ---
if ($action == 'get_users' && $is_administrator) {
    $users = $conn->query("SELECT id, username, role FROM admin_users")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
    exit;
}

if ($action == 'save_user' && $is_administrator) {
    $uid = $data['id'] ?? 0;
    $username = $data['username'];
    $role = $data['role'];
    $password = $data['password'] ?? '';
    
    try {
        if ($uid == 0) { // Tambah User
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admin_users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hash, $role]);
        } else { // Edit User
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admin_users SET username=?, role=?, password=? WHERE id=?");
                $stmt->execute([$username, $role, $hash, $uid]);
            } else {
                $stmt = $conn->prepare("UPDATE admin_users SET username=?, role=? WHERE id=?");
                $stmt->execute([$username, $role, $uid]);
            }
        }
        echo json_encode(["status" => "success"]);
    } catch(PDOException $e) { echo json_encode(["status" => "error", "message" => "Username mungkin sudah dipakai"]); }
    exit;
}

if ($action == 'delete_user' && $is_administrator) {
    $uid = $data['id'];
    if ($uid != $_SESSION['admin_id']) { // Tidak bisa hapus diri sendiri
        $conn->prepare("DELETE FROM admin_users WHERE id=?")->execute([$uid]);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Tidak bisa menghapus akun Anda sendiri!"]);
    }
    exit;
}

if ($action == 'update_profile') {
    $password = $data['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $conn->prepare("UPDATE admin_users SET password=? WHERE id=?")->execute([$hash, $_SESSION['admin_id']]);
    echo json_encode(["status" => "success"]);
    exit;
}
?>
