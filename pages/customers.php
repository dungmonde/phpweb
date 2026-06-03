<?php include '../includes/header.php'; ?>  <!-- Navbar + session check -->
<?php include '../includes/sidebar.php'; ?>  <!-- Menu bên trái -->

<div class="container-fluid p-4 flex-grow-1">
  <!-- Tiêu đề trang + nút Thêm mới -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Danh sách khách hàng</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCustomer">
      <i class="bi bi-plus"></i> Thêm mới
    </button>
  </div>

  <!-- Thanh tìm kiếm + dropdown lọc theo loại KH và trạng thái -->
  <div class="row g-2 mb-3">
    <div class="col-md-4">
      <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Tìm theo tên, số điện thoại..." oninput="clearTimeout(_st);_st=setTimeout(loadCustomers,350)">
    </div>
    <div class="col-md-3">
      <select id="filterType" class="form-select form-select-sm" onchange="loadCustomers()">
        <option value="">-- Tất cả loại --</option>
        <?php
          $types = $conn->query("SELECT * FROM customer_type");
          while ($t = $types->fetch_assoc()):
        ?>
          <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select id="filterStatus" class="form-select form-select-sm" onchange="loadCustomers()">
        <option value="">-- Tất cả trạng thái --</option>
        <option value="1">Hoạt động</option>
        <option value="0">Ngưng</option>
      </select>
    </div>
  </div>

  <!-- Bảng danh sách khách hàng — dữ liệu được JS đổ vào #customerBody -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover table-sm mb-0" id="customerTable">
        <thead class="table-light">
          <tr>
            <th>#</th><th>Họ tên</th><th>Điện thoại</th>
            <th>Email</th><th>Địa chỉ</th><th>Loại KH</th><th>Trạng thái</th><th>Thao tác</th>
          </tr>
        </thead>
        <tbody id="customerBody">
          <tr><td colspan="8" class="text-center py-3">Đang tải...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Popup form dùng chung cho cả Thêm mới lẫn Sửa -->
<div class="modal fade" id="modalCustomer" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalTitle">Thêm khách hàng</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="customerId"> <!-- lưu ID khi đang sửa, rỗng khi thêm mới -->
        <div class="mb-2">
          <label class="form-label small">Họ tên <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="customerName">
          <div class="invalid-feedback">Vui lòng nhập họ tên</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Điện thoại <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="customerPhone">
          <div class="invalid-feedback">Vui lòng nhập số điện thoại</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control form-control-sm" id="customerEmail">
          <div class="invalid-feedback">Vui lòng nhập email</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Loại khách hàng</label>
          <select class="form-select form-select-sm" id="customerType">
            <?php
              $types2 = $conn->query("SELECT * FROM customer_type");
              while ($t = $types2->fetch_assoc()):
            ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Địa chỉ <span class="text-danger">*</span></label>
          <textarea class="form-control form-control-sm" id="customerAddress" rows="2"></textarea>
          <div class="invalid-feedback">Vui lòng nhập địa chỉ</div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary btn-sm" onclick="saveCustomer()">Lưu</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modal = new bootstrap.Modal(document.getElementById('modalCustomer')); // khởi tạo popup Bootstrap
let _st; // biến timeout dùng cho debounce tìm kiếm

// Gọi API lấy danh sách, render ra bảng
async function loadCustomers() {
  const search = document.getElementById('searchInput').value;
  const type   = document.getElementById('filterType').value;
  const status = document.getElementById('filterStatus').value;

  const res  = await fetch(`/api/customers.php?action=list&search=${encodeURIComponent(search)}&type=${type}&status=${status}`);
  const data = await res.json();
  const tbody = document.getElementById('customerBody');

  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Không có dữ liệu</td></tr>';
    return;
  }

  tbody.innerHTML = data.map((c, i) => `
    <tr>
      <td>${i+1}</td>
      <td>${c.full_name}</td>
      <td>${c.phone || '—'}</td>
      <td>${c.email || '—'}</td>
      <td>${c.address || '—'}</td>
      <td><span class="badge bg-info text-dark">${c.type_name || '—'}</span></td>
      <td>${c.status == 1
          ? '<span class="badge bg-success">Hoạt động</span>'
          : '<span class="badge bg-secondary">Ngưng</span>'}</td>
      <td>
        <button class="btn btn-outline-primary btn-sm py-0" onclick="editCustomer(${c.id})">
          <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-outline-danger btn-sm py-0" onclick="deleteCustomer(${c.id})">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>`).join('');
}

// Kiểm tra các ô bắt buộc — highlight đỏ nếu để trống
function checkFields(...ids) {
  let ok = true;
  ids.forEach(id => {
    const el = document.getElementById(id);
    const empty = !el.value.trim();
    el.classList.toggle('is-invalid', empty);
    if (empty) ok = false;
  });
  return ok;
}

// Lưu khách hàng: tạo mới hoặc cập nhật tùy vào có ID hay không
async function saveCustomer() {
  const id = document.getElementById('customerId').value;
  if (!checkFields('customerName','customerPhone','customerEmail','customerAddress')) return;
  const payload = {
    action:           id ? 'update' : 'create',
    id:               id,
    full_name:        document.getElementById('customerName').value.trim(),
    phone:            document.getElementById('customerPhone').value.trim(),
    email:            document.getElementById('customerEmail').value.trim(),
    customer_type_id: document.getElementById('customerType').value,
    address:          document.getElementById('customerAddress').value.trim(),
  };

  const res  = await fetch('/api/customers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  if (data.success) { modal.hide(); loadCustomers(); }
  else alert('Lỗi: ' + data.error);
}

// Lấy dữ liệu khách hàng từ API rồi điền vào form, mở popup
async function editCustomer(id) {
  const res  = await fetch(`/api/customers.php?action=get&id=${id}`);
  const c    = await res.json();
  document.getElementById('customerId').value        = c.id;
  document.getElementById('customerName').value      = c.full_name;
  document.getElementById('customerPhone').value     = c.phone;
  document.getElementById('customerEmail').value     = c.email;
  document.getElementById('customerType').value      = c.customer_type_id;
  document.getElementById('customerAddress').value   = c.address;
  document.getElementById('modalTitle').textContent  = 'Sửa khách hàng';
  modal.show();
}

// Xóa khách hàng sau khi xác nhận
async function deleteCustomer(id) {
  if (!confirm('Xóa khách hàng này?')) return;
  const res  = await fetch('/api/customers.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id })
  });
  const data = await res.json();
  if (data.success) loadCustomers();
}

// Dọn sạch form mỗi khi nhấn nút Thêm mới (không reset khi mở từ nút sửa)
document.getElementById('modalCustomer').addEventListener('show.bs.modal', function(e) {
  if (!e.relatedTarget) return;
  document.getElementById('customerId').value = '';
  document.getElementById('customerName').value = '';
  document.getElementById('customerPhone').value = '';
  document.getElementById('customerEmail').value = '';
  document.getElementById('customerAddress').value = '';
  document.getElementById('modalTitle').textContent = 'Thêm khách hàng';
});

loadCustomers();
</script>
</body></html>