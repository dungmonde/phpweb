<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$page = basename($_SERVER['PHP_SELF'], '.php');

if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php'); exit;
}
$authUser = $_SESSION['user'];
$isAdmin  = $authUser['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ElectroFix — Quản lý sửa chữa thiết bị điện tử</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar app-navbar px-4 d-flex align-items-center justify-content-between">
  <a href="/pages/dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
    <div class="brand-icon"><i class="bi bi-cpu-fill text-white"></i></div>
    <div>
      <div class="brand-title">ElectroFix</div>
    </div>
    <span class="brand-sub d-none d-md-block ms-1">Sửa chữa & Lắp ráp thiết bị điện tử</span>
  </a>
  <div class="d-flex align-items-center gap-3">
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width:34px;height:34px">
        <i class="bi bi-person-fill text-white"></i>
      </div>
      <div>
        <div class="text-white fw-semibold" style="font-size:.85rem;line-height:1.2"><?= htmlspecialchars($authUser['full_name']) ?></div>
        <span class="badge <?= $isAdmin ? 'bg-warning text-dark' : 'bg-light text-dark' ?>" style="font-size:.65rem">
          <?= $isAdmin ? '<i class="bi bi-shield-fill"></i> Admin' : '<i class="bi bi-person"></i> Nhân viên' ?>
        </span>
      </div>
    </div>
    <button class="btn btn-outline-light btn-sm px-3" onclick="doLogout()">
      <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất
    </button>
  </div>
</nav>

<div class="d-flex">
<script>
async function doLogout() {
  await fetch('/api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'logout'}) });
  window.location.href = '/pages/login.php';
}
</script>