<nav id="sidebar">
  <div class="sidebar-label">Menu chính</div>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link <?= $page==='dashboard'?'active':'' ?>" href="/pages/dashboard.php">
        <i class="bi bi-speedometer2"></i> Tổng quan
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $page==='customers'?'active':'' ?>" href="/pages/customers.php">
        <i class="bi bi-person-badge"></i> Khách hàng
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $page==='appointments'?'active':'' ?>" href="/pages/appointments.php">
        <i class="bi bi-wrench-adjustable"></i> Phiếu sửa chữa
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $page==='services'?'active':'' ?>" href="/pages/services.php">
        <i class="bi bi-cpu"></i> Dịch vụ
      </a>
    </li>
  </ul>

  <?php if ($isAdmin): ?>
  <div class="sidebar-label" style="margin-top:12px">Quản trị</div>
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link <?= $page==='users'?'active':'' ?>" href="/pages/users.php">
        <i class="bi bi-shield-lock"></i> Người dùng
      </a>
    </li>
  </ul>
  <?php endif; ?>
</nav>