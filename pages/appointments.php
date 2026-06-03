<?php include '../includes/header.php'; ?>  <!-- Navbar + session check -->
<?php include '../includes/sidebar.php'; ?>  <!-- Menu bên trái -->

<div class="container-fluid p-4 flex-grow-1">
  <!-- Tiêu đề trang + nút Thêm mới -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Lịch hẹn</h5>
    <button class="btn btn-primary btn-sm" onclick="openCreate()">
      <i class="bi bi-plus"></i> Thêm mới
    </button>
  </div>

  <!-- Lọc theo trạng thái và ngày hẹn -->
  <div class="row g-2 mb-3">
    <div class="col-md-3">
      <select id="filterStatus" class="form-select form-select-sm" onchange="loadAppointments()">
        <option value="">-- Tất cả trạng thái --</option>
        <option value="pending">Chờ xác nhận</option>
        <option value="confirmed">Đã xác nhận</option>
        <option value="done">Hoàn thành</option>
        <option value="cancelled">Đã hủy</option>
      </select>
    </div>
    <div class="col-md-3">
      <input type="date" id="filterDate" class="form-control form-control-sm" onchange="loadAppointments()">
    </div>
  </div>

  <!-- Bảng danh sách phiếu sửa chữa — JS đổ vào #appointmentBody -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th><th>Khách hàng</th><th>Nhân viên</th>
            <th>Dịch vụ</th><th>Ngày hẹn</th><th>Trạng thái</th><th>Thao tác</th>
          </tr>
        </thead>
        <tbody id="appointmentBody">
          <tr><td colspan="7" class="text-center py-3">Đang tải...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Popup form dùng chung Thêm / Sửa phiếu sửa chữa -->
<div class="modal fade" id="modalAppointment" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalTitle">Thêm lịch hẹn</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="apptId">  <!-- lưu ID phiếu khi đang sửa -->
        <div class="mb-2">
          <label class="form-label small">Tiêu đề <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="apptTitle">
          <div class="invalid-feedback">Vui lòng nhập tiêu đề</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Khách hàng <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm" id="apptCustomer"></select>  <!-- được đổ dữ liệu bởi loadSelects() -->
        </div>
        <div class="mb-2">
          <label class="form-label small">Nhân viên phụ trách <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm" id="apptUser"></select>
          <div class="invalid-feedback">Vui lòng chọn nhân viên</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Dịch vụ <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm" id="apptService"></select>
          <div class="invalid-feedback">Vui lòng chọn dịch vụ</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Ngày giờ hẹn <span class="text-danger">*</span></label>
          <input type="datetime-local" class="form-control form-control-sm" id="apptDate">
        </div>
        <div class="mb-2">
          <label class="form-label small">Trạng thái</label>
          <select class="form-select form-select-sm" id="apptStatus">
            <option value="pending">Chờ xác nhận</option>
            <option value="confirmed">Đã xác nhận</option>
            <option value="done">Hoàn thành</option>
            <option value="cancelled">Đã hủy</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Ghi chú</label>
          <textarea class="form-control form-control-sm" id="apptNote" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary btn-sm" onclick="saveAppointment()">Lưu</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modal = new bootstrap.Modal(document.getElementById('modalAppointment'));

// Map trạng thái → badge HTML hiển thị trong bảng
const statusBadge = {
  pending:   '<span class="badge bg-warning text-dark">Chờ xác nhận</span>',
  confirmed: '<span class="badge bg-primary">Đã xác nhận</span>',
  done:      '<span class="badge bg-success">Hoàn thành</span>',
  cancelled: '<span class="badge bg-danger">Đã hủy</span>'
};

// Gọi API lấy danh sách phiếu theo bộ lọc, render ra bảng
async function loadAppointments() {
  const status = document.getElementById('filterStatus').value;
  const date   = document.getElementById('filterDate').value;
  const res    = await fetch(`/api/appointments.php?action=list&status=${status}&date=${date}`);
  const data   = await res.json();
  const tbody  = document.getElementById('appointmentBody');

  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Không có dữ liệu</td></tr>';
    return;
  }
  tbody.innerHTML = data.map((a, i) => `
    <tr>
      <td>${i+1}</td>
      <td>${a.customer_name || '—'}</td>
      <td>${a.staff_name || '—'}</td>
      <td>${a.service_name || '—'}</td>
      <td>${a.appointment_date ? a.appointment_date.substring(0,16).replace('T',' ') : '—'}</td>
      <td>${statusBadge[a.status] || a.status}</td>
      <td>
        <button class="btn btn-outline-primary btn-sm py-0" onclick="editAppointment(${a.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-outline-danger btn-sm py-0"  onclick="deleteAppointment(${a.id})"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
}

// Nạp các dropdown trong form: khách hàng, nhân viên, dịch vụ
async function loadSelects() {
  const [customers, users, services] = await Promise.all([
    fetch('/api/appointments.php?action=customers').then(r => r.json()),
    fetch('/api/appointments.php?action=users').then(r => r.json()),
    fetch('/api/appointments.php?action=services').then(r => r.json()),
  ]);
  document.getElementById('apptCustomer').innerHTML =
    '<option value="">-- Chọn khách hàng --</option>' +
    customers.map(c => `<option value="${c.id}">${c.full_name}</option>`).join('');
  document.getElementById('apptUser').innerHTML =
    '<option value="">-- Chọn nhân viên --</option>' +
    users.map(u => `<option value="${u.id}">${u.full_name}</option>`).join('');
  document.getElementById('apptService').innerHTML =
    '<option value="">-- Chọn dịch vụ --</option>' +
    services.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
}

// Mở popup thêm mới, reset form sạch
async function openCreate() {
  await loadSelects();
  ['apptId','apptTitle','apptDate','apptNote'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('apptStatus').value = 'pending';
  document.getElementById('modalTitle').textContent = 'Thêm lịch hẹn';
  modal.show();
}

// Lấy dữ liệu phiếu từ API, điền vào form, mở popup
async function editAppointment(id) {
  await loadSelects();
  const a = await fetch(`/api/appointments.php?action=get&id=${id}`).then(r => r.json());
  document.getElementById('apptId').value       = a.id;
  document.getElementById('apptTitle').value    = a.title || '';
  document.getElementById('apptCustomer').value = a.customer_id;
  document.getElementById('apptUser').value     = a.user_id;
  document.getElementById('apptService').value  = a.service_id;
  document.getElementById('apptDate').value     = a.appointment_date ? a.appointment_date.replace(' ','T').substring(0,16) : '';
  document.getElementById('apptStatus').value   = a.status;
  document.getElementById('apptNote').value     = a.note || '';
  document.getElementById('modalTitle').textContent = 'Sửa lịch hẹn';
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

// Lưu phiếu: tạo mới hoặc cập nhật tùy có ID hay không
async function saveAppointment() {
  const id = document.getElementById('apptId').value;
  if (!checkFields('apptTitle','apptCustomer','apptUser','apptService','apptDate')) return;
  const payload = {
    action:           id ? 'update' : 'create',
    id,
    title:            document.getElementById('apptTitle').value.trim(),
    customer_id:      parseInt(document.getElementById('apptCustomer').value) || 0,
    user_id:          parseInt(document.getElementById('apptUser').value)     || 0,
    service_id:       parseInt(document.getElementById('apptService').value)  || 0,
    appointment_date: document.getElementById('apptDate').value,
    status:           document.getElementById('apptStatus').value,
    note:             document.getElementById('apptNote').value.trim(),
  };

  const data = await fetch('/api/appointments.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
  }).then(r => r.json());
  if (data.success) { modal.hide(); loadAppointments(); }
  else alert('Lỗi: ' + data.error);
}

// Xóa phiếu sau khi xác nhận
async function deleteAppointment(id) {
  if (!confirm('Xóa lịch hẹn này?')) return;
  const data = await fetch('/api/appointments.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action:'delete', id })
  }).then(r => r.json());
  if (data.success) loadAppointments();
}

loadAppointments();
</script>
</body></html>
