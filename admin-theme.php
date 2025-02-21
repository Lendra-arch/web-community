<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    exit('Unauthorized access');
}

$error = '';
$success = '';

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

$theme_result = $conn->query("SELECT * FROM theme LIMIT 1");
$theme = $theme_result->fetch_assoc();
?>

<section id="theme">
    <h2>Kustomisasi Tema</h2>
    <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
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

