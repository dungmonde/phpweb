<?php
header('Content-Type: application/json'); // trả về JSON cho mọi response
require_once '../config/db.php';           // kết nối CSDL

$method = $_SERVER['REQUEST_METHOD']; // GET hoặc POST
$action = $_GET['action'] ?? '';      // hành động: list | get | create | update | delete

if ($method === 'GET') {
    // Lấy danh sách có tìm kiếm + lọc theo loại KH và trạng thái
    if ($action === 'list') {
        $search = '%' . ($conn->real_escape_string($_GET['search'] ?? '')) . '%';
        $type   = intval($_GET['type']   ?? 0);
        $status = $_GET['status'] ?? '';

        $where = "WHERE (c.full_name LIKE ? OR c.phone LIKE ?)";
        $params = [$search, $search];
        $types  = 'ss';

        if ($type) {
            $where   .= " AND c.customer_type_id = ?";
            $params[] = $type;
            $types   .= 'i';
        }
        if ($status !== '') {
            $where   .= " AND c.status = ?";
            $params[] = intval($status);
            $types   .= 'i';
        }

        // JOIN với customer_type để lấy thêm tên loại KH
        $sql  = "SELECT c.*, ct.name AS type_name
                 FROM customer c
                 LEFT JOIN customer_type ct ON c.customer_type_id = ct.id
                 $where
                 ORDER BY c.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));

    // Lấy chi tiết 1 khách hàng theo ID (dùng khi mở form sửa)
    } elseif ($action === 'get') {
        $id   = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM customer WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_assoc());
    }

} elseif ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true); // đọc JSON gửi lên từ JS
    $action = $body['action'] ?? '';

    // Thêm khách hàng mới
    if ($action === 'create') {
        $stmt = $conn->prepare(
            "INSERT INTO customer (customer_type_id, full_name, phone, email, address)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('issss',
            $body['customer_type_id'], $body['full_name'],
            $body['phone'], $body['email'], $body['address']
        );
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Cập nhật thông tin khách hàng
    } elseif ($action === 'update') {
        $stmt = $conn->prepare(
            "UPDATE customer SET customer_type_id=?, full_name=?, phone=?, email=?, address=?
             WHERE id=?"
        );
        $stmt->bind_param('issssi',
            $body['customer_type_id'], $body['full_name'],
            $body['phone'], $body['email'], $body['address'], $body['id']
        );
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Xóa khách hàng
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM customer WHERE id=?");
        $stmt->bind_param('i', $body['id']);
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);
    }
}