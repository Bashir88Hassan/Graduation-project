<?php
require_once '../../Core/db.php'; // اتصال قاعدة البيانات
$city = $_GET['city'] ?? '';
$blood_type = $_GET['blood_type'] ?? '';
$status = $_GET['status'] ?? '';

$query = "SELECT * FROM blood_requests WHERE 1=1 ";
$params = [];
$types = "";

if (!empty($city)) {
    $query .= " AND city LIKE ?";
    $params[] = "%$city%";
    $types .= "s";
}

if (!empty($blood_type)) {
    $query .= " AND blood_type = ?";
    $params[] = $blood_type;
    $types .= "s";
}

if (!empty($status)) {
    $query .= " AND urgency = ?";
    $params[] = $status;
    $types .= "s";
}

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
