<?php
session_start(); 

require_once __DIR__ . '/config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? null;
    $type = $_POST['operation_type'] ?? null;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
    $notes = $_POST['notes'] ?? '';

    if (!$customer_id || !$type || !$amount || $amount <= 0) {
        $_SESSION['error'] = "بيانات غير صحيحة، يرجى التأكد من المدخلات.";
        header("Location: ./customer-details.php?id=" . ($customer_id ?? ''));
        exit();
    }

    try {
        $pdo->beginTransaction();


        $stmt = $pdo->prepare("SELECT balance FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch();

        if (!$customer) {
            throw new Exception("العميل غير موجود.");
        }


        if ($type === 'withdraw' && $customer['balance'] < $amount) {
            throw new Exception("الرصيد غير كافٍ للسحب.");
        }


        $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, type, amount, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customer_id, $type, $amount, $notes]);


        $operator = ($type === 'deposit') ? '+' : '-';
        $stmt = $pdo->prepare("UPDATE customers SET balance = balance $operator ? WHERE id = ?");
        $stmt->execute([$amount, $customer_id]);

        $pdo->commit();
        $_SESSION['success'] = "تمت العملية بنجاح.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "خطأ: " . $e->getMessage();
    }

    header("Location: ./customer-details.php?id=$customer_id"); 
    exit();
}
