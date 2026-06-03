<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php');
} else {
    header('Location: /pages/dashboard.php');
}
exit;