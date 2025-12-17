<?php
require_once 'config/config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $user = new User($db);
        
        if ($user->login($username, $password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['nama_lengkap'] = $user->nama_lengkap;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['email'] = $user->email;
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = 'Username atau password salah!';
        }
    } else {
        $error_message = 'Username dan password harus diisi!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="./src/output.css" />
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
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float {
            animation: float 3s ease-in-out infinite;
        }
        .gradient-bg {
            background: #000000;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 30%, rgba(212, 175, 55, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(212, 175, 55, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .login-container {
            background: #ffffff;
            border: 2px solid #d4af37;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
    </style>
</head>
<body class="gradient-bg">
    <!-- Background Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-32 h-32 rounded-full blur-3xl float" style="background: rgba(212, 175, 55, 0.1);"></div>
        <div class="absolute bottom-20 right-10 w-40 h-40 rounded-full blur-3xl float" style="background: rgba(212, 175, 55, 0.1); animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-36 h-36 rounded-full blur-3xl float" style="background: rgba(212, 175, 55, 0.05); animation-delay: 2s;"></div>
    </div>

    <div class="login-container fade-in">
        <!-- Header -->
        <div style="padding: 1rem 2rem 1.5rem; text-align: center; border-bottom: 2px solid #d4af37;">
            <div class="bg-red-9 w-full flex justify-end">
                <a href="homepage2.html" class="text-3xl">X</a>
            </div>
            <h1 style="font-size: 1.875rem; font-weight: bold; color: #000000; margin-bottom: 0.5rem;">
                <?php echo APP_NAME; ?>
            </h1>
            <p style="color: #666;">Silakan masuk dengan akun Anda</p>
        </div>

        <!-- Error Message -->
        <?php if ($error_message): ?>
            <div style="margin: 1.5rem 2rem 0;">
                <div style="background: rgba(139, 0, 0, 0.2); border: 1px solid #8b0000; color: #ff6b6b; padding: 0.75rem 1rem; border-radius: 8px;">
                    <?php echo $error_message; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" style="padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="username" style="display: block; font-size: 0.875rem; font-weight: 600; color: #d4af37;">
                    Username
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #d0d0d0; border-radius: 8px; background: white; color: #000000; font-size: 15px; transition: all 0.3s; box-sizing: border-box;"
                    placeholder="Masukkan username"
                    onfocus="this.style.borderColor='#d4af37'; this.style.boxShadow='0 4px 12px rgba(212, 175, 55, 0.3)';"
                    onblur="this.style.borderColor='#d0d0d0'; this.style.boxShadow='none';"
                >
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; color: #d4af37;">
                    Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #d0d0d0; border-radius: 8px; background: white; color: #000000; font-size: 15px; transition: all 0.3s; box-sizing: border-box;"
                    placeholder="Masukkan password"
                    onfocus="this.style.borderColor='#d4af37'; this.style.boxShadow='0 4px 12px rgba(212, 175, 55, 0.3)';"
                    onblur="this.style.borderColor='#d0d0d0'; this.style.boxShadow='none';"
                >
            </div>

            <button 
                type="submit" 
                style="width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%); color: #000000; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; transition: all 0.3s; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(212, 175, 55, 0.5)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(212, 175, 55, 0.4)';"
            >
                Masuk
            </button>
        </form>

</body>
</html>
