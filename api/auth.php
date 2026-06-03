<?php
session_start();                           // khởi tạo session để lưu thông tin đăng nhập
header('Content-Type: application/json'); // trả về JSON
require_once '../config/db.php';           // kết nối CSDL

$body   = json_decode(file_get_contents('php://input'), true); // đọc JSON gửi lên
$action = $body['action'] ?? ''; // login hoặc logout

// Xử lý đăng nhập: kiểm tra username + mật khẩu MD5 + trạng thái tài khoản
if ($action === 'login') {
    $username = $body['username'] ?? '';
    $password = $body['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, full_name, username, role FROM users
         WHERE username = ? AND password = MD5(?) AND status = 1"
    );
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $_SESSION['user'] = $user; // lưu thông tin user vào session
        echo json_encode(['success' => true, 'role' => $user['role']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Sai username hoặc mật khẩu!']);
    }

// Xử lý đăng xuất: huỷ toàn bộ session
} elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
}
