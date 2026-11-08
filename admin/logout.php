<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    admin_logout();
}
redirect('/admin/login.php');
