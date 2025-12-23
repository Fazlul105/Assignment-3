<?php
require_once 'config.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_panel.php');
    exit();
}

$appointment_id = intval($_POST['appointment_id']);
$appointment_date = $_POST['appointment_date'];
$mechanic_id = intval($_POST['mechanic_id']);
$status = $_POST['status'];

$conn = getDBConnection();


if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
    $_SESSION['error'] = "❌ Appointment date cannot be in the past";
    header('Location: admin_panel.php');
    exit();
}


$valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    $_SESSION['error'] = "❌ Invalid status selected";
    header('Location: admin_panel.php');
    exit();
}


if ($status !== 'cancelled') {
    $availQuery = "SELECT m.max_appointments, COUNT(a.id) as current_count
                   FROM mechanics m
                   LEFT JOIN appointments a ON m.id = a.mechanic_id 
                   AND a.appointment_date = ? AND a.status != 'cancelled' AND a.id != ?
                   WHERE m.id = ?
                   GROUP BY m.id";
    $stmt = $conn->prepare($availQuery);
    $stmt->bind_param("sii", $appointment_date, $appointment_id, $mechanic_id);
    $stmt->execute();
    $availResult = $stmt->get_result();

    if ($availResult->num_rows === 0) {
        $_SESSION['error'] = "❌ Invalid mechanic selected";
        header('Location: admin_panel.php');
        exit();
    }

    $availData = $availResult->fetch_assoc();
    if ($availData['current_count'] >= $availData['max_appointments']) {
        $_SESSION['error'] = "❌ This mechanic is fully booked for the selected date (Max: {$availData['max_appointments']} appointments)";
        header('Location: admin_panel.php');
        exit();
    }
}


$updateQuery = "UPDATE appointments 
                SET appointment_date = ?, mechanic_id = ?, status = ?
                WHERE id = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("sisi", $appointment_date, $mechanic_id, $status, $appointment_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "✅ Appointment #$appointment_id updated successfully!";
} else {
    $_SESSION['error'] = "❌ Failed to update appointment. Please try again.";
}

$conn->close();
header('Location: admin_panel.php');
exit();
?>