<?php
header('Content-Type: application/json'); // trả về JSON
require_once '../config/db.php';           // kết nối CSDL

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    // Danh sách user, có tìm kiếm và lọc theo vai trò
    if ($action === 'list') {
        $search = '%' . ($conn->real_escape_string($_GET['search'] ?? '')) . '%';
        $role   = $_GET['role'] ?? '';
        $where  = "WHERE (full_name LIKE ? OR username LIKE ?)";
        $params = [$search, $search];
        $types  = 'ss';
        if ($role !== '') { $where .= " AND role = ?"; $params[] = $role; $types .= 's'; }
        // Không trả về password — chỉ lấy các cột cần thiết
        $stmt = $conn->prepare("SELECT id, full_name, username, role, status, created_at FROM users $where ORDER BY created_at DESC");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));

    // Chi tiết 1 user (dùng khi mở form sửa)
    } elseif ($action === 'get') {
        $stmt = $conn->prepare("SELECT id, full_name, username, role, status FROM users WHERE id = ?");
        $stmt->bind_param('i', intval($_GET['id']));
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_assoc());
    }

} elseif ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true); // đọc JSON gửi lên
    $action = $body['action'] ?? '';

    // Tạo user mới, mật khẩu được mã hóa MD5
    if ($action === 'create') {
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, MD5(?), ?)");
        $stmt->bind_param('ssss', $body['full_name'], $body['username'], $body['password'], $body['role']);
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Cập nhật user — nếu có đổi mật khẩu thì cập nhật luôn, nếu không thì giữ nguyên
    } elseif ($action === 'update') {
        if (!empty($body['password'])) {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, password=MD5(?), role=?, status=? WHERE id=?");
            $stmt->bind_param('ssssii', $body['full_name'], $body['username'], $body['password'], $body['role'], $body['status'], $body['id']);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, role=?, status=? WHERE id=?");
            $stmt->bind_param('sssii', $body['full_name'], $body['username'], $body['role'], $body['status'], $body['id']);
        }
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Xóa user
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param('i', $body['id']);
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);
    }
}
