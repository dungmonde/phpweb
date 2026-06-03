<?php
header('Content-Type: application/json'); // trả về JSON
require_once '../config/db.php';           // kết nối CSDL

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    // Danh sách phiếu, có lọc theo trạng thái và ngày
    if ($action === 'list') {
        $status = $_GET['status'] ?? '';
        $date   = $_GET['date']   ?? '';
        $where  = "WHERE 1=1";
        $params = [];
        $types  = '';
        if ($status !== '') { $where .= " AND a.status = ?"; $params[] = $status; $types .= 's'; }
        if ($date   !== '') { $where .= " AND DATE(a.appointment_date) = ?"; $params[] = $date; $types .= 's'; }

        // JOIN 3 bảng để hiển thị tên khách, nhân viên, dịch vụ trực tiếp
        $sql = "SELECT a.*, c.full_name AS customer_name, u.full_name AS staff_name, s.name AS service_name
                FROM appointments a
                LEFT JOIN customer c ON a.customer_id = c.id
                LEFT JOIN users u    ON a.user_id = u.id
                LEFT JOIN services s ON a.service_id = s.id
                $where ORDER BY a.appointment_date DESC";

        if ($types) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        } else {
            echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
        }

    // Chi tiết 1 phiếu (dùng khi mở form sửa)
    } elseif ($action === 'get') {
        $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->bind_param('i', intval($_GET['id']));
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_assoc());

    // 3 action dưới để đổ dữ liệu vào dropdown trong form
    } elseif ($action === 'customers') {
        echo json_encode($conn->query("SELECT id, full_name FROM customer WHERE status=1 ORDER BY full_name")->fetch_all(MYSQLI_ASSOC));

    } elseif ($action === 'users') {
        echo json_encode($conn->query("SELECT id, full_name FROM users WHERE status=1 ORDER BY full_name")->fetch_all(MYSQLI_ASSOC));

    } elseif ($action === 'services') {
        echo json_encode($conn->query("SELECT id, name FROM services WHERE status=1 ORDER BY name")->fetch_all(MYSQLI_ASSOC));
    }

} elseif ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true); // đọc JSON gửi lên
    $action = $body['action'] ?? '';

    // Tạo phiếu mới
    if ($action === 'create') {
        $stmt = $conn->prepare(
            "INSERT INTO appointments (customer_id, user_id, service_id, title, appointment_date, status, note)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iiissss',
            $body['customer_id'], $body['user_id'], $body['service_id'],
            $body['title'], $body['appointment_date'], $body['status'], $body['note']
        );
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Cập nhật phiếu
    } elseif ($action === 'update') {
        $stmt = $conn->prepare(
            "UPDATE appointments SET customer_id=?, user_id=?, service_id=?, title=?, appointment_date=?, status=?, note=?
             WHERE id=?"
        );
        $stmt->bind_param('iiissssi',
            $body['customer_id'], $body['user_id'], $body['service_id'],
            $body['title'], $body['appointment_date'], $body['status'], $body['note'], $body['id']
        );
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);

    // Xóa phiếu
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id=?");
        $stmt->bind_param('i', $body['id']);
        echo json_encode(['success' => $stmt->execute(), 'error' => $conn->error]);
    }
}
