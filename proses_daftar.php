<?php
// Izinkan akses CORS (Cross-Origin Resource Sharing) agar bisa diakses HTML
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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
} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal terhubung ke database: " . $exception->getMessage()]);
    exit;
}

// ==========================================
// MENERIMA DATA JSON DARI HTML (FETCH API)
// ==========================================
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->tipe_pendaftaran)) {
    
    // Data Umum
    $tipe = $data->tipe_pendaftaran;
    $berdonasi = $data->berdonasi ?? 'Tidak';

    // Set nilai default Null
    $nama = $asal = $tahun = $wa = $domisili = $jmlPendamping = null;
    $namaPerwakilan = $waKelompok = $sekolahAngkatan = $jumlahAnggota = $daftarNama = null;

    // Filter berdasarkan tipe pendaftaran
    if ($tipe === 'individu') {
        $nama = $data->nama ?? null;
        $asal = $data->asal ?? null;
        $tahun = $data->tahun ?? null;
        $wa = $data->wa ?? null;
        $domisili = $data->domisili ?? null;
        $jmlPendamping = $data->jmlPendamping ?? 0;
    } else if ($tipe === 'kelompok') {
        $namaPerwakilan = $data->namaPerwakilan ?? null;
        $waKelompok = $data->waKelompok ?? null;
        $sekolahAngkatan = $data->sekolahAngkatan ?? null;
        $jumlahAnggota = $data->jumlahAnggota ?? 0;
        $daftarNama = $data->daftarNama ?? null;
    }

    // ==========================================
    // QUERY INSERT DATABASE
    // ==========================================
    $query = "INSERT INTO pendaftaran 
              (tipe_pendaftaran, berdonasi, nama_lengkap, asal_sekolah, tahun_lulus, no_wa, domisili, jumlah_pendamping, nama_perwakilan, no_wa_kelompok, sekolah_angkatan, jumlah_anggota, daftar_anggota) 
              VALUES 
              (:tipe, :berdonasi, :nama, :asal, :tahun, :wa, :domisili, :jmlPendamping, :namaPerwakilan, :waKelompok, :sekolahAngkatan, :jumlahAnggota, :daftarNama)";
    
    $stmt = $conn->prepare($query);

    // Bind parameter (untuk keamanan dari SQL Injection)
    $stmt->bindParam(":tipe", $tipe);
    $stmt->bindParam(":berdonasi", $berdonasi);
    $stmt->bindParam(":nama", $nama);
    $stmt->bindParam(":asal", $asal);
    $stmt->bindParam(":tahun", $tahun);
    $stmt->bindParam(":wa", $wa);
    $stmt->bindParam(":domisili", $domisili);
    $stmt->bindParam(":jmlPendamping", $jmlPendamping);
    $stmt->bindParam(":namaPerwakilan", $namaPerwakilan);
    $stmt->bindParam(":waKelompok", $waKelompok);
    $stmt->bindParam(":sekolahAngkatan", $sekolahAngkatan);
    $stmt->bindParam(":jumlahAnggota", $jumlahAnggota);
    $stmt->bindParam(":daftarNama", $daftarNama);

    // Eksekusi Query
    if($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Data berhasil disimpan ke database."]);
    } else {
        http_response_code(503);
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan data."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Data pendaftaran tidak lengkap."]);
}
?>
