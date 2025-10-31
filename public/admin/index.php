<?php

require_once __DIR__ . '/bootstrap.php';

use App\Security\Auth;
use App\Security\RateLimiter;
use App\Support\Config;

$error = null;

if (request_method() === 'POST') {
    $limiterConfig = Config::get('security.admin_rate_limit');
    $limiter = new RateLimiter('admin-login', $limiterConfig['max_requests'], $limiterConfig['per_minutes']);
    $identity = $_SERVER['REMOTE_ADDR'] ?? 'cli';

    if (!$limiter->hit($identity)) {
        $error = 'محاولات كثيرة جدًا، حاول لاحقًا';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (Auth::verifyAdmin($username, $password)) {
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard.php');
            exit;
        }

        $error = 'بيانات تسجيل الدخول غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - تسجيل الدخول</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="login-wrapper">
        <h1>تسجيل الدخول</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" class="form-control" placeholder="اسم المستخدم" required>
            <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
            <button type="submit" class="btn">دخول</button>
        </form>
    </div>
</body>
</html>
