<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Kooragoal\Services\Database;
use Kooragoal\Services\DatabaseInitializer;
use Kooragoal\Services\Logger;
use Kooragoal\Services\Scheduler;
use Kooragoal\Services\Security\AuthManager;

if (!function_exists('kooragoal_render_bootstrap_error')) {
    function kooragoal_render_bootstrap_error(string $message): void
    {
        http_response_code(500);

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $message . PHP_EOL);
        } else {
            echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>خطأ في النظام</title>';
            echo '<style>body{font-family:Tahoma,Arial,sans-serif;background:#f8f9fa;color:#212529;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}';
            echo '.card{background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:32px;max-width:520px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.08);}';
            echo '.card h1{font-size:24px;margin-bottom:16px;} .card p{font-size:16px;line-height:1.6;margin:0;}</style></head><body>';
            echo '<div class="card"><h1>حدث خطأ داخلي</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p></div></body></html>';
        }

        exit;
    }
}

/** @var Kooragoal\Services\Container $container */
$logger = null;
try {
    $logger = $container->get(Logger::class);
} catch (Throwable $exception) {
    error_log('Logger bootstrap failed: ' . $exception->getMessage());
}

try {
    $database = $container->get(Database::class);
} catch (Throwable $exception) {
    if ($logger) {
        $logger->error('Database connection failed', ['error' => $exception->getMessage()]);
    } else {
        error_log('Database connection failed: ' . $exception->getMessage());
    }

    $userMessage = 'تعذر الاتصال بقاعدة البيانات. تأكد من بيانات الدخول في config/config.php ومن إنشاء قاعدة البيانات على الخادم.';

    if ($exception instanceof \PDOException) {
        $error = $exception->getMessage();
        if (stripos($error, 'unknown database') !== false) {
            $userMessage = 'قاعدة البيانات المحددة غير موجودة. تأكد من إنشائها في cPanel وربطها بالمستخدم ثم حدّث اسمها في config/config.php.';
        } elseif (stripos($error, 'access denied') !== false) {
            $userMessage = 'فشل تسجيل الدخول إلى MySQL. يرجى التحقق من اسم المستخدم وكلمة المرور وصلاحيات المستخدم في cPanel.';
        } elseif (stripos($error, 'could not find driver') !== false) {
            $userMessage = 'امتداد PDO MySQL غير مفعّل على الخادم. اطلب من مزود الاستضافة تفعيل pdo_mysql أو قم بتمكينه من لوحة التحكم.';
        }
    }

    kooragoal_render_bootstrap_error($userMessage);
}

try {
    $initializer = new DatabaseInitializer($database, __DIR__ . '/../football_db.sql', $logger);
    $initializer->ensureSchema();
} catch (Throwable $exception) {
    if ($logger) {
        $logger->error('Database schema initialisation failed', ['error' => $exception->getMessage()]);
    } else {
        error_log('Database schema initialisation failed: ' . $exception->getMessage());
    }

    kooragoal_render_bootstrap_error('تعذر تهيئة جداول قاعدة البيانات تلقائيًا. يرجى استيراد ملف football_db.sql يدويًا ثم إعادة المحاولة.');
}

try {
    $scheduler = $container->get(Scheduler::class);
    $scheduler->runDueTasks();
} catch (Throwable $exception) {
    if ($logger) {
        $logger->error('Scheduler execution failed', ['error' => $exception->getMessage()]);
    } else {
        error_log('Scheduler execution failed: ' . $exception->getMessage());
    }
}

// Ensure the authentication service is always initialised for session handling
$auth = $container->get(AuthManager::class);
