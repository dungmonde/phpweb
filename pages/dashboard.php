<?php include '../includes/header.php'; ?>  <!-- Navbar + session check -->
<?php include '../includes/sidebar.php'; ?>  <!-- Menu bên trái -->

<?php
// Truy vấn số liệu hiển thị trên các ô thống kê
$totalCustomers  = $conn->query("SELECT COUNT(*) FROM customer")->fetch_row()[0];
$totalServices   = $conn->query("SELECT COUNT(*) FROM services WHERE status=1")->fetch_row()[0];
$pendingRepairs  = $conn->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetch_row()[0];
$doneRepairs     = $conn->query("SELECT COUNT(*) FROM appointments WHERE status='done'")->fetch_row()[0];
$todayRepairs    = $conn->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date)=CURDATE()")->fetch_row()[0];
// Lấy 6 phiếu sửa chữa mới nhất — JOIN nhiều bảng để lấy tên khách, nhân viên, dịch vụ
$recentRepairs   = $conn->query("
    SELECT a.*, c.full_name AS customer_name, u.full_name AS staff_name, s.name AS service_name
    FROM appointments a
    LEFT JOIN customer c ON a.customer_id = c.id
    LEFT JOIN users u    ON a.user_id = u.id
    LEFT JOIN services s ON a.service_id = s.id
    ORDER BY a.created_at DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);
// Lấy 5 khách hàng đăng ký gần nhất
$recentCustomers = $conn->query("SELECT * FROM customer ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid p-4 flex-grow-1">
  <div class="mb-4">
    <h5 class="page-title mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Tổng quan hệ thống</h5>
    <span class="text-muted small">Xin chào, <strong><?= htmlspecialchars($authUser['full_name']) ?></strong> — <?= date('d/m/Y') ?></span>
  </div>

  <!-- 4 ô thống kê: Khách hàng / Chờ sửa / Hoàn thành / Dịch vụ -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
        <div class="value"><?= $totalCustomers ?></div>
        <div class="label">Khách hàng</div>
        <i class="bi bi-people-fill bg-icon"></i>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
        <div class="value"><?= $pendingRepairs ?></div>
        <div class="label">Chờ sửa chữa</div>
        <i class="bi bi-hourglass-split bg-icon"></i>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669)">
        <div class="value"><?= $doneRepairs ?></div>
        <div class="label">Đã hoàn thành</div>
        <i class="bi bi-check-circle-fill bg-icon"></i>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
        <div class="value"><?= $totalServices ?></div>
        <div class="label">Dịch vụ hoạt động</div>
        <i class="bi bi-cpu-fill bg-icon"></i>
      </div>
    </div>
  </div>

  <!-- Banner thông báo số phiếu hôm nay -->
  <div class="alert d-flex align-items-center gap-3 mb-4 border-0 shadow-sm" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:12px">
    <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center text-white" style="width:44px;height:44px;flex-shrink:0">
      <i class="bi bi-calendar-check fs-5"></i>
    </div>
    <div>
      <div class="fw-semibold text-primary">Hôm nay có <strong><?= $todayRepairs ?></strong> phiếu sửa chữa</div>
      <div class="text-muted small">Ngày <?= date('d/m/Y') ?></div>
    </div>
    <a href="/pages/appointments.php" class="btn btn-primary btn-sm ms-auto px-3">Xem phiếu</a>
  </div>

  <!-- 2 bảng bên dưới: phiếu sửa gần đây (trái) + khách hàng mới (phải) -->
  <div class="row g-3">
    <!-- Bảng phiếu sửa chữa gần nhất -->
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
          <span class="fw-semibold"><i class="bi bi-wrench-adjustable me-2 text-primary"></i>Phiếu sửa chữa gần đây</span>
          <a href="/pages/appointments.php" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead>
              <tr><th>Khách hàng</th><th>Dịch vụ</th><th>Ngày hẹn</th><th>Trạng thái</th></tr>
            </thead>
            <tbody>
              <?php if (!$recentRepairs): ?>
              <tr><td colspan="4" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
              <?php else: ?>
              <?php
              // Map trạng thái → màu badge + nhãn hiển thị
              $statusMap = [
                'pending'   => ['bg-warning text-dark', 'Chờ xử lý'],
                'confirmed' => ['bg-info text-dark',    'Đã tiếp nhận'],
                'done'      => ['bg-success',           'Hoàn thành'],
                'cancelled' => ['bg-secondary',         'Đã hủy'],
              ];
              foreach ($recentRepairs as $r):
                [$cls, $lbl] = $statusMap[$r['status']] ?? ['bg-secondary','—'];
              ?>
              <tr>
                <td>
                  <div class="fw-semibold" style="font-size:.85rem"><?= htmlspecialchars($r['customer_name'] ?? '—') ?></div>
                  <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($r['staff_name'] ?? '—') ?></div>
                </td>
                <td class="small"><?= htmlspecialchars($r['service_name'] ?? '—') ?></td>
                <td class="small text-muted"><?= $r['appointment_date'] ? date('d/m/Y H:i', strtotime($r['appointment_date'])) : '—' ?></td>
                <td><span class="badge bg-<?= $cls ?>"><?= $lbl ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Bảng khách hàng mới đăng ký nhất -->
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
          <span class="fw-semibold"><i class="bi bi-person-badge me-2 text-primary"></i>Khách hàng mới</span>
          <a href="/pages/customers.php" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead><tr><th>Họ tên</th><th>Điện thoại</th><th>Trạng thái</th></tr></thead>
            <tbody>
              <?php if (!$recentCustomers): ?>
              <tr><td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
              <?php else: ?>
              <?php foreach ($recentCustomers as $c): ?>
              <tr>
                <td>
                  <div class="fw-semibold" style="font-size:.85rem"><?= htmlspecialchars($c['full_name']) ?></div>
                  <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($c['email'] ?? '') ?></div>
                </td>
                <td class="small"><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                <td><?= $c['status'] == 1 ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Ngưng</span>' ?></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
