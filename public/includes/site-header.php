<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Kooragoal';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Kooragoal</a>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="/today">مباريات اليوم</a>
            <a class="btn btn-outline-primary" href="/live">مباريات مباشرة</a>
            <a class="btn btn-outline-secondary" href="/standings.php?league=39">ترتيب الدوري</a>
            <a class="btn btn-outline-secondary" href="/scorers.php?league=39">قائمة الهدافين</a>
        </div>
    </div>
</nav>
<div class="container">
