<?php
require_once __DIR__ . 'config.php';

$transaction_id = $_GET['id'] ?? header("Location: customers.php");
$customer_id = $_GET['customer_id'] ?? header("Location: customers.php");


$transaction = $pdo->prepare("SELECT * FROM transactions WHERE id = ?")
    ->execute([$transaction_id])->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_type = $_POST['operation_type'];
    $new_amount = (float)$_POST['amount'];
    $new_notes = $_POST['notes'];

    try {
        $pdo->beginTransaction();


        $old_amount = $transaction['amount'];
        $difference = 0;

        if ($transaction['type'] === $new_type) {
            $difference = $new_amount - $old_amount;
        } else {
            $difference = (-$old_amount) - $new_amount;
        }


        $stmt = $pdo->prepare("UPDATE customers 
            SET balance = balance + ? 
            WHERE id = ?");
        $stmt->execute([$difference, $customer_id]);


        $stmt = $pdo->prepare("UPDATE transactions SET 
            type = ?, amount = ?, notes = ? 
            WHERE id = ?");
        $stmt->execute([$new_type, $new_amount, $new_notes, $transaction_id]);

        $pdo->commit();
        $_SESSION['success'] = "تم التعديل بنجاح";
        header("Location: customer_details.php?id=$customer_id");
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "خطأ في التعديل: " . $e->getMessage();
        header("Location: edit_transaction.php?id=$transaction_id&customer_id=$customer_id");
    }
    exit();
}

include __DIR__ . 'header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">تعديل العملية</h2>

    <form method="post">
        <div class="row g-3">
            <div class="col-md-4">
                <select name="operation_type" class="form-select" required>
                    <option value="deposit" <?= $transaction['type'] === 'deposit' ? 'selected' : '' ?>>إيداع</option>
                    <option value="withdraw" <?= $transaction['type'] === 'withdraw' ? 'selected' : '' ?>>سحب</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01"
                    class="form-control"
                    name="amount"
                    value="<?= $transaction['amount'] ?>"
                    required>
            </div>
            <div class="col-md-12">
                <textarea class="form-control"
                    name="notes"
                    rows="3"><?= safe($transaction['notes']) ?></textarea>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-warning">حفظ التغييرات</button>
                <a href="customer_details.php?id=<?= $customer_id ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . 'footer.php'; ?>