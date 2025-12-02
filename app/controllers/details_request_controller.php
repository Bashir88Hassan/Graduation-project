<?php
session_start();
require_once '../../Core/db.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('رقم الطلب غير صالح');
}

$request_id = intval($_GET['id']);


$stmt = $conn->prepare("SELECT * FROM blood_requests WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('طلب التبرع غير موجود');
}

$request = $result->fetch_assoc();

// /////////////////////



// تأكد من أن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    die('يجب تسجيل الدخول أولاً');
}

$user_id = $_SESSION['user_id'];
$request_id = $_GET['id'];
$confirmation_message = '';
if(isset($_POST['will_blood'])) {
    // التحقق من التسجيل المسبق
    $check = $conn->prepare("SELECT * FROM donations WHERE user_id = ? AND request_id = ?");
    $check->bind_param("ii", $user_id, $request_id);
    $check->execute();
    $result = $check->get_result();
  

    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO donations (user_id, request_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $user_id, $request_id);
        $stmt->execute();
        $confirmation_message = "✅ تم تسجيل استعدادك للتبرع. شكرًا لإنسانيتك ❤️";
    } else {
        $confirmation_message = "⚠️ لقد سجلت تبرعك مسبقًا.";
    }
}

include __DIR__ . '../views/details_request.php';
?>