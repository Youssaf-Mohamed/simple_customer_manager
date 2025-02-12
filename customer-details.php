<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'functions.php';

// التحقق من وجود معرف العميل في الرابط والتأكد أنه رقم صحيح
$customer_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$customer_id) {
    header("Location: customers.php");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("⚠️ لم يتم العثور على العميل!");
}


$stmt = $pdo->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customer_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0"><?= safe($customer['name']) ?></h4>
                <p class="text-muted mb-0"><?= safe($customer['phone']) ?></p>
            </div>
            <div class="col-md-6 text-end">
                <h2 class="mb-0"><?= formatBalance($customer['balance']) ?></h2>
            </div>
        </div>
    </div>
</div>


<form method="post" action="update_balance.php" class="mb-4">
    <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer_id) ?>">

    <div class="row g-3">
        <div class="col-md-3">
            <select name="operation_type" class="form-select" required>
                <option value="deposit">إضافة رصيد (+)</option>
                <option value="withdraw">خصم رصيد (-)</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" class="form-control" name="amount" placeholder="المبلغ" required>
        </div>
        <div class="col-md-4">
            <textarea class="form-control" name="notes" placeholder="  دون ملاحظاتك "></textarea>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">تأكيد</button>
        </div>
    </div>
</form>


<div class="card shadow">
    <div class="card-header bg-light">
        <h5 class="mb-0">العمليات المالية</h5>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php if (empty($transactions)): ?>
                <div class="list-group-item text-center text-muted py-4">
                    لا توجد عمليات مالية لعرضها.
                </div>
            <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <span class="badge bg-<?= $t['type'] === 'deposit' ? 'success' : 'danger' ?>">
                                    <?= $t['type'] === 'deposit' ? '+' : '-' ?>
                                </span>
                            </div>
                            <div class="col-md-2">
                                <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                            </div>
                            <div class="col-md-2">
                                <?= number_format($t['amount'], 2) ?> جنية
                            </div>
                            <div class="col-md-5">
                                <?= safe($t['notes']) ?>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="edit_transaction.php?id=<?= urlencode($t['id']) ?>&customer_id=<?= urlencode($customer_id) ?>" class="btn btn-sm btn-warning me-2">تعديل</a>
                                <a href="delete_transaction.php?id=<?= urlencode($t['id']) ?>&customer_id=<?= urlencode($customer_id) ?>" class="btn btn-sm btn-danger">حذف</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>