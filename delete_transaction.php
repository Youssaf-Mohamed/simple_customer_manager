<?php
session_start(); // بدء الجلسة لاستخدام $_SESSION

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// التحقق من أن القيم موجودة وصحيحة
if (!isset($_GET['id'], $_GET['customer_id']) || !is_numeric($_GET['id']) || !is_numeric($_GET['customer_id'])) {
    $_SESSION['error'] = "معرف غير صالح!";
    header('Location: ./customers.php');
    exit();
}

$transaction_id = intval($_GET['id']);
$customer_id = intval($_GET['customer_id']);

try {
    $pdo->beginTransaction();

    // جلب بيانات المعاملة قبل الحذف
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch();

    if ($transaction) {

        $operator = ($transaction['type'] === 'deposit') ? '-' : '+';
        $stmt = $pdo->prepare("UPDATE customers SET balance = balance $operator ? WHERE id = ?");
        $stmt->execute([$transaction['amount'], $customer_id]);


        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$transaction_id]);

        $_SESSION['success'] = "تم حذف العملية بنجاح!";
    } else {
        $_SESSION['error'] = "المعاملة غير موجودة!";
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "خطأ في الحذف: " . $e->getMessage();
}


header("Location: ./customer_details.php?id=$customer_id");
exit();
