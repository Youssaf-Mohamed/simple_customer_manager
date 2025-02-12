<?php
session_start();
include 'config.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استقبال البيانات مع تنقية المدخلات
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $balance = isset($_POST['balance']) ? trim($_POST['balance']) : 0;

    // التأكد من تسجيل دخول المستخدم (وجود user_id في الجلسة)
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "خطأ: يجب تسجيل الدخول أولاً.";
        header("Location: login.php");
        exit();
    }

    try {
        // تضمين user_id من الجلسة في عملية الإدخال
        $stmt = $pdo->prepare("INSERT INTO customers (user_id, name, phone, balance) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $name, $phone, $balance]);
        $_SESSION['success'] = "تمت إضافة العميل بنجاح";
        header("Location: customers.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "خطأ في الإضافة: " . $e->getMessage();
        header("Location: customers.php");
        exit();
    }
}
?>

<div class="container mt-5">
    <h2 class="mb-4">إضافة عميل جديد</h2>

    <form method="post">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="name" placeholder="اسم العميل" required>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" name="phone" placeholder="رقم الهاتف" required>
            </div>
            <div class="col-md-12">
                <input type="number" step="0.01" class="form-control" name="balance" placeholder="الرصيد الابتدائي">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">حفظ</button>
                <a href="customers.php" class="btn btn-secondary">إلغاء</a>
            </div>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>