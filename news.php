<?php
error_reporting(E_ALL); //Wajib delete ini
ini_set('display_errors', 1);

require 'db_connect.php'; // Koneksi ke database

// Periksa apakah parameter ID ada di URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    // Query untuk mengambil berita berdasarkan ID
    $stmt = $conn->prepare("SELECT title, content, image, date_posted FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
    } else {
        echo "<p>Berita tidak ditemukan.</p>";
        exit;
    }
} else {
    echo "<p>ID berita tidak valid.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="news-style.css">
    <link rel="icon" href="/uploads/logo.png" type="image/x-icon"/>
    <link rel="stylesheet" href="dynamic-styles.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <article class="news-article">
            <h1><?php echo htmlspecialchars($news['title']); ?></h1>
            <p class="date"><em>Dipublikasikan pada: <?php echo date("d M Y", strtotime($news['date_posted'])); ?></em></p>
            
            <?php if (!empty($news['image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="news-image">
            <?php endif; ?>
            
            <div class="content">
                <p><?php echo nl2br(htmlspecialchars($news['content'])); ?></p>
            </div>
            <a href="https://sarangglatik.web.id" class="back-button">Kembali ke Beranda</a>
        </article>
    </div>
</body>
</html>