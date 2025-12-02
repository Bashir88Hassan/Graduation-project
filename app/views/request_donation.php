<?php
session_start();
require_once '../Core/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Forms/login.php");
    exit;
}

if (!isset($_GET['request_id'])) {
    die("رقم الطلب غير محدد.");
}

$request_id = intval($_GET['request_id']);

// جلب معلومات الطلب
$stmt = $conn->prepare("
    SELECT hospital_name, city, urgency 
    FROM blood_requests 
    WHERE id = ? AND create_by = ?
");
$stmt->bind_param("ii", $request_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("الطلب غير موجود أو لا تملك صلاحية عرضه.");
}

$request = $result->fetch_assoc();
$stmt->close();

// جلب التبرعات المرتبطة بالطلب
$donations = [];
$stmt = $conn->prepare("
    SELECT d.id, u.name, u.phone, d.donated_at, d.status
    FROM donations d
    JOIN users u ON d.user_id = u.id
    WHERE d.request_id = ?
    ORDER BY d.donated_at DESC
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $donations[] = $row;
}

$stmt->close();
// تأكيد التبرع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_donation_id'])) {
    $donation_id = intval($_POST['confirm_donation_id']);

    // التأكد أن التبرع يخص الطلب الحالي
    $checkStmt = $conn->prepare("
        SELECT d.id
        FROM donations d
        JOIN blood_requests r ON d.request_id = r.id
        WHERE d.id = ? AND r.create_by = ?
    ");
    $checkStmt->bind_param("ii", $donation_id, $_SESSION['user_id']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
    // تحديث حالة التبرع
    $updateStmt = $conn->prepare("UPDATE donations SET status = 'completed', donated_at = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $donation_id);
    $updateStmt->execute();
    $updateStmt->close();

    // جلب user_id الخاص بالمتبرع
    $userIdStmt = $conn->prepare("SELECT user_id FROM donations WHERE id = ?");
    $userIdStmt->bind_param("i", $donation_id);
    $userIdStmt->execute();
    $userIdStmt->bind_result($donor_user_id);
    $userIdStmt->fetch();
    $userIdStmt->close();

    // إضافة النقاط للمستخدم (المتبرع الحقيقي)
    $pointsToAdd = 10;
    $updatePointsQuery = "UPDATE users SET points = points + ? WHERE id = ?";
    $stmt_points = $conn->prepare($updatePointsQuery);
    $stmt_points->bind_param("ii", $pointsToAdd, $donor_user_id);
    $stmt_points->execute();
    $stmt_points->close();
}

}
?>
