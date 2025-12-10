<?php
require_once 'config/config.php';
requireRole(['admin']);

require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $user->username = sanitizeInput($_POST['username']);
                $user->password = $_POST['password'];
                $user->nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
                $user->email = sanitizeInput($_POST['email']);
                $user->role = sanitizeInput($_POST['role']);

                if ($user->create()) {
                    $message = 'User berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan user!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $user->id = sanitizeInput($_POST['id']);
                $user->username = sanitizeInput($_POST['username']);
                $user->nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
                $user->email = sanitizeInput($_POST['email']);
                $user->role = sanitizeInput($_POST['role']);

                if ($user->update()) {
                    $message = 'User berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui user!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $user->id = sanitizeInput($_POST['id']);
                if ($user->delete()) {
                    $message = 'User berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus user!';
                    $message_type = 'error';
                }
                break;

            case 'change_password':
                $user->id = sanitizeInput($_POST['id']);
                $new_password = $_POST['new_password'];
                
                if ($user->changePassword($new_password)) {
                    $message = 'Password berhasil diubah!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengubah password!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all users
$stmt = $user->readAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="./src/output.css">
    <style>
        body {
            background-color: #000000;
            margin: 0;
            padding: 0;
        }
        .fade-in {
            animation: fadeIn .6s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dashboard-container {
            background: #ffffff;
            border: 2px solid #d4af37;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
            margin: 20px;
            min-height: calc(100vh - 40px);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #d4af37;
            font-size: 14px;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #d0d0d0;
            border-radius: 8px;
            font-size: 15px;
            background: white;
            color: #000000;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-danger {
            background: rgba(139, 0, 0, 0.2);
            color: #8b0000;
        }
        .badge-success {
            background: rgba(212, 175, 55, 0.15);
            color: #d4af37;
        }
        .badge-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #856404;
        }
    </style>
</head>
<body class="bg-black">
    <!-- Main Container (White with Gold Border) -->
    <div class="dashboard-container flex">
        <!-- Sidebar -->
        <?php 
        $page = "users";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-scroll bg-white">
            <!-- Top Navigation -->
            <header class="flex justify-between items-center mb-10 fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-black mb-2">Manajemen User</h1>
                    <p class="text-gray-600">Kelola user dengan tampilan elegan ✨</p>
                </div>

                <div class="flex items-center gap-4 p-3 pr-5 rounded-xl">
                    <div class="text-right">
                        <div class="font-semibold text-black"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="text-gray-600 text-sm"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; box-shadow: 0 2px 8px rgba(212, 175, 55, 0.3);">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content" style="background: transparent;">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 20px; padding: 15px; border-radius: 8px; border-left: 4px solid; <?php echo $message_type === 'error' ? 'background: rgba(139, 0, 0, 0.2); border-color: #8b0000; color: #ff6b6b;' : 'background: rgba(212, 175, 55, 0.15); border-color: #d4af37; color: #d4af37;'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add User Form -->
                <div class="form-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <h2 style="color: #d4af37; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Tambah User Baru</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="kasir">Kasir</option>
                                    <option value="gudang">Gudang</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; margin-top: 15px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Tambah User</button>
                    </form>
                </div>

                <!-- Data User Table -->
                <div class="table-container" style="background: #ffffff; border: 2px solid #d4af37; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    <div class="table-header" style="background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); padding: 20px;">
                        <h3 class="table-title" style="color: #000000; font-size: 20px; font-weight: bold; margin: 0;">Daftar User</h3>
                    </div>
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(212, 175, 55, 0.1);">
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">ID</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Username</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Nama Lengkap</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Email</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Role</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Tanggal Dibuat</th>
                                <th style="padding: 15px; text-align: left; color: #d4af37; font-weight: 600; border-bottom: 2px solid #d4af37;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr style="border-b: 1px solid #e0e0e0; transition: background 0.2s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#ffffff'">
                                <td style="padding: 15px; color: #000000;"><?php echo $row['id']; ?></td>
                                <td style="padding: 15px; color: #000000; font-weight: 500;"><?php echo $row['username']; ?></td>
                                <td style="padding: 15px; color: #000000; font-weight: 500;"><?php echo $row['nama_lengkap']; ?></td>
                                <td style="padding: 15px; color: #000000;"><?php echo $row['email'] ?: '-'; ?></td>
                                <td style="padding: 15px;">
                                    <span class="badge <?php 
                                        echo $row['role'] === 'admin' ? 'badge-danger' : 
                                            ($row['role'] === 'kasir' ? 'badge-success' : 'badge-warning'); 
                                    ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #000000;"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td style="padding: 15px;">
                                    <button onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                            style="padding: 8px 16px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-right: 5px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Edit</button>
                                    <button onclick="changePassword(<?php echo $row['id']; ?>, '<?php echo $row['username']; ?>')" 
                                            style="padding: 8px 16px; background: linear-gradient(135deg, #3498db, #2980b9); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-right: 5px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(52, 152, 219, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Password</button>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="deleteUser(<?php echo $row['id']; ?>)" 
                                            style="padding: 8px 16px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: #ffffff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(231, 76, 60, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Hapus</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; border: 2px solid #d4af37; width: 90%; max-width: 600px; max-height: 90%; overflow-y: auto; box-shadow: 0 8px 30px rgba(212, 175, 55, 0.3);">
            <h2 style="color: #d4af37; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Edit User</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                            <option value="gudang">Gudang</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Update</button>
                    <button type="button" onclick="closeEditModal()" style="padding: 12px 24px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#d0d0d0'" onmouseout="this.style.background='#e0e0e0'">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; border: 2px solid #d4af37; width: 90%; max-width: 400px; box-shadow: 0 8px 30px rgba(212, 175, 55, 0.3);">
            <h2 style="color: #d4af37; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">Ubah Password</h2>
            <form method="POST" id="passwordForm">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="id" id="password_user_id">
                
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" id="password_username" readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" style="padding: 12px 24px; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(212, 175, 55, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">Ubah Password</button>
                    <button type="button" onclick="closePasswordModal()" style="padding: 12px 24px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#d0d0d0'" onmouseout="this.style.background='#e0e0e0'">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_nama_lengkap').value = data.nama_lengkap;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_role').value = data.role;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function changePassword(id, username) {
            document.getElementById('password_user_id').value = id;
            document.getElementById('password_username').value = username;
            document.getElementById('new_password').value = '';
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        function deleteUser(id) {
            if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').onclick = function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        }

        document.getElementById('passwordModal').onclick = function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        }
    </script>
</body>
</html>
