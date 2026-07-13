<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Reuni Akbar '26</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .loader { border: 3px solid #f3f3f3; border-top: 3px solid #1e40af; border-radius: 50%; width: 24px; height: 24px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="text-slate-800">

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <!-- ================= HALAMAN LOGIN ================= -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">RA</div>
                <h1 class="text-2xl font-bold text-slate-800">Admin Login</h1>
                <p class="text-slate-500 text-sm">Gunakan kredensial admin untuk masuk</p>
            </div>
            
            <form id="loginForm" onsubmit="event.preventDefault(); doLogin();">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" id="username" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none" required>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" id="password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none" required>
                </div>
                <button type="submit" id="btn-login" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition">Masuk Dashboard</button>
                <div id="login-error" class="text-red-500 text-sm text-center mt-3 hidden"></div>
            </form>
        </div>
    </div>
    
    <script>
        function doLogin() {
            const formData = new FormData();
            formData.append('username', document.getElementById('username').value);
            formData.append('password', document.getElementById('password').value);
            
            fetch('api.php?action=login', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') { window.location.reload(); } 
                else {
                    const err = document.getElementById('login-error');
                    err.textContent = data.message;
                    err.classList.remove('hidden');
                }
            });
        }
    </script>

<?php else: ?>
    <!-- ================= HALAMAN DASHBOARD ================= -->
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex h-full">
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center font-bold">RA</div>
                <span class="font-bold text-lg">Admin Panel</span>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="#" onclick="switchTab('data-individu')" id="nav-data-individu" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg transition-colors"><i class="fas fa-user w-5"></i> Pendaftar Individu</a>
                <a href="#" onclick="switchTab('data-kelompok')" id="nav-data-kelompok" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg transition-colors"><i class="fas fa-users w-5"></i> Pendaftar Kelompok</a>
                <div class="pt-4 mt-2 border-t border-slate-800">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase mb-2">Manajemen Konten</p>
                    <a href="#" onclick="switchTab('pengaturan')" id="nav-pengaturan" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg transition-colors"><i class="fas fa-image w-5"></i> Gambar & Logo</a>
                </div>
            </nav>
            <div class="p-4 border-t border-slate-800">
                <button onclick="doLogout()" class="flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-slate-800 rounded-lg w-full text-left transition-colors"><i class="fas fa-sign-out-alt w-5"></i> Keluar</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
            <div class="flex-1 overflow-y-auto p-4 md:p-8">
                
                <!-- DATA INDIVIDU -->
                <div id="section-data-individu" class="block">
                    <h1 class="text-2xl font-bold mb-6">Data Pendaftar Individu</h1>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-600 text-sm border-b">
                                        <th class="p-4">Tgl Daftar</th>
                                        <th class="p-4">Nama Lengkap</th>
                                        <th class="p-4">Sekolah & Tahun</th>
                                        <th class="p-4">No WA</th>
                                        <th class="p-4">Pendamping</th>
                                        <th class="p-4">Donasi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-individu" class="text-sm divide-y divide-slate-100">
                                    <tr><td colspan="6" class="p-4 text-center text-slate-500">Memuat data...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- DATA KELOMPOK -->
                <div id="section-data-kelompok" class="hidden">
                    <h1 class="text-2xl font-bold mb-6">Data Pendaftar Kelompok</h1>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-600 text-sm border-b">
                                        <th class="p-4">Tgl Daftar</th>
                                        <th class="p-4">Perwakilan</th>
                                        <th class="p-4">Asal & Angkatan</th>
                                        <th class="p-4">Jml Anggota</th>
                                        <th class="p-4">No WA</th>
                                        <th class="p-4">Daftar Anggota</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kelompok" class="text-sm divide-y divide-slate-100">
                                    <tr><td colspan="6" class="p-4 text-center text-slate-500">Memuat data...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PENGATURAN GAMBAR -->
                <div id="section-pengaturan" class="hidden">
                    <h1 class="text-2xl font-bold mb-6">Ubah Logo & Favicon</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Favicon -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                            <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Favicon Web</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img id="preview-favicon" src="https://placehold.co/32x32" class="w-12 h-12 rounded border object-cover">
                                <input type="file" accept="image/*" class="text-sm" onchange="convertToBase64(this, 'preview-favicon', 'favicon')">
                            </div>
                        </div>

                        <!-- Logo -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                            <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Logo Utama</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img id="preview-logo_utama" src="https://placehold.co/100x100" class="w-16 h-16 rounded-full border object-cover">
                                <input type="file" accept="image/*" class="text-sm" onchange="convertToBase64(this, 'preview-logo_utama', 'logo_utama')">
                            </div>
                        </div>

                        <!-- QRIS -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                            <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">QRIS Donasi</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img id="preview-qris_donasi" src="https://placehold.co/200x200" class="w-20 h-20 rounded border object-cover">
                                <input type="file" accept="image/*" class="text-sm" onchange="convertToBase64(this, 'preview-qris_donasi', 'qris_donasi')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script Logika Dashboard (Fetch Data & Upload) -->
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('main > div > div[id^="section-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById(`section-${tabId}`).classList.remove('hidden');
            document.getElementById(`section-${tabId}`).classList.add('block');

            document.querySelectorAll('aside nav a').forEach(el => {
                el.classList.remove('bg-blue-600', 'text-white');
                el.classList.add('text-slate-400');
            });
            document.getElementById(`nav-${tabId}`).classList.add('bg-blue-600', 'text-white');
        }

        function doLogout() {
            fetch('api.php?action=logout').then(() => window.location.reload());
        }

        // LOAD DATA PESERTA
        function loadData() {
            fetch('api.php?action=get_peserta')
            .then(res => res.json())
            .then(data => {
                const tbIndividu = document.getElementById('tbody-individu');
                const tbKelompok = document.getElementById('tbody-kelompok');
                
                // Render Individu
                tbIndividu.innerHTML = '';
                if(data.individu.length === 0) tbIndividu.innerHTML = '<tr><td colspan="6" class="p-4 text-center">Belum ada data</td></tr>';
                data.individu.forEach(row => {
                    tbIndividu.innerHTML += `
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 text-xs">${row.tanggal_daftar}</td>
                            <td class="p-4 font-medium">${row.nama_lengkap}</td>
                            <td class="p-4">${row.asal_sekolah} (${row.tahun_lulus})</td>
                            <td class="p-4">${row.no_wa}</td>
                            <td class="p-4">${row.jumlah_pendamping} Orang</td>
                            <td class="p-4">${row.berdonasi}</td>
                        </tr>`;
                });

                // Render Kelompok
                tbKelompok.innerHTML = '';
                if(data.kelompok.length === 0) tbKelompok.innerHTML = '<tr><td colspan="6" class="p-4 text-center">Belum ada data</td></tr>';
                data.kelompok.forEach(row => {
                    tbKelompok.innerHTML += `
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 text-xs">${row.tanggal_daftar}</td>
                            <td class="p-4 font-medium">${row.nama_perwakilan}</td>
                            <td class="p-4">${row.asal_sekolah} (${row.angkatan})</td>
                            <td class="p-4 font-bold text-blue-600">${row.jumlah_anggota}</td>
                            <td class="p-4">${row.no_wa_kelompok}</td>
                            <td class="p-4 whitespace-pre-wrap text-xs text-slate-500">${row.daftar_anggota}</td>
                        </tr>`;
                });
            });
        }

        // LOAD CURRENT IMAGES
        function loadImages() {
            fetch('api.php?action=get_pengaturan')
            .then(res => res.json())
            .then(data => {
                if(data.favicon) document.getElementById('preview-favicon').src = data.favicon;
                if(data.logo_utama) document.getElementById('preview-logo_utama').src = data.logo_utama;
                if(data.qris_donasi) document.getElementById('preview-qris_donasi').src = data.qris_donasi;
            });
        }

        // CONVERT & UPLOAD BASE64 AUTO-SAVE
        function convertToBase64(fileInput, previewId, dbKey) {
            const file = fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const base64String = e.target.result;
                    document.getElementById(previewId).src = base64String;
                    
                    // Langsung simpan ke Database via API
                    fetch('api.php?action=simpan_pengaturan', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ kunci: dbKey, nilai: base64String })
                    }).then(res => res.json()).then(res => {
                        if(res.status === 'success') alert('Gambar berhasil diperbarui!');
                    });
                };
                reader.readAsDataURL(file);
            }
        }

        // Panggil saat halaman dimuat
        loadData();
        loadImages();
    </script>
<?php endif; ?>
</body>
</html>
