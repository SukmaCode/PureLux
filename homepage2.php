<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Ambil 3 produk (misalnya best seller)
$query = "
    SELECT 
        nama_parfum,
        kategori_id,
        satuan,
        deskripsi,
        harga_jual,
        foto_parfum
    FROM parfum
";

$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PURELUX - Luxury Perfume</title>
    <link rel="stylesheet" href="./src/output.css" />
    <!-- <script type="module" src="./src/three.js"></script> -->
  </head>
  <body class="bg-white font-lejourserif">
    <!-- Navbar -->
    <nav class="fixed w-full bg-white/95 backdrop-blur-sm shadow-sm z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          <div class="flex-shrink-0">
            <h1
              class="text-2xl sm:text-3xl font-lejourserif font-bold text-[#0f0f0f]"
            >
              PURELUX
            </h1>
          </div>
          <div class="hidden md:flex space-x-8 justify-center items-center">
            <a
              href="#"
              class="text-[#0f0f0f] hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
              >Home</a
            >
            <a
              href="#collection"
              class="text-[#0f0f0f] hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
              >Collection</a
            >
            <a
              href="#about"
              class="text-[#0f0f0f] hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
              >About</a
            >
            <a
              href="#contact"
              class="text-[#0f0f0f] hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
              >Contact</a
            >
          </div>
          <div class="hidden md:flex gap-4">
            <a
              href="login.php"
              class="font-montserrat px-6 py-2 bg-[#d4af37] rounded-sm text-white hover:bg-[#d1a71f]"
              >Login</a
            >
            <a href="" class="font-montserrat px-4 py-2 rounded-sm text-black"
              >Sign Up</a
            >
          </div>
          <button class="md:hidden text-[#0f0f0f]">
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              ></path>
            </svg>
          </button>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-[#0f0f0f] text-white py-30 overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div
          class="absolute top-20 right-20 w-96 h-96 bg-[#d4af37] rounded-full blur-3xl"
        ></div>
        <div
          class="absolute bottom-20 left-20 w-96 h-96 bg-[#d4af37] rounded-full blur-3xl"
        ></div>
      </div>

      <div class="max-w-7xl mx-auto relative z-10">
        <div
          class="flex flex-col-reverse md:flex-row justify-between items-center px-8"
        >
          <div class="space-y-8">
            <div class="space-y-4">
              <p
                class="text-[#d4af37] text-sm sm:text-base tracking-widest uppercase font-medium"
              >
                Timeless PureLux
              </p>
              <h2
                class="text-4xl sm:text-5xl lg:text-7xl font-lejourserif font-bold leading-tight"
              >
                The Art of
                <span class="text-[#d4af37] block">Luxury Scent</span>
              </h2>
              <p
                class="font-montserrat text-gray-300 text-base sm:text-lg max-w-lg leading-relaxed"
              >
                Discover our exclusive collection of handcrafted perfumes, where
                tradition meets modern sophistication
              </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
              <button
                class="bg-[#d4af37] text-[#0f0f0f] px-8 py-4 rounded-none font-montserrat hover:bg-white transition-all duration-300 hover:scale-105 shadow-lg"
              >
                EXPLORE COLLECTION
              </button>
              <button
                class="border-2 border-[#d4af37] text-[#d4af37] px-8 py-4 rounded-none font-montserrat hover:bg-[#d4af37] hover:text-[#0f0f0f] transition-all duration-300"
              >
                LEARN MORE
              </button>
            </div>
          </div>

          <!-- <canvas id="canvas3d" class="w-[500px] h-[500px]"></canvas> -->
          <div>
            <img src="./assets/img/nomerk-perfume.png" alt="" class="w-96" />
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Collection -->
    <section id="collection" class="py-20 px-4 bg-white">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 space-y-4">
          <p class="text-[#d4af37] text-sm tracking-widest uppercase">
            Premium Selection
          </p>
          <h2
            class="text-4xl sm:text-5xl font-lejourserif font-bold text-[#0f0f0f]"
          >
            Featured Collection
          </h2>
          <p class="text-gray-600 max-w-2xl mx-auto font-montserrat">
            Handpicked masterpieces from our exclusive range
          </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">

<?php foreach ($products as $product): ?>
  <div
    class="group bg-white border border-[#d4af37]/30 shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2"
  >
    <div
      class="relative h-80 bg-gradient-to-br flex justify-center items-center from-gray-100 to-gray-50 overflow-hidden"
    >
      <?php if ($row['foto_parfum']): ?>
                                        <img src="<?php echo $row['foto_parfum']; ?>" 
                                            class="w-20 h-20 object-cover rounded-lg border border-[#d4af37]">
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>

      <div
        class="absolute top-4 right-4 bg-[#d4af37] text-[#0f0f0f] px-3 py-1 text-xs font-montserrat font-bold"
      >
        BEST SELLER
      </div>
    </div>

    <div class="p-6 space-y-3">
      <h3 class="text-2xl font-lejourserif font-semibold text-[#0f0f0f]">
        <?= htmlspecialchars($product['nama_parfum']) ?>
      </h3>

      <p class="text-gray-600 text-sm font-montserrat">
        <?= htmlspecialchars($product['kategori_id']) ?> - <?= htmlspecialchars($product['satuan']) ?>ml
      </p>

      <p class="text-gray-500 text-sm line-clamp-2 font-montserrat">
        <?= htmlspecialchars($product['deskripsi']) ?>
      </p>

      <div class="flex justify-between items-center pt-4">
        <span class="text-2xl font-montserrat font-bold text-[#d4af37]">
          Rp <?= number_format($product['harga_jual'], 0, ',', '.') ?>
        </span>

        <button
          class="bg-[#0f0f0f] text-white px-6 py-2 font-montserrat text-sm hover:bg-[#d4af37] hover:text-[#0f0f0f] transition-colors duration-300"
        >
          ADD TO CART
        </button>
      </div>
    </div>
  </div>
<?php endforeach; ?>

</div>

      </div>
    </section>

    <!-- About Brand -->
    <section id="about" class="py-20 px-4 bg-[#0f0f0f] text-white">
      <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div class="relative h-96 md:h-[600px]">
            <div
              class="absolute inset-0 bg-gradient-to-br from-[#d4af37]/20 to-transparent rounded-lg"
            ></div>
            <div
              class="absolute inset-4 bg-white/5 backdrop-blur-sm rounded-lg shadow-2xl flex items-center justify-center"
            >
              <div class="text-center space-y-4 p-8">
                <div
                  class="w-32 h-48 bg-gradient-to-b from-[#d4af37]/30 to-[#d4af37]/10 mx-auto rounded-sm shadow-xl"
                ></div>
                <p class="text-[#d4af37] font-lejourserif text-xl">
                  Craftsmanship
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <p class="text-[#d4af37] text-sm tracking-widest uppercase">
              Our Story
            </p>
            <h2 class="text-4xl sm:text-5xl font-lejourserif font-bold">
              A Legacy of Timeless PureLux
            </h2>
            <p class="text-gray-300 text-lg leading-relaxed font-montserrat">
              Since 1985, PURELUX has been crafting exceptional fragrances that
              embody sophistication and artistry. Each bottle represents decades
              of perfumery expertise and an unwavering commitment to quality.
            </p>
            <p class="text-gray-400 leading-relaxed font-montserrat">
              Our master perfumers select only the finest ingredients from
              around the world, creating scents that tell stories and evoke
              emotions. Every fragrance is a masterpiece, designed for those who
              appreciate the finer things in life.
            </p>
            <div class="grid grid-cols-3 gap-6 pt-8">
              <div class="text-center">
                <p class="text-4xl font-lejourserif font-bold text-[#d4af37]">
                  38+
                </p>
                <p class="text-gray-400 text-sm mt-2">Years Legacy</p>
              </div>
              <div class="text-center">
                <p class="text-4xl font-lejourserif font-bold text-[#d4af37]">
                  150+
                </p>
                <p class="text-gray-400 text-sm mt-2">Unique Scents</p>
              </div>
              <div class="text-center">
                <p class="text-4xl font-lejourserif font-bold text-[#d4af37]">
                  50K+
                </p>
                <p class="text-gray-400 text-sm mt-2">Happy Clients</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 px-4 bg-white">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16 space-y-4">
          <p
            class="text-[#d4af37] text-sm tracking-widest uppercase font-montserrat"
          >
            What They Say
          </p>
          <h2
            class="text-4xl sm:text-5xl font-lejourserif font-bold text-[#0f0f0f]"
          >
            Client Testimonials
          </h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div
            class="bg-gray-50 p-8 shadow-lg border-t-4 border-[#d4af37] hover:shadow-xl transition-shadow duration-300"
          >
            <div class="flex mb-4">
              <span class="text-[#d4af37] text-2xl">★★★★★</span>
            </div>
            <div class="flex items-center space-x-4">
              <div
                class="w-12 h-12 bg-gradient-to-br from-[#d4af37] to-[#d4af37]/50 rounded-full"
              >
                <!-- <img src="" alt="profil" /> -->
              </div>
              <div>
                <p class="font-semibold text-[#0f0f0f]">Sarah Anderson</p>
                <p class="text-sm text-gray-500 font-montserrat">
                  Fashion Designer
                </p>
              </div>
            </div>
            <p
              class="text-gray-600 italic mt-6 leading-relaxed font-montserrat"
            >
              "Absolutely stunning fragrance. The quality is unmatched and the
              scent lasts all day. Worth every penny!"
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#0f0f0f] text-white py-16 px-4">
      <div class="max-w-7xl mx-auto">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
          <div class="space-y-4">
            <h3 class="text-3xl font-lejourserif font-bold">
              PURELUX<span class="text-[#d4af37]">.</span>
            </h3>
            <p class="text-gray-400 text-sm leading-relaxed font-montserrat">
              Crafting timeless fragrances since 1985. Experience luxury in
              every drop.
            </p>
            <div class="flex space-x-4 pt-4">
              <a
                href="#"
                class="w-10 h-10 bg-[#d4af37]/20 hover:bg-[#d4af37] flex items-center justify-center transition-colors duration-300"
              >
                <span class="text-[#d4af37] hover:text-[#0f0f0f]">f</span>
              </a>
              <a
                href="#"
                class="w-10 h-10 bg-[#d4af37]/20 hover:bg-[#d4af37] flex items-center justify-center transition-colors duration-300"
              >
                <span class="text-[#d4af37] hover:text-[#0f0f0f]">in</span>
              </a>
              <a
                href="#"
                class="w-10 h-10 bg-[#d4af37]/20 hover:bg-[#d4af37] flex items-center justify-center transition-colors duration-300"
              >
                <span class="text-[#d4af37] hover:text-[#0f0f0f]">ig</span>
              </a>
            </div>
          </div>

          <div>
            <h4 class="text-[#d4af37] font-semibold mb-4 text-lg">Shop</h4>
            <ul class="space-y-2 text-gray-400">
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >New Arrivals</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >Best Sellers</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >Gift Sets</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >Limited Edition</a
                >
              </li>
            </ul>
          </div>

          <div>
            <h4 class="text-[#d4af37] font-semibold mb-4 text-lg">Support</h4>
            <ul class="space-y-2 text-gray-400">
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >Contact Us</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >Shipping Info</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >Returns</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="hover:text-[#d4af37] transition-colors duration-300 font-montserrat"
                  >FAQ</a
                >
              </li>
            </ul>
          </div>

          <div>
            <h4 class="text-[#d4af37] font-semibold mb-4 text-lg">
              Newsletter
            </h4>
            <p class="text-gray-400 text-sm mb-4 font-montserrat">
              Subscribe for exclusive offers and updates
            </p>
            <div class="space-y-3">
              <input
                type="email"
                placeholder="Your email"
                class="w-full font-montserrat bg-white/10 border border-[#d4af37]/30 px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-[#d4af37] transition-colors"
              />
              <button
                class="w-full bg-[#d4af37] font-montserrat text-[#0f0f0f] px-4 py-3 font-semibold hover:bg-white transition-colors duration-300"
              >
                SUBSCRIBE
              </button>
            </div>
          </div>
        </div>

        <div class="border-t border-[#d4af37]/30 pt-8 text-center">
          <p class="text-gray-400 text-sm">
            © 2025 PURELUX. All rights reserved. | Crafted with passion
          </p>
        </div>
      </div>
    </footer>
  </body>
</html>
