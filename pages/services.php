<?php include '../includes/header.php'; ?>  <!-- Navbar + session check -->
<?php include '../includes/sidebar.php'; ?>  <!-- Menu bên trái -->

<!-- Danh sách dịch vụ -->
<div class="container-fluid p-4 flex-grow-1">
  <!-- Tiêu đề trang + nút Thêm mới (chỉ admin mới thấy) -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Danh sách dịch vụ</h5>
    <?php if ($isAdmin): ?>
    <button class="btn btn-primary btn-sm" onclick="openCreate()">
      <i class="bi bi-plus"></i> Thêm mới
    </button>
    <?php endif; ?>
  </div>

  <!-- Ô tìm kiếm theo tên dịch vụ -->
  <div class="row g-2 mb-3">
    <div class="col-md-4">
      <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Tìm theo tên dịch vụ..." oninput="clearTimeout(_st);_st=setTimeout(loadServices,350)">
    </div>
  </div>

  <!-- Bảng danh sách dịch vụ — JS đổ vào #serviceBody -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th><th>Tên dịch vụ</th><th>Giá</th>
            <th>Mô tả</th><th>Trạng thái</th><th>Thao tác</th>
          </tr>
        </thead>
        <tbody id="serviceBody">
          <tr><td colspan="6" class="text-center py-3">Đang tải...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Popup form Thêm / Sửa dịch vụ -->
<div class="modal fade" id="modalService" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalTitle">Thêm dịch vụ</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="serviceId">  <!-- lưu ID khi đang sửa -->
        <div class="mb-2">
          <label class="form-label small">Tên dịch vụ <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="serviceName">
        </div>
        <div class="mb-2">
          <label class="form-label small">Giá (VNĐ) <span class="text-danger">*</span></label>
          <input type="number" class="form-control form-control-sm" id="servicePrice" min="0" step="1000">
          <div class="invalid-feedback">Vui lòng nhập giá</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Mô tả <span class="text-danger">*</span></label>
          <textarea class="form-control form-control-sm" id="serviceDesc" rows="2"></textarea>
          <div class="invalid-feedback">Vui lòng nhập mô tả</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Trạng thái</label>
          <select class="form-select form-select-sm" id="serviceStatus">
            <option value="1">Hoạt động</option>
            <option value="0">Ngưng</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary btn-sm" onclick="saveService()">Lưu</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modal = new bootstrap.Modal(document.getElementById('modalService'));
let _st;

// Định dạng số thành tiền VNĐ (ví dụ: 500000 → 500.000 ₫)
const fmt = n => Number(n).toLocaleString('vi-VN') + ' ₫';

// Gọi API lấy danh sách dịch vụ theo từ khóa, render ra bảng
async function loadServices() {
  const search = document.getElementById('searchInput').value;
  const data   = await fetch(`/api/services.php?action=list&search=${encodeURIComponent(search)}`).then(r => r.json());
  const tbody  = document.getElementById('serviceBody');

  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Không có dữ liệu</td></tr>';
    return;
  }
  tbody.innerHTML = data.map((s, i) => `
    <tr>
      <td>${i+1}</td>
      <td>${s.name}</td>
      <td class="text-end">${fmt(s.price)}</td>
      <td>${s.description || '—'}</td>
      <td>${s.status == 1
          ? '<span class="badge bg-success">Hoạt động</span>'
          : '<span class="badge bg-secondary">Ngưng</span>'}</td>
      <td>
        <?php if ($isAdmin): ?>
        <button class="btn btn-outline-primary btn-sm py-0" onclick="editService(${s.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-outline-danger btn-sm py-0"  onclick="deleteService(${s.id})"><i class="bi bi-trash"></i></button>
        <?php else: ?>
        <span class="text-muted small">Chỉ xem</span>
        <?php endif; ?>
      </td>
    </tr>`).join('');
}

// Mở popup thêm mới, reset form sạch
function openCreate() {
  document.getElementById('serviceId').value    = '';
  document.getElementById('serviceName').value  = '';
  document.getElementById('servicePrice').value = '';
  document.getElementById('serviceDesc').value  = '';
  document.getElementById('serviceStatus').value = '1';
  document.getElementById('modalTitle').textContent = 'Thêm dịch vụ';
  modal.show();
}

// Lấy dữ liệu dịch vụ từ API, điền vào form, mở popup
async function editService(id) {
  const s = await fetch(`/api/services.php?action=get&id=${id}`).then(r => r.json());
  document.getElementById('serviceId').value     = s.id;
  document.getElementById('serviceName').value   = s.name;
  document.getElementById('servicePrice').value  = s.price;
  document.getElementById('serviceDesc').value   = s.description || '';
  document.getElementById('serviceStatus').value = s.status;
  document.getElementById('modalTitle').textContent = 'Sửa dịch vụ';
  modal.show();
}

// Kiểm tra các ô bắt buộc — highlight đỏ nếu trống
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

// Lưu dịch vụ: tạo mới hoặc cập nhật
async function saveService() {
  const id = document.getElementById('serviceId').value;
  if (!checkFields('serviceName','servicePrice','serviceDesc')) return;
  const payload = {
    action:      id ? 'update' : 'create',
    id,
    name:        document.getElementById('serviceName').value.trim(),
    price:       parseFloat(document.getElementById('servicePrice').value) || 0,
    description: document.getElementById('serviceDesc').value.trim(),
    status:      parseInt(document.getElementById('serviceStatus').value),
  };

  const data = await fetch('/api/services.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
  }).then(r => r.json());
  if (data.success) { modal.hide(); loadServices(); }
  else alert('Lỗi: ' + data.error);
}

// Xóa dịch vụ sau khi xác nhận
async function deleteService(id) {
  if (!confirm('Xóa dịch vụ này?')) return;
  const data = await fetch('/api/services.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action:'delete', id })
  }).then(r => r.json());
  if (data.success) loadServices();
}

loadServices();
</script>
</body></html>
