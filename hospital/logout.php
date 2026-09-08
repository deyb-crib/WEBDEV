<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
unset($_SESSION['hospital_admin']);
header('Location: ../admin/login.php');
exit;