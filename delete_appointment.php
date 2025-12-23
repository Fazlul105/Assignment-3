<?php
require_once 'config.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$appointment_id = intval($_POST['appointment_id']);

$conn = getDBConnection();


$query = "DELETE FROM appointments WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete appointment']);
}

$conn->close();
?>