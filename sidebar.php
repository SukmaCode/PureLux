<?php
    // Deteksi halaman saat ini
    $current_page = basename($_SERVER['PHP_SELF']);

    function isActive($page) {
        return basename($_SERVER['PHP_SELF']) === $page 
            ? "bg-white/20 text-white shadow-md" 
            : "hover:bg-white/10 text-white/90 hover:text-white";
    }

?>

</head>
<link rel="stylesheet" href="./src/output.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        /* Sidebar Hover Animation */
        .sidebar {
            width: 80px;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }
        .sidebar-logo {
            width: 50px;
            height: 48px;
            flex-shrink: 0;
        }

        .sidebar:hover {
            width: 250px;
        }
        
        /* Text visibility */
        .sidebar-text {
            opacity: 0;
            white-space: nowrap;
            transition: opacity 0.4s ease 0.1s, transform 0.3s ease;
            transform: translateX(-10px);
            pointer-events: none;
        }
        .sidebar:hover .sidebar-text {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }
        
        /* Nav link hover */
        .nav-link {
            transition: all 0.3s ease;
            justify-content: flex-start;
        }
        .nav-link:hover {
            transform: translateX(3px);
        }
        
        /* Section header visibility */
        .section-header {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease 0.15s;
            transform: translateX(-10px);
        }
        .sidebar:hover .section-header {
            opacity: 1;
            max-height: 50px;
            transform: translateX(0);
        }
        
        /* Icon always visible */
        .nav-icon {
            min-width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
        
        /* Active state */
        .nav-link.active {
            background: rgba(212, 175, 55, 0.15);
            border-left: 3px solid #d4af37;
        }
        
        .nav-link.active .nav-icon {
            filter: brightness(0) saturate(100%) invert(70%) sepia(50%) saturate(500%) hue-rotate(10deg) brightness(0.9);
        }
        
        .nav-link.active .sidebar-text {
            color: #d4af37;
            font-weight: 600;
        }
        
        /* Smooth transitions */
        .sidebar * {
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body>
    <div class="min-h-screen hidden md:flex">
        <!-- Sidebar -->
        <aside class="sidebar b g-white text-black shadow-lg overflow-y-auto flex-shrink-0 border-r-2" style="border-right-color: #d4af37;">
            <!-- Sidebar Header -->
            <div class="p-4 border-b-2 flex items-center gap-8" style="border-bottom-color: #d4af37;">
                <div class="rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-lg font-bold shadow-lg sidebar-logo" style="color: #000000;">
                    PL
                </div>
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold sidebar-text" style="color: #d4af37;">PureLux</h2>
                </div>
            </div>
            
            <!-- Sidebar Navigation -->
            <nav class="p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <!-- Dashboard -->
                    <li>
                        <a href="dashboard.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 <?= ($page == 'dashboard') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl flex items-center justify-center">
                                <img src="./assets/icon/dashboard.svg" alt="" class="w-6 fill-red-500">
                            </span>
                            <span class="sidebar-text font-medium">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Penjualan Section (Admin & Kasir) -->
                    <li class="mt-4 section-header">
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider" style="color: #d4af37;">
                            Penjualan
                        </div>
                    </li>
                    <li>
                        <a href="penjualan.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700  <?= ($page == 'penjualan') ? 'active' : 'text-gray-700 hover:text-black' ?> hover:text-black">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/penjualan.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Penjualan</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan_penjualan.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'laporan_penjualan') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/laporan_penjualan.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Laporan Penjualan</span>
                        </a>
                    </li>
                    
                    <!-- Inventory Section (Admin & Gudang) -->
                    <li class="mt-4 section-header">
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider" style="color: #d4af37;">
                            Inventory
                        </div>
                    </li>
                    <li>
                        <a href="parfum.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'parfum') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/parfume.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Parfum</span>
                        </a>
                    </li>
                    <li>
                        <a href="mix_parfum.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 <?= ($page == 'mix_parfum') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/mix-perfume.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Mix de'Parfum</span>
                        </a>
                    </li>
                    <li>
                        <a href="pembelian.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'pembelian') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/pembelian.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan_pembelian.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'laporan_pembelian') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/laporan_pembelian.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Laporan Pembelian</span>
                        </a>
                    </li>
                    <li>
                        <a href="stok.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'manajemen_stok') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/manajemen_stok.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Manajemen Stok</span>
                        </a>
                    </li>
                    
                    <!-- Master Data Section (Admin Only) -->
                    <li class="mt-4 section-header">
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider" style="color: #d4af37;">
                            Master Data
                        </div>
                    </li>
                    <li>
                        <a href="kategori.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'kategori_parfum') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/kategori.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Kategori Parfum</span>
                        </a>
                    </li>
                    <li>
                        <a href="vendor.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'vendor') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/vendor.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Vendor</span>
                        </a>
                    </li>
                    <li>
                        <a href="customer.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'customer') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/customer.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Customer</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'manajemen_user') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/user.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Manajemen User</span>
                        </a>
                    </li>
                    
                    <!-- System Section -->
                    <li class="mt-4 section-header">
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider" style="color: #d4af37;">
                            System
                        </div>
                    </li>
                    <li>
                        <a href="pengaturan.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-yellow-50 text-gray-700 hover:text-black <?= ($page == 'pengaturan') ? 'active' : 'text-gray-700 hover:text-black' ?>">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/setting.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Pengaturan</span>
                        </a>
                    </li>
                    <li class="mt-4 pt-4 border-t-2" style="border-top-color: #d4af37;">
                        <a href="logout.php" class="nav-link flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-300 hover:bg-red-50 text-gray-700 hover:text-red-600">
                            <span class="nav-icon text-xl">
                                <img src="./assets/icon/logout.svg" alt="" class="w-6">
                            </span>
                            <span class="sidebar-text font-medium">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
    </div>
</body>