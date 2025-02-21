<?php
header("Content-type: text/css");
require_once 'db_connect.php';

// Fetch background image from database
$bg_result = $conn->query("SELECT content FROM content WHERE section = 'background_image'");
$background = $bg_result->fetch_assoc();
$background_image = $background ? $background['content'] : 'hero-bg.jpg';
?>

.hero-section {
  position: relative;
  height: 100vh;
  background-image: url("<?php echo $background_image; ?>");
  background-size: cover;
  background-position: center;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}