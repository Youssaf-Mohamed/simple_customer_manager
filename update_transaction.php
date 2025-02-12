<?php
session_start(); 

require_once __DIR__ . '/config.php'; 
require_once __DIR__ . '/functions.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /customers");
    exit();
}

$transaction_id = $_POST['transaction_id'] ?? '';
$customer_id = $_POST['customer_id'] ?? '';
$new_type = $_POST['operation_type'] ?? '';
$new_amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$new_notes = $_POST['notes'] ?? '';

try {
    $pdo->beginTransaction();


    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$transaction_id]);
    $old_transaction = $stmt->fetch();

    if (!$old_transaction) {
        throw new Exception("العملية غير موجودة.");
    }


    $old_amount = floatval($old_transaction['amount']);
    $difference = 0;

    if ($old_transaction['type'] === $new_type) {
        $difference = $new_amount - $old_amount;
    } else {
        $difference = (-$old_amount) - $new_amount;
    }


    $stmt = $pdo->prepare("SELECT balance FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();

    if (!$customer) {
        throw new Exception("العميل غير موجود.");
    }

    if ($new_type === 'withdraw' && ($customer['balance'] + $difference) < 0) {
        throw new Exception("الرصيد غير كافٍ لإتمام التعديل.");
    }


    $stmt = $pdo->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$difference, $customer_id]);


    $stmt = $pdo->prepare("UPDATE transactions SET type = ?, amount = ?, notes = ? WHERE id = ?");
    $stmt->execute([$new_type, $new_amount, $new_notes, $transaction_id]);

    $pdo->commit();
    $_SESSION['success'] = "تم التعديل بنجاح.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "خطأ في التعديل: " . $e->getMessage();
}

header("Location: ./customer?id=$customer_id"); 
exit();
