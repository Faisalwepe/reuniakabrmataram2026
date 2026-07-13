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
    <style>body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }</style>
</head>
<body class="text-slate-800">

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <!-- ================= HALAMAN LOGIN ================= -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">RA</div>
                <h1 class="text-2xl font-bold text-slate-800">Admin Login</h1>
                <p class="text-slate-500 text-sm">Masuk untuk mengelola data reuni</p>
            </div>
            <form onsubmit="event.preventDefault(); doLogin();">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" id="username" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none" required>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" id="password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition">Masuk Dashboard</button>
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
            .then(res => res.json()).then(data => {
                if(data.status === 'success') { window.location.reload(); } 
                else {
                    const err = document.getElementById('login-error');
                    err.textContent = data.message; err.classList.remove('hidden');
                }
            });
        }
    </script>

<?php else: ?>
    <!-- ================= HALAMAN DASHBOARD ================= -->
    <?php $role = $_SESSION['admin_role']; ?>
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex h-full">
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center font-bold">RA</div>
                <div>
                    <span class="font-bold text-lg block leading-tight">Admin Panel</span>
                    <span class="text-xs text-blue-400 capitalize"><?= $role ?></span>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="#" onclick="switchTab('dashboard')" id="nav-dashboard" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg transition-colors"><i class="fas fa-home w-5"></i> Ringkasan Total</a>
                <a href="#" onclick="switchTab('data-individu')" id="nav-data-individu" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg transition-colors"><i class="fas fa-user w-5"></i> Pendaftar Individu</a>
                <a href="#" onclick="switchTab('data-kelompok')" id="nav-data-kelompok" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg transition-colors"><i class="fas fa-users w-5"></i> Pendaftar Kelompok</a>
                
                <div class="pt-4 mt-2 border-t border-slate-800">
                    <p class="px-4 text-xs font-semibold text-slate-500 uppercase mb-2">Manajemen</p>
                    
                    <?php if ($role === 'administrator'): ?>
                        <a href="#" onclick="switchTab('pengaturan')" id="nav-pengaturan" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg transition-colors"><i class="fas fa-image w-5"></i> Konten & Gambar</a>
                        <a href="#" onclick="switchTab('users')" id="nav-users" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg transition-colors"><i class="fas fa-user-cog w-5"></i> Akun User</a>
                    <?php else: ?>
                        <a href="#" onclick="switchTab('profil')" id="nav-profil" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg transition-colors"><i class="fas fa-id-badge w-5"></i> Profil Saya</a>
                    <?php endif; ?>
                </div>
            </nav>
            <div class="p-4 border-t border-slate-800">
                <button onclick="doLogout()" class="flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-slate-800 rounded-lg w-full text-left transition-colors"><i class="fas fa-sign-out-alt w-5"></i> Keluar</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
            <div class="flex-1 overflow-y-auto p-4 md:p-8">
                
                <!-- 1. DASHBOARD RINGKASAN -->
                <div id="section-dashboard" class="block">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Ringkasan Kehadiran</h1>
                        <button onclick="exportCSV('semua')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2 shadow-sm"><i class="fas fa-file-csv"></i> Ekspor Semua (CSV)</button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Card Total Real -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex items-center gap-4 col-span-1 md:col-span-3 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-3xl"><i class="fas fa-users"></i></div>
                            <div>
                                <p class="text-blue-100 font-medium">Total Seluruh Orang Yang Akan Hadir</p>
                                <p class="text-4xl font-bold" id="total-semua-orang">0 <span class="text-lg font-normal">Orang</span></p>
                                <p class="text-xs text-blue-200 mt-1">Gabungan pendaftar, pendamping, & anggota kelompok</p>
                            </div>
                        </div>

                        <!-- Card Individu -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl"><i class="fas fa-user"></i></div>
                                <div><p class="text-slate-500 text-sm font-medium">Jalur Individu</p><p class="text-2xl font-bold text-slate-800" id="total-form-individu">0 <span class="text-sm font-normal text-slate-500">Form</span></p></div>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-100 text-sm text-slate-600">
                                Total Hadir: <strong id="total-orang-individu" class="text-emerald-600">0 Orang</strong> <br> <span class="text-xs text-slate-400">(Termasuk Pendamping)</span>
                            </div>
                        </div>

                        <!-- Card Kelompok -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xl"><i class="fas fa-users-cog"></i></div>
                                <div><p class="text-slate-500 text-sm font-medium">Jalur Kelompok</p><p class="text-2xl font-bold text-slate-800" id="total-form-kelompok">0 <span class="text-sm font-normal text-slate-500">Form</span></p></div>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-100 text-sm text-slate-600">
                                Total Hadir: <strong id="total-orang-kelompok" class="text-amber-600">0 Orang</strong> <br> <span class="text-xs text-slate-400">(Termasuk Anggota)</span>
                            </div>
                        </div>
                        
                        <!-- Card Donasi -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl"><i class="fas fa-hand-holding-heart"></i></div>
                                <div><p class="text-slate-500 text-sm font-medium">Total Donatur</p><p class="text-2xl font-bold text-slate-800" id="total-donatur">0 <span class="text-sm font-normal text-slate-500">Pihak</span></p></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. DATA INDIVIDU -->
                <div id="section-data-individu" class="hidden">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h1 class="text-2xl font-bold">Data Individu</h1>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div class="relative flex-1 md:w-64">
                                <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                                <input type="text" onkeyup="searchTable('search-individu', 'tbody-individu')" id="search-individu" placeholder="Cari nama..." class="w-full pl-10 pr-4 py-2 border rounded-lg outline-none focus:border-blue-500 text-sm">
                            </div>
                            <button onclick="exportCSV('individu')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 flex-shrink-0"><i class="fas fa-file-excel mr-1"></i> CSV</button>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-600 text-sm border-b whitespace-nowrap">
                                        <th class="p-4">Tanggal</th>
                                        <th class="p-4">Nama Lengkap</th>
                                        <th class="p-4">Sekolah & Tahun</th>
                                        <th class="p-4">No WA</th>
                                        <th class="p-4">Pendamping</th>
                                        <th class="p-4">Donasi</th>
                                        <th class="p-4">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-individu" class="text-sm divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 3. DATA KELOMPOK -->
                <div id="section-data-kelompok" class="hidden">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h1 class="text-2xl font-bold">Data Kelompok</h1>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div class="relative flex-1 md:w-64">
                                <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                                <input type="text" onkeyup="searchTable('search-kelompok', 'tbody-kelompok')" id="search-kelompok" placeholder="Cari perwakilan..." class="w-full pl-10 pr-4 py-2 border rounded-lg outline-none focus:border-blue-500 text-sm">
                            </div>
                            <button onclick="exportCSV('kelompok')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 flex-shrink-0"><i class="fas fa-file-excel mr-1"></i> CSV</button>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-600 text-sm border-b whitespace-nowrap">
                                        <th class="p-4">Tanggal</th>
                                        <th class="p-4">Nama Perwakilan</th>
                                        <th class="p-4">Asal & Angkatan</th>
                                        <th class="p-4">Jml Anggota</th>
                                        <th class="p-4">No WA</th>
                                        <th class="p-4">Donasi</th>
                                        <th class="p-4">Nominal</th>
                                        <th class="p-4 min-w-[200px]">Daftar Nama</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kelompok" class="text-sm divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($role === 'administrator'): ?>
                <!-- 4. PENGATURAN KONTEN -->
                <div id="section-pengaturan" class="hidden">
                    <h1 class="text-2xl font-bold mb-6">Manajemen Konten & Gambar</h1>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                            <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Favicon Web</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img id="preview-favicon" src="https://placehold.co/32x32" class="w-12 h-12 rounded border object-cover">
                                <input type="file" accept="image/*" class="text-xs w-full" onchange="convertToBase64(this, 'preview-favicon', 'favicon')">
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                            <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">Logo Utama Navbar</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img id="preview-logo_utama" src="https://placehold.co/100x100" class="w-16 h-16 rounded-full border object-cover">
                                <input type="file" accept="image/*" class="text-xs w-full" onchange="convertToBase64(this, 'preview-logo_utama', 'logo_utama')">
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                            <h3 class="font-bold text-slate-800 mb-4 border-b pb-2">QRIS Donasi</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <img id="preview-qris_donasi" src="https://placehold.co/200x200" class="w-20 h-20 rounded border object-cover">
                                <input type="file" accept="image/*" class="text-xs w-full" onchange="convertToBase64(this, 'preview-qris_donasi', 'qris_donasi')">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. MANAJEMEN USER -->
                <div id="section-users" class="hidden">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Manajemen User (Admin)</h1>
                        <button onclick="openUserModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700"><i class="fas fa-plus mr-1"></i> Tambah User</button>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-600 text-sm border-b">
                                    <th class="p-4">Username</th>
                                    <th class="p-4">Role</th>
                                    <th class="p-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-users" class="text-sm divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                    
                    <!-- Modal Form User -->
                    <div id="user-modal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50">
                        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 m-4">
                            <h3 class="text-xl font-bold mb-4" id="modal-title">Tambah User</h3>
                            <form id="form-user" onsubmit="event.preventDefault(); saveUser();">
                                <input type="hidden" id="u-id" value="0">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                                    <input type="text" id="u-username" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                                    <select id="u-role" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none">
                                        <option value="admin">Admin (Hanya lihat data)</option>
                                        <option value="administrator">Administrator (Full Akses)</option>
                                    </select>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-xs text-slate-400 font-normal" id="pw-hint">(Kosongkan jika tidak ingin diubah)</span></label>
                                    <input type="password" id="u-password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none">
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="document.getElementById('user-modal').classList.add('hidden')" class="px-4 py-2 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">Batal</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- 6. PROFIL SAYA (UNTUK ADMIN BIASA) -->
                <div id="section-profil" class="hidden">
                    <h1 class="text-2xl font-bold mb-6">Profil Saya</h1>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 max-w-md">
                        <form onsubmit="event.preventDefault(); updateMyPassword();">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                                <input type="text" value="<?= $_SESSION['admin_username'] ?>" disabled class="w-full px-4 py-2 bg-slate-50 border rounded-lg text-slate-500">
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                                <input type="password" id="my-new-password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 outline-none">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition">Update Password</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <!-- SCRIPT DATA & FUNGSI APLIKASI -->
    <script>
        let dataGlobal = { individu: [], kelompok: [] };
        const isAdmin = <?= ($role === 'administrator') ? 'true' : 'false' ?>;

        function switchTab(tabId) {
            document.querySelectorAll('main > div > div[id^="section-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById(`section-${tabId}`).classList.remove('hidden');
            document.getElementById(`section-${tabId}`).classList.add('block');
            document.querySelectorAll('aside nav a').forEach(el => {
                el.classList.remove('bg-blue-600', 'text-white'); el.classList.add('text-slate-400');
            });
            document.getElementById(`nav-${tabId}`).classList.add('bg-blue-600', 'text-white');
        }

        function doLogout() { fetch('api.php?action=logout').then(() => window.location.reload()); }

        // --- FETCH DATA UTAMA ---
        function loadData() {
            fetch('api.php?action=get_peserta').then(res => res.json()).then(data => {
                dataGlobal = data;
                renderTables(data);
                calculateTotals(data);
            });
            if(isAdmin) loadUsers();
            loadImages();
        }

        // --- KALKULASI DASHBOARD ---
        function calculateTotals(data) {
            let formIndividu = data.individu.length;
            let formKelompok = data.kelompok.length;
            let orangIndividu = 0, orangKelompok = 0, donatur = 0;

            data.individu.forEach(r => {
                orangIndividu += (1 + parseInt(r.jumlah_pendamping || 0));
                if(r.berdonasi.includes("Ya")) donatur++;
            });
            
            data.kelompok.forEach(r => {
                orangKelompok += parseInt(r.jumlah_anggota || 0);
                if(r.berdonasi.includes("Ya")) donatur++;
            });

            document.getElementById('total-form-individu').innerHTML = `${formIndividu} <span class="text-sm font-normal text-slate-500">Form</span>`;
            document.getElementById('total-orang-individu').innerText = `${orangIndividu} Orang`;
            
            document.getElementById('total-form-kelompok').innerHTML = `${formKelompok} <span class="text-sm font-normal text-slate-500">Form</span>`;
            document.getElementById('total-orang-kelompok').innerText = `${orangKelompok} Orang`;

            document.getElementById('total-semua-orang').innerHTML = `${orangIndividu + orangKelompok} <span class="text-lg font-normal">Orang</span>`;
            document.getElementById('total-donatur').innerHTML = `${donatur} <span class="text-sm font-normal text-slate-500">Pihak</span>`;
        }

        // --- RENDER TABEL ---
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        }

        function renderTables(data) {
            const tbIndividu = document.getElementById('tbody-individu');
            tbIndividu.innerHTML = data.individu.map(r => `
                <tr class="hover:bg-slate-50">
                    <td class="p-4 text-xs whitespace-nowrap">${r.tanggal_daftar}</td>
                    <td class="p-4 font-medium">${r.nama_lengkap}</td>
                    <td class="p-4">${r.asal_sekolah} (${r.tahun_lulus})</td>
                    <td class="p-4">${r.no_wa}</td>
                    <td class="p-4">${r.jumlah_pendamping} Pendamping</td>
                    <td class="p-4"><span class="px-2 py-1 rounded text-xs font-semibold ${r.berdonasi.includes('Ya') ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}">${r.berdonasi}</span></td>
                    <td class="p-4 font-semibold text-slate-700">${r.berdonasi.includes('Ya') ? formatRupiah(r.nominal_donasi) : '-'}</td>
                </tr>
            `).join('') || '<tr><td colspan="7" class="p-4 text-center">Belum ada data</td></tr>';

            const tbKelompok = document.getElementById('tbody-kelompok');
            tbKelompok.innerHTML = data.kelompok.map(r => `
                <tr class="hover:bg-slate-50">
                    <td class="p-4 text-xs whitespace-nowrap">${r.tanggal_daftar}</td>
                    <td class="p-4 font-medium">${r.nama_perwakilan}</td>
                    <td class="p-4">${r.asal_sekolah} (${r.angkatan})</td>
                    <td class="p-4 font-bold text-blue-600">${r.jumlah_anggota} Org</td>
                    <td class="p-4">${r.no_wa_kelompok}</td>
                    <td class="p-4"><span class="px-2 py-1 rounded text-xs font-semibold ${r.berdonasi.includes('Ya') ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}">${r.berdonasi}</span></td>
                    <td class="p-4 font-semibold text-slate-700">${r.berdonasi.includes('Ya') ? formatRupiah(r.nominal_donasi) : '-'}</td>
                    <td class="p-4 text-xs text-slate-500 whitespace-pre-wrap min-w-[200px]">${r.daftar_anggota}</td>
                </tr>
            `).join('') || '<tr><td colspan="8" class="p-4 text-center">Belum ada data</td></tr>';
        }

        // --- PENCARIAN (LIVE SEARCH) ---
        function searchTable(inputId, tbodyId) {
            const input = document.getElementById(inputId).value.toLowerCase();
            const rows = document.getElementById(tbodyId).getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                // Asumsi nama selalu ada di kolom ke-2 (index 1)
                const nameCol = rows[i].getElementsByTagName('td')[1]; 
                if (nameCol) {
                    const txtValue = nameCol.textContent || nameCol.innerText;
                    rows[i].style.display = txtValue.toLowerCase().indexOf(input) > -1 ? "" : "none";
                }
            }
        }

        // --- EXPORT CSV ---
        function exportCSV(tipe) {
            let csv = [];
            
            if (tipe === 'individu' || tipe === 'semua') {
                if(tipe === 'semua') csv.push("--- JALUR INDIVIDU ---");
                csv.push("Tanggal,Tipe,Nama Lengkap,Asal Sekolah & Tahun,No WA,Jml Pendamping,Total Orang Hadir,Donasi,Nominal Donasi");
                dataGlobal.individu.forEach(r => {
                    let totalHadir = 1 + parseInt(r.jumlah_pendamping || 0);
                    csv.push(`"${r.tanggal_daftar}","Individu","${r.nama_lengkap}","${r.asal_sekolah} (${r.tahun_lulus})","'${r.no_wa}","${r.jumlah_pendamping}","${totalHadir}","${r.berdonasi}","${r.nominal_donasi}"`);
                });
            }

            if (tipe === 'kelompok' || tipe === 'semua') {
                if(tipe === 'semua') { csv.push(""); csv.push("--- JALUR KELOMPOK ---"); }
                csv.push("Tanggal,Tipe,Nama Perwakilan,Asal Sekolah & Angkatan,No WA Perwakilan,Jml Anggota (Total Hadir),Daftar Anggota,Donasi,Nominal Donasi");
                dataGlobal.kelompok.forEach(r => {
                    // Clean newlines for CSV string
                    let cleanDaftar = r.daftar_anggota.replace(/(\r\n|\n|\r)/gm, " | ");
                    csv.push(`"${r.tanggal_daftar}","Kelompok","${r.nama_perwakilan}","${r.asal_sekolah} (${r.angkatan})","'${r.no_wa_kelompok}","${r.jumlah_anggota}","${cleanDaftar}","${r.berdonasi}","${r.nominal_donasi}"`);
                });
            }

            let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
            let encodedUri = encodeURI(csvContent);
            let link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `Data_Reuni_${tipe}_${new Date().getTime()}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // --- MANAJEMEN USER (ADMINISTRATOR ONLY) ---
        function loadUsers() {
            fetch('api.php?action=get_users').then(res => res.json()).then(data => {
                document.getElementById('tbody-users').innerHTML = data.map(u => `
                    <tr>
                        <td class="p-4 font-medium">${u.username} ${u.username === 'admin' ? '<span class="ml-2 text-xs bg-red-100 text-red-600 px-2 py-1 rounded">SuperUser</span>' : ''}</td>
                        <td class="p-4 capitalize">${u.role}</td>
                        <td class="p-4 text-right">
                            <button onclick="editUser(${u.id}, '${u.username}', '${u.role}')" class="text-blue-600 hover:underline mr-3 text-sm">Edit</button>
                            ${u.username !== 'admin' ? `<button onclick="deleteUser(${u.id})" class="text-red-600 hover:underline text-sm">Hapus</button>` : ''}
                        </td>
                    </tr>
                `).join('');
            });
        }
        
        function openUserModal() {
            document.getElementById('u-id').value = 0;
            document.getElementById('form-user').reset();
            document.getElementById('u-username').disabled = false;
            document.getElementById('pw-hint').innerText = '(Wajib diisi)';
            document.getElementById('u-password').required = true;
            document.getElementById('modal-title').innerText = "Tambah User Baru";
            document.getElementById('user-modal').classList.remove('hidden');
        }

        function editUser(id, username, role) {
            document.getElementById('u-id').value = id;
            document.getElementById('u-username').value = username;
            document.getElementById('u-username').disabled = (username === 'admin'); // Jaga agar username master tidak diubah
            document.getElementById('u-role').value = role;
            document.getElementById('u-password').value = '';
            document.getElementById('u-password').required = false;
            document.getElementById('pw-hint').innerText = '(Kosongkan jika tidak ingin ubah password)';
            document.getElementById('modal-title').innerText = "Edit User";
            document.getElementById('user-modal').classList.remove('hidden');
        }

        function saveUser() {
            fetch('api.php?action=save_user', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: document.getElementById('u-id').value,
                    username: document.getElementById('u-username').value,
                    role: document.getElementById('u-role').value,
                    password: document.getElementById('u-password').value
                })
            }).then(res => res.json()).then(res => {
                if(res.status === 'success') {
                    document.getElementById('user-modal').classList.add('hidden');
                    loadUsers();
                } else alert(res.message);
            });
        }

        function deleteUser(id) {
            if(confirm('Yakin ingin menghapus user ini?')) {
                fetch('api.php?action=delete_user', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                }).then(() => loadUsers());
            }
        }

        function updateMyPassword() {
            fetch('api.php?action=update_profile', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: document.getElementById('my-new-password').value })
            }).then(res => res.json()).then(res => {
                if(res.status === 'success') {
                    alert('Password berhasil diupdate! Silakan login kembali.');
                    doLogout();
                }
            });
        }

        // --- MANAJEMEN KONTEN GAMBAR ---
        function loadImages() {
            if(!isAdmin) return;
            fetch('api.php?action=get_pengaturan').then(res => res.json()).then(data => {
                if(data.favicon) document.getElementById('preview-favicon').src = data.favicon;
                if(data.logo_utama) document.getElementById('preview-logo_utama').src = data.logo_utama;
                if(data.qris_donasi) document.getElementById('preview-qris_donasi').src = data.qris_donasi;
            });
        }

        function convertToBase64(fileInput, previewId, dbKey) {
            const file = fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const base64String = e.target.result;
                    document.getElementById(previewId).src = base64String;
                    fetch('api.php?action=simpan_pengaturan', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ kunci: dbKey, nilai: base64String })
                    }).then(res => res.json()).then(res => {
                        if(res.status === 'success') alert('Gambar berhasil diperbarui!');
                    });
                };
                reader.readAsDataURL(file);
            }
        }

        // INIT APP
        loadData();
    </script>
<?php endif; ?>
</body>
</html>
