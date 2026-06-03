<?php include '../includes/header.php'; ?>  <!-- Navbar + session check -->
<?php
// Chặn truy cập nếu không phải admin — redirect về trang khách hàng
if (!$isAdmin) {
    header('Location: /pages/customers.php'); exit;
}
?>
<?php include '../includes/sidebar.php'; ?>  <!-- Menu bên trái -->

<div class="container-fluid p-4 flex-grow-1">
  <!-- Tiêu đề trang + nút Thêm mới -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Quản lý người dùng</h5>
    <button class="btn btn-primary btn-sm" onclick="openCreate()">
      <i class="bi bi-plus"></i> Thêm mới
    </button>
  </div>

  <!-- Thanh tìm kiếm + lọc theo vai trò -->
  <div class="row g-2 mb-3">
    <div class="col-md-4">
      <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Tìm theo tên, username..." oninput="clearTimeout(_st);_st=setTimeout(loadUsers,350)">
    </div>
    <div class="col-md-3">
      <select id="filterRole" class="form-select form-select-sm" onchange="loadUsers()">
        <option value="">-- Tất cả vai trò --</option>
        <option value="admin">Admin</option>
        <option value="staff">Nhân viên</option>
      </select>
    </div>
  </div>

  <!-- Bảng danh sách người dùng — JS đổ vào #userBody -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th><th>Họ tên</th><th>Username</th>
            <th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th>Thao tác</th>
          </tr>
        </thead>
        <tbody id="userBody">
          <tr><td colspan="7" class="text-center py-3">Đang tải...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Popup form Thêm / Sửa người dùng -->
<div class="modal fade" id="modalUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalTitle">Thêm người dùng</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="userId">  <!-- lưu ID khi đang sửa -->
        <div class="mb-2">
          <label class="form-label small">Họ tên <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="userFullname">
          <div class="invalid-feedback">Vui lòng nhập họ tên</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Username <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="userUsername">
          <div class="invalid-feedback">Vui lòng nhập username</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Mật khẩu <span id="passRequired" class="text-danger">*</span></label>
          <input type="password" class="form-control form-control-sm" id="userPassword" placeholder="Để trống nếu không đổi">
          <div class="invalid-feedback">Vui lòng nhập mật khẩu</div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Vai trò</label>
          <select class="form-select form-select-sm" id="userRole">
            <option value="staff">Nhân viên</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Trạng thái</label>
          <select class="form-select form-select-sm" id="userStatus">
            <option value="1">Hoạt động</option>
            <option value="0">Ngưng</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary btn-sm" onclick="saveUser()">Lưu</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modal = new bootstrap.Modal(document.getElementById('modalUser'));
let _st;

// Gọi API lấy danh sách người dùng theo bộ lọc, render ra bảng
async function loadUsers() {
  const search = document.getElementById('searchInput').value;
  const role   = document.getElementById('filterRole').value;
  const data   = await fetch(`/api/users.php?action=list&search=${encodeURIComponent(search)}&role=${role}`).then(r => r.json());
  const tbody  = document.getElementById('userBody');

  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Không có dữ liệu</td></tr>';
    return;
  }
  tbody.innerHTML = data.map((u, i) => `
    <tr>
      <td>${i+1}</td>
      <td>${u.full_name}</td>
      <td><code>${u.username}</code></td>
      <td>${u.role === 'admin'
          ? '<span class="badge bg-danger">Admin</span>'
          : '<span class="badge bg-secondary">Nhân viên</span>'}</td>
      <td>${u.status == 1
          ? '<span class="badge bg-success">Hoạt động</span>'
          : '<span class="badge bg-secondary">Ngưng</span>'}</td>
      <td>${u.created_at ? u.created_at.substring(0,10) : '—'}</td>
      <td>
        <button class="btn btn-outline-primary btn-sm py-0" onclick="editUser(${u.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-outline-danger btn-sm py-0"  onclick="deleteUser(${u.id})"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
}

// Mở popup thêm mới, reset form sạch, hiện dấu * mật khẩu
function openCreate() {
  document.getElementById('userId').value      = '';
  document.getElementById('userFullname').value = '';
  document.getElementById('userUsername').value = '';
  document.getElementById('userPassword').value = '';
  document.getElementById('userRole').value    = 'staff';
  document.getElementById('userStatus').value  = '1';
  document.getElementById('passRequired').style.display = 'inline';
  document.getElementById('modalTitle').textContent = 'Thêm người dùng';
  modal.show();
}

// Lấy dữ liệu user từ API, điền vào form, ẩn dấu * mật khẩu
async function editUser(id) {
  const u = await fetch(`/api/users.php?action=get&id=${id}`).then(r => r.json());
  document.getElementById('userId').value       = u.id;
  document.getElementById('userFullname').value = u.full_name;
  document.getElementById('userUsername').value = u.username;
  document.getElementById('userPassword').value = '';
  document.getElementById('userRole').value     = u.role;
  document.getElementById('userStatus').value   = u.status;
  document.getElementById('passRequired').style.display = 'none';
  document.getElementById('modalTitle').textContent = 'Sửa người dùng';
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

// Lưu user: tạo mới hoặc cập nhật (mật khẩu bắt buộc khi tạo mới)
async function saveUser() {
  const id = document.getElementById('userId').value;
  const toCheck = ['userFullname', 'userUsername'];
  if (!id) toCheck.push('userPassword');
  if (!checkFields(...toCheck)) return;
  const payload = {
    action:    id ? 'update' : 'create',
    id,
    full_name: document.getElementById('userFullname').value.trim(),
    username:  document.getElementById('userUsername').value.trim(),
    password:  document.getElementById('userPassword').value,
    role:      document.getElementById('userRole').value,
    status:    document.getElementById('userStatus').value,
  };

  const data = await fetch('/api/users.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
  }).then(r => r.json());
  if (data.success) { modal.hide(); loadUsers(); }
  else alert('Lỗi: ' + data.error);
}

// Xóa user sau khi xác nhận
async function deleteUser(id) {
  if (!confirm('Xóa người dùng này?')) return;
  const data = await fetch('/api/users.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action:'delete', id })
  }).then(r => r.json());
  if (data.success) loadUsers();
}

loadUsers();
</script>
</body></html>
