<?php
session_start();
require_once 'db_connect.php';

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Login logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to get user data based on username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password entered with the one stored in the database
        if (password_verify($password, $user['password'])) {
            // If login is successful
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            $_SESSION['role'] = $user['role']; // Store user role in session
            $_SESSION['device_id'] = $_SERVER['HTTP_USER_AGENT']; // Store device ID

            // Update last login
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();

            // Redirect to admin dashboard after successful login
            header('Location: admin-dashboard.php');
            exit();
        } else {
            $error = 'Username or password is incorrect!';
        }
    } else {
        $error = 'Username or password is incorrect!';
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-dashboard.php');
    exit();
}

// Session Expiry Logic
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $session_lifetime = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_lifetime) {
        session_unset();
        session_destroy();
        header('Location: admin-dashboard.php?error=session_expired');
        exit();
    }
    $_SESSION['last_activity'] = time(); // Update last activity time
}

// Memproses formulir pembaruan konten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_content'])) {
    $section = $_POST['section'];  // ID bagian yang akan diperbarui
    $content = $_POST['content'];  // Konten baru

    // Validasi data
    if (empty($section) || empty($content)) {
        $error = "Section ID atau Content kosong.";
    } else {
        // Query update menggunakan parameterized query
        $query = "UPDATE `content` SET `content` = ? WHERE `id` = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die("Error pada persiapan query: " . $conn->error);
        }

        // Mengikat parameter ke query
        $stmt->bind_param("si", $content, $section);

        // Eksekusi query
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $success = "Konten berhasil diperbarui.";
            } else {
                $error = "Tidak ada perubahan pada data (mungkin data sama).";
            }
        } else {
            $error = "Error saat menjalankan query: " . $stmt->error;
        }

        // Menutup statement
        $stmt->close();
    }
}

//Memproses event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        $name = $_POST['event_name'];
        $date = $_POST['event_date'];
        $description = $_POST['event_description'];
        $location = $_POST['event_location'];
        
        $stmt = $conn->prepare("INSERT INTO events (name, date, description, location) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $date, $description, $location);
        if ($stmt->execute()) {
            $success = "Acara baru berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan acara.";
        }
    } elseif (isset($_POST['delete_event'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Acara berhasil dihapus.";
        } else {
            $error = "Gagal menghapus acara.";
        }
    }
}

//Memproses Tema
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_theme'])) {
    $primary_color = $_POST['primary_color'];
    $secondary_color = $_POST['secondary_color'];
    $accent_color = $_POST['accent_color'];
    $font = $_POST['font'];
    
    $stmt = $conn->prepare("UPDATE theme SET primary_color = ?, secondary_color = ?, accent_color = ?, font = ?");
    $stmt->bind_param("ssss", $primary_color, $secondary_color, $accent_color, $font);
    if ($stmt->execute()) {
        $success = "Tema berhasil diperbarui.";
    } else {
        $error = "Gagal memperbarui tema.";
    }
}

// Memproses News
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_news'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author = $_POST['author'];
        $tags = $_POST['tags'];
        $image = $_FILES['image']['name'];
        $date = date('Y-m-d');
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_types)) {
            // Generate unique filename
            $new_file_name = 'news-' . time() . '.' . $imageFileType;
            $new_target_file = $target_dir . $new_file_name;

            // Compress the image before uploading
            if ($imageFileType === 'jpg' || $imageFileType === 'jpeg') {
                $image_resource = imagecreatefromjpeg($_FILES["image"]["tmp_name"]);
                imagejpeg($image_resource, $new_target_file, 75); // Compress to 75% quality
                imagedestroy($image_resource);
            } elseif ($imageFileType === 'png') {
                $image_resource = imagecreatefrompng($_FILES["image"]["tmp_name"]);
                imagepng($image_resource, $new_target_file, 6); // 0 is no compression, 9 is max compression
                imagedestroy($image_resource);
            } elseif ($imageFileType === 'gif') {
                $image_resource = imagecreatefromgif($_FILES["image"]["tmp_name"]);
                imagegif($image_resource, $new_target_file); // No compression for GIFs
                imagedestroy($image_resource);
            }

            // Check if the file was uploaded and compressed successfully
            if (file_exists($new_target_file)) {
                // Insert the data into the database
                $stmt = $conn->prepare("INSERT INTO news (title, content, author, tags, image, date_posted) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $title, $content, $author, $tags, $new_file_name, $date);
                if ($stmt->execute()) {
                    $success = "Berita baru berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambahkan berita.";
                }
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        } else {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        }
    } elseif (isset($_POST['delete_news'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Berita berhasil dihapus.";
        } else {
            $error = "Gagal menghapus berita.";
        }
    }
}


//Memproses Anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_board_member'])) {
        $name = $_POST['name'];
        $position = $_POST['position'];
        $image = $_FILES['image']['name'];
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_types)) {
            // Generate unique filename with .webp extension
            $new_file_name = 'board-member-' . time() . '.webp';
            $new_target_file = $target_dir . $new_file_name;

            // Convert and compress the image to WebP
            $upload_success = false;
            $image_tmp = $_FILES["image"]["tmp_name"];

            if ($imageFileType === 'jpg' || $imageFileType === 'jpeg') {
                $image_resource = imagecreatefromjpeg($image_tmp);
                $upload_success = imagewebp($image_resource, $new_target_file, 60); // Higher compression
            } elseif ($imageFileType === 'png') {
                $image_resource = imagecreatefrompng($image_tmp);
                imagepalettetotruecolor($image_resource); // Convert PNG to TrueColor for better compression
                $upload_success = imagewebp($image_resource, $new_target_file, 80);
            } elseif ($imageFileType === 'gif') {
                $image_resource = imagecreatefromgif($image_tmp);
                $upload_success = imagewebp($image_resource, $new_target_file, 80);
            }

            // Destroy image resource to free memory
            if ($image_resource) {
                imagedestroy($image_resource);
            }

            // Check if conversion was successful
            if ($upload_success && file_exists($new_target_file)) {
                // Insert data into database
                $stmt = $conn->prepare("INSERT INTO board_members (name, position, image) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $position, $new_file_name);
                if ($stmt->execute()) {
                    $success = "Anggota pengurus baru berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambahkan anggota pengurus: " . $conn->error;
                }
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        } else {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        }
    } elseif (isset($_POST['delete_board_member'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM board_members WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Anggota pengurus berhasil dihapus.";
        } else {
            $error = "Gagal menghapus anggota pengurus: " . $conn->error;
        }
    }
}


//Memproses Galeri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_gallery_image'])) {
        $image = $_FILES['image']['name'];
        $caption = $_POST['caption'];
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_types)) {
            // Generate unique filename with .webp extension
            $new_file_name = 'gallery-' . time() . '.webp';
            $new_target_file = $target_dir . $new_file_name;

            // Convert and compress the image to WebP
            $upload_success = false;
            $image_tmp = $_FILES["image"]["tmp_name"];

            if ($imageFileType === 'jpg' || $imageFileType === 'jpeg') {
                $image_resource = imagecreatefromjpeg($image_tmp);
                $upload_success = imagewebp($image_resource, $new_target_file, 50); // Lebih tinggi kompresi tanpa pecah
            } elseif ($imageFileType === 'png') {
                $image_resource = imagecreatefrompng($image_tmp);
                imagepalettetotruecolor($image_resource); // Ubah PNG ke TrueColor agar kompresi lebih baik
                $upload_success = imagewebp($image_resource, $new_target_file, 70);
            } elseif ($imageFileType === 'gif') {
                $image_resource = imagecreatefromgif($image_tmp);
                $upload_success = imagewebp($image_resource, $new_target_file, 80);
            }

            // Destroy image resource to free memory
            if ($image_resource) {
                imagedestroy($image_resource);
            }

            // Check if conversion was successful
            if ($upload_success && file_exists($new_target_file)) {
                // Insert data into database
                $stmt = $conn->prepare("INSERT INTO gallery (image, caption) VALUES (?, ?)");
                $stmt->bind_param("ss", $new_file_name, $caption);
                if ($stmt->execute()) {
                    $success = "Gambar galeri berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambahkan gambar galeri.";
                }
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        } else {
            $error = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        }
    } elseif (isset($_POST['delete_gallery_image'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Gambar galeri berhasil dihapus.";
        } else {
            $error = "Gagal menghapus gambar galeri.";
        }
    }
}


// Process BG IMAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_background'])) {
    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === 0) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_name = $_FILES['background_image']['name'];
        $file_tmp = $_FILES['background_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            // Generate unique filename
            $new_file_name = 'hero-bg-' . time() . '.' . $file_ext;
            
            // Compress the image before uploading
            $compressed_image = null;
            
            if ($file_ext === 'jpg' || $file_ext === 'jpeg') {
                $image = imagecreatefromjpeg($file_tmp);
                imagejpeg($image, $new_file_name, 75); // 75 is the quality (0-100)
                imagedestroy($image);
            } elseif ($file_ext === 'png') {
                $image = imagecreatefrompng($file_tmp);
                imagepng($image, $new_file_name, 6); // 0 is no compression, 9 is maximum compression
                imagedestroy($image);
            } elseif ($file_ext === 'gif') {
                $image = imagecreatefromgif($file_tmp);
                imagegif($image, $new_file_name);
                imagedestroy($image);
            }
            
            // Get current background image before updating
            $stmt = $conn->prepare("SELECT content FROM content WHERE section = 'background_image'");
            $stmt->execute();
            $result = $stmt->get_result();
            $old_bg = $result->fetch_assoc();
            
            if (file_exists($new_file_name)) {
                // Update the database with new filename
                $stmt = $conn->prepare("UPDATE content SET content = ? WHERE section = 'background_image'");
                $stmt->bind_param("s", $new_file_name);
                
                if ($stmt->execute()) {
                    // Delete old background file if it exists and is not the default
                    if ($old_bg && $old_bg['content'] !== 'hero-bg.jpg' && file_exists($old_bg['content'])) {
                        unlink($old_bg['content']);
                    }
                    $success = "Background berhasil diperbarui.";
                } else {
                    // If database update fails, delete the uploaded file
                    if (file_exists($new_file_name)) {
                        unlink($new_file_name);
                    }
                    $error = "Gagal memperbarui database.";
                }
                $stmt->close();
            } else {
                $error = "Gagal mengunggah file.";
            }
        } else {
            $error = "Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    } else {
        $error = "Terjadi kesalahan saat upload file.";
    }
}


// Process ABOUT image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about_image'])) {
    if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === 0) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_name = $_FILES['about_image']['name'];
        $file_tmp = $_FILES['about_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            // Generate unique filename
            $new_file_name = 'about-img-' . time() . '.' . $file_ext;
            
            // Compress the image before uploading
            $compressed_image = null;
            
            if ($file_ext === 'jpg' || $file_ext === 'jpeg') {
                $image = imagecreatefromjpeg($file_tmp);
                imagejpeg($image, $new_file_name, 75); // 75 is the quality (0-100)
                imagedestroy($image);
            } elseif ($file_ext === 'png') {
                $image = imagecreatefrompng($file_tmp);
                imagepng($image, $new_file_name, 6); // 0 is no compression, 9 is maximum compression
                imagedestroy($image);
            } elseif ($file_ext === 'gif') {
                $image = imagecreatefromgif($file_tmp);
                imagegif($image, $new_file_name);
                imagedestroy($image);
            }
            
            // Get current about image before updating
            $stmt = $conn->prepare("SELECT content FROM content WHERE section = 'about_image'");
            $stmt->execute();
            $result = $stmt->get_result();
            $old_img = $result->fetch_assoc();
            
            if (file_exists($new_file_name)) {
                // Update the database with new filename
                $stmt = $conn->prepare("UPDATE content SET content = ? WHERE section = 'about_image'");
                $stmt->bind_param("s", $new_file_name);
                
                if ($stmt->execute()) {
                    // Delete old image file if it exists and is not the default
                    if ($old_img && $old_img['content'] !== 'about-default.jpg' && file_exists($old_img['content'])) {
                        unlink($old_img['content']);
                    }
                    $success = "Gambar Tentang Kami berhasil diperbarui.";
                } else {
                    // If database update fails, delete the uploaded file
                    if (file_exists($new_file_name)) {
                        unlink($new_file_name);
                    }
                    $error = "Gagal memperbarui database.";
                }
                $stmt->close();
            } else {
                $error = "Gagal mengunggah file.";
            }
        } else {
            $error = "Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    } else {
        $error = "Terjadi kesalahan saat upload file.";
    }
}


// Initialize variables
$error = '';
$success = '';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'content';

// Fetch data for each section (for displaying in the admin panel)
$content_result = $conn->query("SELECT * FROM content");
$board_members_result = $conn->query("SELECT * FROM board_members");
$gallery_result = $conn->query("SELECT * FROM gallery");
$news_result = $conn->query("SELECT * FROM news ORDER BY date_posted DESC");
$events_result = $conn->query("SELECT * FROM events ORDER BY date ASC");
$theme_result = $conn->query("SELECT * FROM theme LIMIT 1");
$theme = $theme_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Klub Sarang Glatik</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="admin-style.css">
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
    <div class="admin-container">
        <h1>Admin Dashboard - Klub Sarang Glatik</h1>

        <!-- Login Form -->
        <?php if (!is_logged_in()): ?>
            <form method="POST" action="" class="login-form">
                <h2>Login</h2>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        <?php else: ?>
            <!-- Admin Navigation Menu -->
            <div class="admin-nav">
                <button class="hamburger" aria-label="Toggle menu" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul id="nav-links">
                    <li><a href="?tab=content" <?php echo $active_tab === 'content' ? 'class="active"' : ''; ?>>Konten</a></li>
                    <li><a href="?tab=board" <?php echo $active_tab === 'board' ? 'class="active"' : ''; ?>>Pengurus</a></li>
                    <li><a href="?tab=gallery" <?php echo $active_tab === 'gallery' ? 'class="active"' : ''; ?>>Galeri</a></li>
                    <li><a href="?tab=news" <?php echo $active_tab === 'news' ? 'class="active"' : ''; ?>>Berita</a></li>
                    <li><a href="?tab=events" <?php echo $active_tab === 'events' ? 'class="active"' : ''; ?>>Acara</a></li>
                    <li><a href="?tab=theme" <?php echo $active_tab === 'theme' ? 'class="active"' : ''; ?>>Tema</a></li>
                    <li><a href="?logout=1">Logout</a></li>
                </ul>
            </div>

            <!-- Admin Content -->
            <div id="admin-content">
                <?php if ($active_tab === 'content'): ?>
                       <section id="content">
                        <h2>Edit Konten</h2>
                        
                        <!-- Background Image Upload Form -->
                        <div class="background-image-section">
                            <h3>Background Hero Section</h3>
                            <form method="post" action="" enctype="multipart/form-data" class="background-form">
                                <?php
                                // Fetch current background image
                                $bg_result = $conn->query("SELECT content FROM content WHERE section = 'background_image'");
                                $current_bg = $bg_result->fetch_assoc();
                                if ($current_bg) {
                                    echo '<div class="current-background">';
                                    echo '<p>Background Saat Ini:</p>';
                                    echo '<img src="' . htmlspecialchars($current_bg['content']) . '" alt="Current Background" style="max-width: 200px;">';
                                    echo '<p class="file-name">Nama file: ' . htmlspecialchars($current_bg['content']) . '</p>';
                                    echo '</div>';
                                }
                                ?>
                                <div class="form-group">
                                    <label for="background_image">Upload Background Baru:</label>
                                    <input type="file" id="background_image" name="background_image" accept="image/*" required>
                                    <small>Rekomendasi: Gunakan gambar dengan rasio 16:9 dan ukuran minimal 1920x1080 pixels</small>
                                </div>
                                <button type="submit" name="update_background">Update Background</button>
                            </form>
                        </div>
                        
                    <!-- Content Forms -->
       <!-- About Image Upload Form -->
                        <div class="about-image-section">
                            <h3>Gambar Tentang Kami</h3>
                            <form method="post" action="" enctype="multipart/form-data" class="about-form">
                                <?php
                                // Fetch current about image
                                $about_img_result = $conn->query("SELECT content FROM content WHERE section = 'about_image'");
                                $current_about = $about_img_result->fetch_assoc();
                                if ($current_about) {
                                    echo '<div class="current-about">';
                                    echo '<p>Gambar Saat Ini:</p>';
                                    echo '<img src="' . htmlspecialchars($current_about['content']) . '" alt="Current About Image" style="max-width: 200px;">';
                                    echo '<p class="file-name">Nama file: ' . htmlspecialchars($current_about['content']) . '</p>';
                                    echo '</div>';
                                }
                                ?>
                                <div class="form-group">
                                    <label for="about_image">Upload Gambar Baru:</label>
                                    <input type="file" id="about_image" name="about_image" accept="image/*" required>
                                    <small>Rekomendasi: Gunakan gambar dengan rasio 4:3 atau 1:1</small>
                                </div>
                                <button type="submit" name="update_about_image">Update Gambar</button>
                            </form>
                        </div>

                        <!-- Existing Content Forms -->
                        <?php 
                        // Tentukan ID yang ingin diedit
                        $editable_ids = [1, 2, 3]; // Ganti dengan ID yang sesuai

                        while ($row = $content_result->fetch_assoc()): 
                            if (in_array($row['id'], $editable_ids)): // Hanya tampilkan jika ID ada dalam array editable_ids
                        ?>
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="content_<?php echo $row['id']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row['section'])); ?>:
                                        </label>
                                        <textarea id="content_<?php echo $row['id']; ?>" name="content" rows="3"><?php echo htmlspecialchars($row['content']); ?></textarea>
                                        <input type="hidden" name="section" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="update_content" value="update_<?php echo $row['id']; ?>">Update</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php endwhile; ?>

                    </section>

                <?php elseif ($active_tab === 'board'): ?>
                    <section id="board">
                        <h2>Susunan Pengurus</h2>
                        <form method="post" action="" enctype="multipart/form-data" class="add-form">
                            <div class="form-group">
                                <label for="name">Nama:</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="position">Jabatan:</label>
                                <input type="text" id="position" name="position" required>
                            </div>
                            <div class="form-group">
                                <label for="image">Foto:</label>
                                <input type="file" id="image" name="image" required>
                            </div>
                            <button type="submit" name="add_board_member">Tambah Pengurus</button>
                        </form>
                        <table>
                            <tr>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                            <?php while ($row = $board_members_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td><img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" width="50"></td>
                                    <td>
                                        <form method="post" action="">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_board_member" class="delete-btn"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </section>
                <?php elseif ($active_tab === 'gallery'): ?>
                    <section id="gallery">
                        <h2>Galeri Foto</h2>
                        <form method="post" action="" enctype="multipart/form-data" class="add-form">
                            <div class="form-group">
                                <label for="gallery_image">Foto:</label>
                                <input type="file" id="gallery_image" name="image" required>
                            </div>
                            <div class="form-group">
                                <label for="caption">Caption:</label>
                                <input type="text" id="caption" name="caption" required>
                            </div>
                            <button type="submit" name="add_gallery_image">Tambah Foto</button>
                        </form>
                        <div class="gallery-grid">
                            <?php while ($row = $gallery_result->fetch_assoc()): ?>
                                <div class="gallery-item">
                                    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['caption']); ?>">
                                    <p><?php echo htmlspecialchars($row['caption']); ?></p>
                                    <form method="post" action="">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_gallery_image" class="delete-btn"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </section>
               <?php elseif ($active_tab === 'news'): ?>
    <section id="news">
        <h2>Berita & Kegiatan</h2>
        <form method="post" action="" enctype="multipart/form-data" class="add-form">
            <div class="form-group">
                <label for="news_title">Judul:</label>
                <input type="text" id="news_title" name="title" required>
            </div>
            <div class="form-group">
                <label for="news_content">Konten:</label>
                <textarea id="news_content" name="content" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="news_author">Penulis:</label>
                <input type="text" id="news_author" name="author" required>
            </div>
            <div class="form-group">
                <label for="news_tags">Tags (pisahkan dengan koma):</label>
                <input type="text" id="news_tags" name="tags">
            </div>
            <div class="form-group">
                <label for="news_image">Gambar:</label>
                <input type="file" id="news_image" name="image" required>
            </div>
            <button type="submit" name="add_news">Tambah Berita</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Tags</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $news_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['tags']); ?></td>
                    <td><?php echo $row['date_posted']; ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_news" class="delete-btn"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>
                <?php elseif ($active_tab === 'events'): ?>
                    <section id="events">
                        <h2>Acara</h2>
                        <form method="post" action="" class="add-form">
                            <div class="form-group">
                                <label for="event_name">Nama Acara:</label>
                                <input type="text" id="event_name" name="event_name" required>
                            </div>
                            <div class="form-group">
                                <label for="event_date">Tanggal:</label>
                                <input type="date" id="event_date" name="event_date" required>
                            </div>
                            <div class="form-group">
                                <label for="event_description">Deskripsi:</label>
                                <textarea id="event_description" name="event_description" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="event_location">Lokasi:</label>
                                <input type="text" id="event_location" name="event_location" required>
                            </div>
                            <button type="submit" name="add_event">Tambah Acara</button>
                        </form>
                        <table>
                            <tr>
                                <th>Nama Acara</th>
                                <th>Tanggal</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                            <?php while ($row = $events_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo $row['date']; ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td>
                                        <form method="post" action="">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_event" class="delete-btn"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </section>
                <?php elseif ($active_tab === 'theme'): ?>
                    <section id="theme">
                        <h2>Kustomisasi Tema</h2>
                        <form method="post" action="" class="theme-form">
                            <div class="form-group">
                                <label for="primary_color">Warna Utama:</label>
                                <input type="color" id="primary_color" name="primary_color" value="<?php echo $theme['primary_color']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="secondary_color">Warna Sekunder:</label>
                                <input type="color" id="secondary_color" name="secondary_color" value="<?php echo $theme['secondary_color']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="accent_color">Warna Aksen:</label>
                                <input type="color" id="accent_color" name="accent_color" value="<?php echo $theme['accent_color']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="font">Font:</label>
                                <select id="font" name="font">
                                    <option value="Poppins" <?php echo ($theme['font'] == 'Poppins') ? 'selected' : ''; ?>>Poppins</option>
                                    <option value="Roboto" <?php echo ($theme['font'] == 'Roboto') ? 'selected' : ''; ?>>Roboto</option>
                                    <option value="Open Sans" <?php echo ($theme['font'] == 'Open Sans') ? 'selected' : ''; ?>>Open Sans</option>
                                    <option value="Lato" <?php echo ($theme['font'] == 'Lato') ? 'selected' : ''; ?>>Lato</option>
                                </select>
                            </div>
                            <button type="submit" name="update_theme">Update Tema</button>
                        </form>
                    </section>
                <?php endif; ?>
            </div>
<?php endif; ?>
    
    </div>
    <script>
document.addEventListener("DOMContentLoaded", () => {
  const adminNav = document.querySelector(".admin-nav");
  const hamburger = document.querySelector("#hamburger");
  const navLinks = document.querySelector("#nav-links");
  let lastScrollY = window.scrollY;

      // Prevent scrolling when menu is open
      if (adminNav.classList.contains("active")) {
        document.body.style.overflow = "hidden"
      } else {
        document.body.style.overflow = "auto"
      }

 // Toggle hamburger menu
  hamburger.addEventListener("click", () => {
    hamburger.classList.toggle("active");
    navLinks.classList.toggle("active");
  });

  // Handle scroll behavior
  window.addEventListener("scroll", () => {
    const currentScrollY = window.scrollY;

    if (currentScrollY > 50) {
      adminNav.classList.add("fixed");
    } else {
      adminNav.classList.remove("fixed");
    }

    if (currentScrollY > lastScrollY) {
      adminNav.classList.add("hidden");
    } else {
      adminNav.classList.remove("hidden");
    }

    lastScrollY = currentScrollY;
  });

    });

 


</script>
</body>
</html>