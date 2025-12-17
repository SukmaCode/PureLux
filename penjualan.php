<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);

require_once 'models/Parfum.php';
require_once 'models/Penjualan.php';
require_once 'models/Customer.php';
require_once 'models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();

// Auto-fix: Tambahkan kolom yang hilang jika belum ada
try {
    // Cek dan tambah kolom metode_transaksi jika belum ada
    $check_query = "SHOW COLUMNS FROM penjualan LIKE 'metode_transaksi'";
    $stmt = $db->prepare($check_query);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE penjualan ADD COLUMN metode_transaksi VARCHAR(20) DEFAULT 'cash' AFTER note");
    }
    
    // Cek dan tambah kolom nomor_rekening jika belum ada
    $check_query2 = "SHOW COLUMNS FROM penjualan LIKE 'nomor_rekening'";
    $stmt2 = $db->prepare($check_query2);
    $stmt2->execute();
    if ($stmt2->rowCount() == 0) {
        $db->exec("ALTER TABLE penjualan ADD COLUMN nomor_rekening VARCHAR(50) NULL AFTER metode_transaksi");
    }
} catch (PDOException $e) {
    // Ignore error jika kolom sudah ada atau ada masalah lain
}

$parfum = new Parfum($db);
$penjualan = new Penjualan($db);
$customer = new Customer($db);
$pengaturan = new Pengaturan($db);

// Get PPN setting
$ppn_persen = floatval($pengaturan->get('ppn_persen') ?? 10);

$message = '';
$message_type = '';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'search_parfum':
            $keyword = sanitizeInput($_GET['keyword']);
            $stmt = $parfum->search($keyword);
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = $row;
            }
            echo json_encode($results);
            exit;
            
        case 'get_parfum':
            $parfum_id = sanitizeInput($_GET['parfum_id']);
            $parfum->id = $parfum_id;
            if ($parfum->readOne()) {
                echo json_encode([
                    'id' => $parfum->id,
                    'kode_parfum' => $parfum->kode_parfum,
                    'nama_parfum' => $parfum->nama_parfum,
                    'harga_jual' => $parfum->harga_jual,
                    'diskon' => $parfum->diskon ?? 0,
                    'stok' => $parfum->stok,
                    'foto_parfum' => $parfum->foto_parfum
                ]);
            } else {
                echo json_encode(['error' => 'parfum tidak ditemukan']);
            }
            exit;
            
    }
}

// Handle transaction submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'process_transaction') {
    try {
        $db->beginTransaction();
        
        // Create penjualan record
        $penjualan->no_transaksi = $penjualan->generateNoTransaksi();
        $penjualan->user_id = $_SESSION['user_id'];
        $penjualan->customer_id = !empty($_POST['customer_id']) ? sanitizeInput($_POST['customer_id']) : null;
        $penjualan->diskon = sanitizeInput($_POST['diskon']);
        $penjualan->ppn = sanitizeInput($_POST['ppn']);
        $penjualan->total_harga = sanitizeInput($_POST['total_harga']);
        $penjualan->total_bayar = sanitizeInput($_POST['total_bayar']);
        $penjualan->kembalian = sanitizeInput($_POST['kembalian']);
        $penjualan->metode_transaksi = !empty($_POST['metode']) ? sanitizeInput($_POST['metode']) : 'cash';
        $penjualan->nomor_rekening = !empty($_POST['nomor_rekening']) ? sanitizeInput($_POST['nomor_rekening']) : null;
        $penjualan->note = !empty($_POST['note']) ? sanitizeInput($_POST['note']) : null;
        
        if (!$penjualan->create()) {
            throw new Exception('Gagal membuat transaksi');
        }
        
        $penjualan_id = $db->lastInsertId();
        
        // Process detail penjualan
        $items = json_decode($_POST['items'], true);
        foreach ($items as $item) {
            // Insert detail penjualan
            $detail_query = "INSERT INTO detail_penjualan (penjualan_id, parfum_id, jumlah, harga_satuan, diskon, subtotal) 
                             VALUES (:penjualan_id, :parfum_id, :jumlah, :harga_satuan, :diskon, :subtotal)";
            $detail_stmt = $db->prepare($detail_query);
            $detail_stmt->bindParam(':penjualan_id', $penjualan_id);
            $detail_stmt->bindParam(':parfum_id', $item['id']);
            $detail_stmt->bindParam(':jumlah', $item['quantity']);
            $detail_stmt->bindParam(':harga_satuan', $item['price']);
            $item_diskon = isset($item['diskon']) ? $item['diskon'] : 0;
            $detail_stmt->bindParam(':diskon', $item_diskon);
            $detail_stmt->bindParam(':subtotal', $item['subtotal']);
            
            if (!$detail_stmt->execute()) {
                throw new Exception('Gagal menyimpan detail penjualan');
            }
            
            // Update stok parfum
            $parfum->updateStok($item['id'], -$item['quantity']);
        }
        
        $db->commit();
        
        // Redirect to receipt
        header('Location: struk.php?id=' . $penjualan_id);
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Get all customer for dropdown
$customer_stmt = $customer->readAll();
$customer_stmt->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="./src/output.css">
</head>
<body class="bg-black">
    <!-- Main Container (White with Gold Border) -->
    <div class="bg-white border-2 border-[#d4af37] rounded-xl shadow-[0_4px_20px_rgba(212,175,55,0.3)] m-2 md:m-5 min-h-screen">

    <div class="flex flex-col md:flex-row">
        
        <!-- Sidebar -->
        <?php 
        $page = "penjualan";
        $role = $_SESSION['user_role'];
        require_once 'sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 overflow-y-auto bg-white">

            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 fade-in">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-black font-montserrat">Penjualan</h1>
                    <p class="text-gray-600 font-montserrat text-sm md:text-base">Sistem penjualan dengan tampilan elegan ✨</p>
                </div>

                <!-- Profile -->
                <div class="flex items-center gap-4 p-3 pr-5 rounded-xl self-end md:self-auto">
                    <div class="text-right">
                        <div class="font-semibold text-black text-sm md:text-base"><?php echo $_SESSION['nama_lengkap']; ?></div>
                        <div class="text-gray-600 text-xs md:text-sm"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center text-lg font-bold bg-gradient-to-br from-[#d4af37] to-[#c9a961] text-black shadow-[0_2px_8px_rgba(212,175,55,0.3)]">
                        <?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Main Body -->
            <div class="bg-transparent">

                <!-- Alert -->
                <?php if ($message): ?>
                    <div class="mb-5 p-4 rounded-lg border-l-4 
                        <?php echo $message_type === 'error' 
                        ? 'bg-red-900/20 border-red-900 text-red-400' 
                        : 'bg-[#d4af37]/15 border-[#d4af37] text-[#d4af37]'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Responsive Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-5 h-auto lg:h-[calc(100vh-200px)] p-2 md:p-5">

                    <!-- Produk -->
                    <div class="bg-white rounded-xl shadow-[0_2px_10px_rgba(212,175,55,0.2)] p-4 md:p-6 overflow-y-auto border-2 border-[#d4af37]">

                        <!-- Search -->
                        <div class="mb-6 relative">
                            <input type="text" id="searchInput" placeholder="Cari parfum..." onkeyup="searchProducts()" 
                                class="w-full py-3 md:py-4 px-4 pr-12 border-2 border-gray-300 rounded-lg text-base bg-white text-gray-800 shadow-[0_2px_8px_rgba(0,0,0,0.05)] focus:outline-none focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)] placeholder:text-gray-400">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xl pointer-events-none">🔍</span>
                        </div>

                        <!-- Products Grid Responsive -->
                        <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-5">
                            <!-- auto load -->
                        </div>
                    </div>

                    <!-- Cart -->
                    <div class="bg-white rounded-xl shadow-[0_2px_10px_rgba(212,175,55,0.2)] p-4 md:p-6 flex flex-col border-2 border-[#d4af37]">

                        <h3 class="text-black text-xl md:text-2xl mb-5 pb-4 border-b-2 border-[#d4af37] text-center font-bold font-montserrat">
                            Keranjang Belanja
                        </h3>

                        <!-- Cart Items -->
                        <div class="flex-1 overflow-y-auto mb-5 pr-1 md:pr-2"
                            id="cartItems">
                            <div class="text-center py-12 px-5 text-gray-500">
                                <div class="text-5xl md:text-6xl mb-4 opacity-60">🛒</div>
                                <p class="text-sm md:text-base font-medium font-montserrat">Keranjang kosong</p>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="border-t-[3px] border-[#d4af37] pt-5 bg-gray-50/80 rounded-lg p-4 md:p-5 mt-2">

                            <div class="flex justify-between mb-3 text-black text-sm md:text-[15px] font-montserrat">
                                <span>Subtotal:</span>
                                <span id="subtotal">Rp 0</span>
                            </div>

                            <div class="flex justify-between mb-3 text-black text-sm md:text-[15px] font-montserrat">
                                <span>Diskon:</span>
                                <span id="diskonDisplay">Rp 0</span>
                            </div>

                            <div class="flex justify-between mb-3 text-black text-sm md:text-[15px] font-montserrat">
                                <span>PPN (<?php echo $ppn_persen; ?>%):</span>
                                <span id="ppn">Rp 0</span>
                            </div>

                            <div class="flex justify-between font-bold text-lg md:text-xl text-[#d4af37] font-montserrat border-t-2 border-[#d4af37] pt-3 md:pt-4 mt-2">
                                <span>Total:</span>
                                <span id="total">Rp 0</span>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-5 pt-5 border-t-2 border-[#d4af37]">

                            <!-- Customer -->
                            <div class="mb-5">
                                <label class="block mb-2 font-semibold text-[#d4af37] text-sm font-montserrat">Customer</label>
                                <select id="customer_id" name="customer_id" 
                                    class="w-full py-3 px-4 border-2 border-gray-300 rounded-lg text-sm md:text-[15px] bg-white text-black focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)]">
                                    <option value="">Pilih customer</option>
                                    <?php while ($row = $customer_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_customer']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-5">
                                <label class="block mb-2 font-semibold text-[#d4af37] text-sm font-montserrat">Metode Pembayaran</label>

                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    <div onclick="selectPaymentMethod('cash')" id="payment-cash"
                                        class="payment-method-btn py-3 text-center border-2 border-gray-300 rounded-lg bg-white text-xs md:text-sm font-semibold cursor-pointer hover:border-[#d4af37] hover:-translate-y-0.5 duration-150 active">
                                        💵 CASH
                                    </div>

                                    <div onclick="selectPaymentMethod('qris')" id="payment-qris"
                                        class="payment-method-btn py-3 text-center border-2 border-gray-300 rounded-lg bg-white text-xs md:text-sm font-semibold cursor-pointer hover:border-[#d4af37] hover:-translate-y-0.5 duration-150">
                                        📱 QRIS
                                    </div>

                                    <div onclick="selectPaymentMethod('transfer')" id="payment-transfer"
                                        class="payment-method-btn py-3 text-center border-2 border-gray-300 rounded-lg bg-white text-xs md:text-sm font-semibold cursor-pointer hover:border-[#d4af37] hover:-translate-y-0.5 duration-150">
                                        🏦 TRANSFER
                                    </div>
                                </div>

                                <input type="hidden" id="metode" value="">
                            </div>

                            <!-- Diskon -->
                            <div class="mb-5">
                                <label for="diskonInput" class="block mb-2 font-semibold text-[#d4af37] text-sm">Diskon (Rp)</label>
                                <input type="number" id="diskonInput" value="0" min="0"
                                    onkeyup="updateSummary()" onchange="updateSummary()"
                                    class="w-full py-3 px-4 border-2 border-gray-300 rounded-lg text-sm md:text-[15px] bg-white text-black focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)]">
                            </div>

                            <!-- Payment Amount -->
                            <div class="mb-5" id="paymentInputGroup">
                                <label for="paymentInput" class="block mb-2 font-semibold text-[#d4af37] text-sm">Jumlah Bayar</label>
                                <input type="number" id="paymentInput" value="0" min="0"
                                    onkeyup="calculateChange()" onchange="calculateChange()"
                                    class="w-full py-3 px-4 border-2 border-gray-300 rounded-lg text-sm md:text-[15px] bg-white text-black focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)]">
                            </div>

                            <!-- Transfer Input -->
                            <div class="mb-5 hidden" id="transferInputGroup">
                                <label class="block mb-2 font-semibold text-[#d4af37] text-sm">Nomor Rekening</label>
                                <input type="text" id="nomorRekeningInput" maxlength="50"
                                    class="w-full py-3 px-4 border-2 border-gray-300 rounded-lg text-sm md:text-[15px] bg-white text-black focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)]">
                            </div>

                            <!-- Change -->
                            <div class="flex justify-between bg-[#d4af37]/15 py-3 px-4 rounded-lg font-semibold text-black" id="changeRow">
                                <span>Kembalian:</span>
                                <span id="change" class="text-lg text-[#d4af37]">Rp 0</span>
                            </div>

                            <!-- Note -->
                            <input type="text" id="note" placeholder="📝 Catatan (opsional)"
                                class="mt-3 w-full py-3 px-4 border-2 border-gray-300 rounded-lg text-sm md:text-[15px] bg-white text-black focus:border-[#d4af37] focus:shadow-[0_4px_12px_rgba(212,175,55,0.3)]">

                            <!-- Button -->
                            <button id="processBtn" onclick="processTransaction()"
                                class="w-full py-4 mt-4 bg-gradient-to-br from-[#d4af37] to-[#c9a961] text-black rounded-xl text-base md:text-lg font-bold shadow-[0_4px_15px_rgba(212,175,55,0.4)] hover:-translate-y-1 hover:shadow-[0_6px_20px_rgba(212,175,55,0.5)] transition disabled:bg-gray-400 disabled:text-gray-600 disabled:cursor-not-allowed"
                                disabled>
                                Proses Transaksi
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>


    <script>
        let cart = [];
        let products = [];

        // Load products on page load
        window.onload = function() {
            searchProducts();
        };

        function searchProducts() {
            const keyword = document.getElementById('searchInput').value;
            
            fetch(`penjualan.php?action=search_parfum&keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    products = data;
                    displayProducts(data);
                })
                .catch(error => console.error('Error:', error));
        }

        function displayProducts(products) {
            const grid = document.getElementById('productGrid');
            grid.innerHTML = '';

            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card';
                productCard.onclick = () => addToCart(product);
                
                const imageUrl = product.foto_parfum 
                    ? product.foto_parfum 
                    : 'assets/img/parfum-bottle.png';

                
                productCard.innerHTML = `
                <div class="border-2 border-[#d4af37] rounded-sm flex cursor-pointer flex-col justify-center items-center duration-100 hover:scale-105">
                    <img src="${imageUrl}" alt="${product.nama_parfum}" class="product-image" onerror="this.src='assets/img/parfum-bottle.png'">
                    <div class="w-full bg-[#d4af37] p-2">
                        <div class="text-white font-montserrat font-bold text-sm md:text-md">${product.nama_parfum}</div>
                        <div class="text-white font-montserrat">${formatCurrency(product.harga_jual)}</div>
                        <div class="text-white font-montserrat">Stok: ${product.stok} ${product.satuan}</div>
                    </div>
                </div>
                `;
                
                grid.appendChild(productCard);
            });
        }

        function addToCart(product) {
            if (product.stok <= 0) {
                alert('Stok parfum habis!');
                return;
            }

            const existingItem = cart.find(item => item.id === product.id);
            
            if (existingItem) {
                if (existingItem.quantity < product.stok) {
                    existingItem.quantity++;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
                // Update diskon jika belum ada atau tetap gunakan yang sudah ada
                if (existingItem.diskon === undefined || existingItem.diskon === 0) {
                    existingItem.diskon = parseFloat(product.diskon || 0);
                }
            } else {
                cart.push({
                    id: product.id,
                    kode_parfum: product.kode_parfum,
                    nama_parfum: product.nama_parfum,
                    price: parseFloat(product.harga_jual),
                    diskon: parseFloat(product.diskon || 0),
                    quantity: 1,
                    max_stock: product.stok,
                    foto_parfum: product.foto_parfum || null
                });
            }
            
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartBody = document.getElementById('cartItems');

            if (cart.length === 0) {
                cartBody.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Keranjang kosong</p>
                    </div>`;
                updateSummary();
                return;
            }

            let cartHTML = '';

            cart.forEach((item, index) => {
                const subtotal = (item.price * item.quantity) - (item.diskon || 0);
                const imageUrl = item.foto_parfum 
                    ? item.foto_parfum 
                    : 'assets/img/parfum-bottle.png';


                cartHTML += `
                    <div class="cart-item">
                        <img src="${imageUrl}" alt="${item.nama_parfum}" onerror="this.src='assets/img/parfum-bottle.png'">
                        <div class="cart-item-content">
                            <div class="item-name font-montserrat">${item.nama_parfum}</div>
                            <del class="cart-item-price font-montserrat text-xs">${formatCurrency(item.price)}</del>
                            <div class="font-montserrat font-bold text-xl">${formatCurrency(subtotal)}</div>
                        </div>
                        <div class="w-full flex justify-end">
                            <div class="qty-selector flex items-center gap-2">
                                <button 
                                    class="qty-btn bg-gray-200 hover:bg-gray-300 text-black px-3 py-1 rounded-lg"
                                    onclick="updateQuantity(${index}, -1)">
                                    −
                                </button>

                                <input 
                                    type="number"
                                    value="${item.quantity}"
                                    min="1"
                                    max="${item.max_stock}"
                                    onchange="setQuantity(${index}, this.value)"
                                    class="qty-input no-spinner w-16 text-center border border-gray-300 rounded-lg py-1"
                                >

                                <button 
                                    class="qty-btn bg-gray-200 hover:bg-gray-300 text-black px-3 py-1 rounded-lg"
                                    onclick="updateQuantity(${index}, 1)">
                                    +
                                </button>
                            </div>

                        </div>
                    </div>
                `;
            });

            cartBody.innerHTML = cartHTML;
            updateSummary();
        }
        
        function selectPaymentMethod(method) {
            // Remove active class from all buttons
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to selected button
            document.getElementById(`payment-${method}`).classList.add('active');
            
            // Set hidden input value
            document.getElementById('metode').value = method;
            
            // If not cash, auto-set payment amount to total and hide change
            if (method !== 'cash') {
                const total = parseFloat(document.getElementById('total').textContent.replace(/[^\d]/g, ''));
                document.getElementById('paymentInput').value = total;
                document.getElementById('paymentInputGroup').style.display = 'none';
                document.getElementById('changeRow').style.display = 'none';
                
                // Show transfer input if method is transfer
                if (method === 'transfer') {
                    document.getElementById('transferInputGroup').style.display = 'block';
                } else {
                    document.getElementById('transferInputGroup').style.display = 'none';
                }
            } else {
                document.getElementById('paymentInputGroup').style.display = 'block';
                document.getElementById('changeRow').style.display = 'flex';
                document.getElementById('paymentInput').value = 0;
                document.getElementById('transferInputGroup').style.display = 'none';
            }
            calculateChange();
        }


        function updateQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity >= 1 && newQuantity <= item.max_stock) {
                item.quantity = newQuantity;
                updateCartDisplay();
            }
        }

        function setQuantity(index, value) {
            const item = cart[index];
            const newQuantity = parseInt(value);
            
            if (newQuantity >= 1 && newQuantity <= item.max_stock) {
                item.quantity = newQuantity;
                updateCartDisplay();
            }
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        function updateItemDiskon(index, diskonValue) {
            const item = cart[index];
            item.diskon = parseFloat(diskonValue) || 0;
            updateCartDisplay();
        }

        function updateSummary() {
            // Hitung subtotal dengan diskon per item
            const subtotal = cart.reduce((sum, item) => {
                const itemSubtotal = (item.price * item.quantity) - (item.diskon || 0);
                return sum + Math.max(0, itemSubtotal);
            }, 0);
            
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const subtotalAfterDiskon = Math.max(0, subtotal - diskon);
            const ppnPersen = <?php echo $ppn_persen; ?>;
            const ppn = subtotalAfterDiskon * (ppnPersen / 100);
            const total = subtotalAfterDiskon + ppn;
            
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('diskonDisplay').textContent = formatCurrency(diskon);
            document.getElementById('ppn').textContent = formatCurrency(ppn);
            document.getElementById('total').textContent = formatCurrency(total);
            
            calculateChange();
        }

        function calculateChange() {
            // Hitung subtotal dengan diskon per item
            const subtotal = cart.reduce((sum, item) => {
                const itemSubtotal = (item.price * item.quantity) - (item.diskon || 0);
                return sum + Math.max(0, itemSubtotal);
            }, 0);
            
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const subtotalAfterDiskon = Math.max(0, subtotal - diskon);
            const ppnPersen = <?php echo $ppn_persen; ?>;
            const totalWithPPN = subtotalAfterDiskon * (1 + (ppnPersen / 100));
            const metode = document.getElementById('metode').value;
            const payment = parseFloat(document.getElementById('paymentInput').value) || 0;
            
            let change = 0;
            if (metode === 'cash') {
                change = payment - totalWithPPN;
            } else {
                // For non-cash, no change
                change = 0;
            }
            
            document.getElementById('change').textContent = formatCurrency(Math.max(0, change));
            
            const processBtn = document.getElementById('processBtn');
            const canProcess = cart.length > 0 && (metode !== 'cash' || payment >= totalWithPPN);
            processBtn.disabled = !canProcess || !metode;
        }

        function processTransaction() {
            // Hitung subtotal dengan diskon per item
            const subtotal = cart.reduce((sum, item) => {
                const itemSubtotal = (item.price * item.quantity) - (item.diskon || 0);
                return sum + Math.max(0, itemSubtotal);
            }, 0);
            
            const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
            const subtotalAfterDiskon = Math.max(0, subtotal - diskon);
            const ppnPersen = <?php echo $ppn_persen; ?>;
            const ppn = subtotalAfterDiskon * (ppnPersen / 100);
            const totalWithPPN = subtotalAfterDiskon + ppn;
            let payment = parseFloat(document.getElementById('paymentInput').value) || 0;
            const customerId = document.getElementById('customer_id').value;
            const metode = document.getElementById('metode').value;
            const note = document.getElementById('note').value;
            const nomorRekening = document.getElementById('nomorRekeningInput') ? document.getElementById('nomorRekeningInput').value : '';
            
            if (!metode) {
                alert("Pilih metode pembayaran terlebih dahulu!");
                return;
            }
            
            // Validate transfer method
            if (metode === 'transfer' && !nomorRekening.trim()) {
                alert('Masukkan nomor rekening untuk metode transfer!');
                return;
            }
            
            // For non-cash payments, set payment equal to total
            if (metode !== 'cash') {
                payment = totalWithPPN;
            } else if (payment < totalWithPPN) {
                alert('Jumlah bayar kurang!');
                return;
            }
            
            const change = payment - totalWithPPN;
            
            // Calculate subtotal for each item (with item discount, without PPN)
            cart.forEach(item => {
                item.subtotal = Math.max(0, (item.price * item.quantity) - (item.diskon || 0));
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="process_transaction">
                <input type="hidden" name="items" value='${JSON.stringify(cart)}'>
                <input type="hidden" name="customer_id" value="${customerId}">
                <input type="hidden" name="diskon" value="${diskon}">
                <input type="hidden" name="ppn" value="${ppn}">
                <input type="hidden" name="total_harga" value="${totalWithPPN}">
                <input type="hidden" name="total_bayar" value="${payment}">
                <input type="hidden" name="kembalian" value="${change}">
                <input type="hidden" name="metode" value="${metode}">
                <input type="hidden" name="nomor_rekening" value="${nomorRekening}">
                <input type="hidden" name="note" value="${note}">
            `;
            
            document.body.appendChild(form);
            form.submit();
        }

        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }
    </script>
</body>
</html>
