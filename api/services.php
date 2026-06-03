<?php
session_start();                           // cần session để kiểm tra quyền
header('Content-Type: application/json'); // trả về JSON
require_once '../config/db.php';           // kết nối CSDL

// Chặn POST nếu không phải admin — chỉ admin mới được thêm/sửa/xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Không có quyền thực hiện thao tác này!']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    // Danh sách dịch vụ, có tìm kiếm theo tên
    if ($action === 'list') {
        $search = '%' . ($conn->real_escape_string($_GET['search'] ?? '')) . '%';
        $stmt   = $conn->prepare("SELECT * FROM services WHERE name LIKE ? ORDER BY created_at DESC");
        $stmt->bind_param('s', $search);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));

    // Chi tiết 1 dịch vụ (dùng khi mở form sửa)
    } elseif ($action === 'get') {
        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->bind_param('i', intval($_GET['id']));
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_assoc());
    }

} elseif ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true); // đọc JSON gửi lên
    $action = $body['action'] ?? '';

    // Tạo dịch vụ mới
    if ($action === 'create') {
        $stmt = $conn->prepare("INSERT INTO services (name, price, description, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sdsi', $body['name'], $body['price'], $body['description'], $body['status']);
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Cập nhật dịch vụ
    } elseif ($action === 'update') {
        $stmt = $conn->prepare("UPDATE services SET name=?, price=?, description=?, status=? WHERE id=?");
        $stmt->bind_param('sdsii', $body['name'], $body['price'], $body['description'], $body['status'], $body['id']);
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Xóa dịch vụ
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
        $stmt->bind_param('i', $body['id']);
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);
    }
}
