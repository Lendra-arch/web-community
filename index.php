<?php
require_once 'db_connect.php';

$content_result = $conn->query("SELECT * FROM content");
$content = [];
while ($row = $content_result->fetch_assoc()) {
    $content[$row['section']] = $row['content'];
}

$bg_result = $conn->query("SELECT content FROM content WHERE section = 'background_image' LIMIT 1");
$background = $bg_result->fetch_assoc();
$background_image = $background ? 'uploads/' . $background['content'] : 'default-background.jpg';

$board_members_result = $conn->query("SELECT * FROM board_members");
$gallery_result = $conn->query("SELECT * FROM gallery LIMIT 12");
$news_result = $conn->query("SELECT * FROM news ORDER BY date_posted DESC LIMIT 3");
$theme_result = $conn->query("SELECT * FROM theme LIMIT 1");
$theme = $theme_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klub CB Sarang Glatik</title>
    <meta name="description" content="<?php echo htmlspecialchars($content['meta_description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($content['meta_keywords']); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/uploads/logo.png" type="image/x-icon"/>
    <link rel="stylesheet" href="dynamic-styles.php">
     <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent_color']; ?>;
            --font-family: <?php echo $theme['font']; ?>, sans-serif;
        }
    </style>
</head>
<body>
     <div class="loading-overlay" id="loading">
        <div class="loader"></div>
    </div>
    <main class="main" id="main">

    <!-- Navbar -->
      <header>
    <nav class="navbar">
        <div class="logo">
            <img src="/uploads/logo.png" alt="Logo" height="50px" weight="50px">
        </div>
        <button class="hamburger-menu" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <ul class="nav-menu">
            <li><a href="#beranda">Home</a></li>
            <li><a href="#tentang-kami">Tentang Kami</a></li>
            <li><a href="#galeri">Gallery</a></li>
            <li><a href="#pendaftaran" class="register-link">Pendaftaran</a></li>
        </ul>
    </nav>
</header>

        <!-- Section 1: Beranda -->
        <section id="beranda" class="hero-section">
            <div class="overlay"></div>
            <div class="hero-content">
                <div class="text-content">
                    <p class="welcome-text">Selamat Datang di</p>
                    <h1>Klub<br>Sarang Glatik</h1>
                    <p class="subtitle"><?php echo $content['short_text']; ?></p>
                    <a href="https://wa.me/+6285815755656" id="cta-button" style="text-decoration: none;">Hubungi Kami</a>
                </div>
                <div class="social-links">
                    <a href="https://www.instagram.com/sarang_glatik_official" class="social-link" aria-label="Instagram"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" width="36px" height="36px">    <path fill="white" d="M 8 3 C 5.243 3 3 5.243 3 8 L 3 16 C 3 18.757 5.243 21 8 21 L 16 21 C 18.757 21 21 18.757 21 16 L 21 8 C 21 5.243 18.757 3 16 3 L 8 3 z M 8 5 L 16 5 C 17.654 5 19 6.346 19 8 L 19 16 C 19 17.654 17.654 19 16 19 L 8 19 C 6.346 19 5 17.654 5 16 L 5 8 C 5 6.346 6.346 5 8 5 z M 17 6 A 1 1 0 0 0 16 7 A 1 1 0 0 0 17 8 A 1 1 0 0 0 18 7 A 1 1 0 0 0 17 6 z M 12 7 C 9.243 7 7 9.243 7 12 C 7 14.757 9.243 17 12 17 C 14.757 17 17 14.757 17 12 C 17 9.243 14.757 7 12 7 z M 12 9 C 13.654 9 15 10.346 15 12 C 15 13.654 13.654 15 12 15 C 10.346 15 9 13.654 9 12 C 9 10.346 10.346 9 12 9 z"/></svg></a>
                    <a href="#" class="social-link" aria-label="TikTok"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="27px" height="27px"><path fill="white" d="M448 209.9a210.1 210.1 0 0 1 -122.8-39.3V349.4A162.6 162.6 0 1 1 185 188.3V278.2a74.6 74.6 0 1 0 52.2 71.2V0l88 0a121.2 121.2 0 0 0 1.9 22.2h0A122.2 122.2 0 0 0 381 102.4a121.4 121.4 0 0 0 67 20.1z"/></svg></a>
                </div>
            </div>
            <div class="scroll-indicator">
                Scroll Down ⬇
            </div>
        </section>

        <!-- Section 2: Tentang Kami -->
        <section id="tentang-kami" class="about-section">
            <div class="container">
                <div class="about-content">
                    <?php
                    $about_img_result = $conn->query("SELECT content FROM content WHERE section = 'about_image'");
                    $about_img = $about_img_result->fetch_assoc();
                    $about_image = $about_img ? $about_img['content'] : 'about-default.jpg';

                    $about_text_result = $conn->query("SELECT content FROM content WHERE section = 'about_text'");
                    $about_text = $about_text_result->fetch_assoc();
                    ?>
                    <div class="about-image">
                        <img src="<?php echo htmlspecialchars($about_image); ?>" alt="Tentang Kami">
                    </div>
                    <div class="about-text-content">
                        <h2>Tentang Sarang Glatik</h2>
                        <p><?php echo $content['about_text']; ?></p>
                        <ul class="feature-list">
                            <li>
                                <span class="check-icon">✓</span>
                                Dibentuk pada tahun 2019
                            </li>
                            <li>
                                <span class="check-icon">✓</span>
                                Anggota dari berbagai kota di Indonesia
                            </li>
                            <li>
                                <span class="check-icon">✓</span>
                                Rutin mengadakan touring dan bakti sosial
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Susunan Pengurus -->
        <section id="pengurus" class="board-section">
            <div class="container">
                <h2>Susunan Pengurus</h2>
                <div class="board-grid">
                    <?php while ($row = $board_members_result->fetch_assoc()): ?>
                        <div class="board-member">
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><?php echo htmlspecialchars($row['position']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <!-- Section 4: Galeri Foto -->
        <section id="galeri" class="gallery-section">
            <div class="container">
                <h2>Galeri Foto</h2>
                <div class="gallery-grid">
                    <?php while ($row = $gallery_result->fetch_assoc()): ?>
                        <div class="gallery-item">
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['caption']); ?>">
                            <div class="gallery-caption"><?php echo htmlspecialchars($row['caption']); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="gallery-more">
                    <a href="https://www.instagram.com/sarang_glatik_official" target="_blank" rel="noopener noreferrer" class="instagram-link">
                        Lihat lebih banyak di Instagram  
                        <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" width="19px" height="19px" style="margin-left: 7px;">    <path d="M 8 3 C 5.243 3 3 5.243 3 8 L 3 16 C 3 18.757 5.243 21 8 21 L 16 21 C 18.757 21 21 18.757 21 16 L 21 8 C 21 5.243 18.757 3 16 3 L 8 3 z M 8 5 L 16 5 C 17.654 5 19 6.346 19 8 L 19 16 C 19 17.654 17.654 19 16 19 L 8 19 C 6.346 19 5 17.654 5 16 L 5 8 C 5 6.346 6.346 5 8 5 z M 17 6 A 1 1 0 0 0 16 7 A 1 1 0 0 0 17 8 A 1 1 0 0 0 18 7 A 1 1 0 0 0 17 6 z M 12 7 C 9.243 7 7 9.243 7 12 C 7 14.757 9.243 17 12 17 C 14.757 17 17 14.757 17 12 C 17 9.243 14.757 7 12 7 z M 12 9 C 13.654 9 15 10.346 15 12 C 15 13.654 13.654 15 12 15 C 10.346 15 9 13.654 9 12 C 9 10.346 10.346 9 12 9 z"/></svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Section 5: Cara Pendaftaran -->
        <section id="pendaftaran" class="registration-section">
            <div class="container">
                <h2>Cara Pendaftaran</h2>
                <div class="registration-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Ketahui Info</h3>
                        <p>Tanya tanya terlebih dahulu</p>
                    </div>
                    <div class="step">
                        <div class=" step-number">2</div>
                        <h3>Isi Formulir</h3>
                        <p>Isi formulir yang sudah disediakan</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Tunggu Persetujuan</h3>
                        <p>Tunggu Proses ACC, Sekitar 1 hari setelah pengiriman.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <h3>Selamat Bergabung!</h3>
                        <p>Anda resmi menjadi anggota dari Klub Sarang Glatik</p>
                    </div>
                </div>
                <a href="https://wa.me/+6285815755656" id="cta-button" style="text-decoration: none;">Daftar Sekarang</a>
            </div>
        </section>

        <!-- Section 6: Kegiatan & Berita -->
        <section id="kegiatan-berita" class="news-section">
            <div class="container">
                <h2>Kegiatan & Berita Terkini</h2>
                <div class="news-grid">
                    <?php while ($row = $news_result->fetch_assoc()): ?>
                        <article class="news-item">
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo substr(htmlspecialchars($row['content']), 0, 100) . '...'; ?></p>
                            <a href="news.php?id=<?php echo $row['id']; ?>" class="read-more" data-id="<?php echo $row['id']; ?>">Baca Selengkapnya</a>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <footer>
            <p>&copy; 2025 Klub Sarang Glatik.</p>
        </footer>
    </main>
    <script src="script.js" defer></script>
</body>
</html>