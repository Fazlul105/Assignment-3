<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}


$client_name = trim($_POST['client_name']);
$address = trim($_POST['address']);
$phone = trim($_POST['phone']);
$car_license = trim($_POST['car_license']);
$car_engine = trim($_POST['car_engine']);
$appointment_date = $_POST['appointment_date'];
$mechanic_id = intval($_POST['mechanic_id']);

$conn = getDBConnection();


$errors = [];


if (!preg_match("/^[a-zA-Z ]{2,100}$/", $client_name)) {
    $errors[] = "Name should contain only letters and spaces";
}


if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
    $errors[] = "Phone should contain 10-15 digits only";
}


if (!preg_match("/^[A-Z0-9]{5,20}$/i", $car_engine)) {
    $errors[] = "Car engine number should be alphanumeric (5-20 characters)";
}


if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
    $errors[] = "Appointment date cannot be in the past";
}


$checkQuery = "SELECT id FROM appointments 
               WHERE phone = ? AND appointment_date = ? AND status != 'cancelled'";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ss", $phone, $appointment_date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $errors[] = "You already have an appointment on this date";
}


$availQuery = "SELECT m.max_appointments, COUNT(a.id) as current_count
               FROM mechanics m
               LEFT JOIN appointments a ON m.id = a.mechanic_id 
               AND a.appointment_date = ? AND a.status != 'cancelled'
               WHERE m.id = ?
               GROUP BY m.id";
$stmt = $conn->prepare($availQuery);
$stmt->bind_param("si", $appointment_date, $mechanic_id);
$stmt->execute();
$availResult = $stmt->get_result();

if ($availResult->num_rows === 0) {
    $errors[] = "Invalid mechanic selected";
} else {
    $availData = $availResult->fetch_assoc();
    if ($availData['current_count'] >= $availData['max_appointments']) {
        $errors[] = "This mechanic is fully booked for the selected date";
    }
}


if (!empty($errors)) {
    $_SESSION['error'] = implode(", ", $errors);
    header('Location: index.php');
    exit();
}


$insertQuery = "INSERT INTO appointments 
                (client_name, address, phone, car_license, car_engine, appointment_date, mechanic_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("ssssssi", $client_name, $address, $phone, $car_license, $car_engine, $appointment_date, $mechanic_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Appointment booked successfully! Your appointment ID is: " . $stmt->insert_id;
} else {
    $_SESSION['error'] = "Failed to book appointment. Please try again.";
}

$conn->close();
header('Location: index.php');
exit();
?>