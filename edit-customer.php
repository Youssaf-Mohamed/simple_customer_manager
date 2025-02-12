<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// التحقق من وجود معرف العميل
if (!isset($_GET['id'])) {
    header('location: customers');
}
$customer_id = $_GET['id'];

// جلب بيانات العميل
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

// إذا لم يتم العثور على العميل
if (!$customer) {
    $_SESSION['error'] = "العميل غير موجود";
    header('location: customers');
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $balance = $_POST['balance'] ?? 0;

    try {
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, balance = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $balance, $customer_id]);

        $_SESSION['success'] = "تم تحديث بيانات العميل بنجاح";
        header("location: customer?id=$customer_id");
    } catch (PDOException $e) {
        $_SESSION['error'] = "خطأ في التحديث: " . $e->getMessage();
        header("location edit-customer?id=$customer_id");
    }
}

include __DIR__ . '/header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">تعديل بيانات العميل</h2>
    
    <form method="post">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">اسم العميل</label>
                <input type="text" class="form-control" 
                       id="name" name="name" 
                       value="<?= safe($customer['name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">رقم الهاتف</label>
                <input type="text" class="form-control" 
                       id="phone" name="phone" 
                       value="<?= safe($customer['phone']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="balance" class="form-label">الرصيد الحالي</label>
                <input type="number" step="0.01" class="form-control" 
                       id="balance" name="balance" 
                       value="<?= safe($customer['balance']) ?>" required>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                <a href="/customer?id=<?= $customer_id ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>