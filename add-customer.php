<?php
include 'config.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $balance = $_POST['balance'] ?? 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, balance) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $balance]);
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