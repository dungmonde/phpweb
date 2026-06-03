<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: /pages/dashboard.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ElectroFix — Đăng nhập</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <div class="card login-card">
    <div class="card-body p-5">
      <div class="text-center mb-4">
        <div class="bg-primary bg-opacity-10 rounded-3 d-inline-flex p-3 mb-2">
          <i class="bi bi-cpu-fill fs-2 text-primary"></i>
        </div>
        <h4 class="fw-bold mb-0">ElectroFix</h4>
        <p class="text-muted small mt-1">Sửa chữa & Lắp ráp thiết bị điện tử</p>
      </div>

      <div id="alertBox" class="alert alert-danger py-2 small d-none"></div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username" placeholder="Nhập username" autofocus>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label small fw-semibold">Mật khẩu</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" placeholder="Nhập mật khẩu">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePass()">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button class="btn btn-primary w-100 py-2 fw-semibold" id="btnLogin" onclick="doLogin()">
        <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
      </button>
    </div>
  </div>

<script>
function togglePass() {
  const inp = document.getElementById('password');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') { inp.type = 'text'; ico.className = 'bi bi-eye-slash'; }
  else { inp.type = 'password'; ico.className = 'bi bi-eye'; }
}

async function doLogin() {
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  const alertBox = document.getElementById('alertBox');
  const btn      = document.getElementById('btnLogin');

  alertBox.classList.add('d-none');
  if (!username || !password) {
    alertBox.textContent = 'Vui lòng nhập đầy đủ thông tin!';
    alertBox.classList.remove('d-none'); return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';

  const data = await fetch('/api/auth.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ action: 'login', username, password })
  }).then(r => r.json());

  if (data.success) {
    window.location.href = '/pages/customers.php';
  } else {
    alertBox.textContent = data.error || 'Đăng nhập thất bại!';
    alertBox.classList.remove('d-none');
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập';
  }
}

document.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
</script>
</body>
</html>
