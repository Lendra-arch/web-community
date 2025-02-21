<?php
include('db_connection.php');

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT title, image, content FROM news WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);

$stmt->close();
$conn->close();
?>