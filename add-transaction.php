<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $customer_id = intval($_POST['customer_id']);
        $type = $_POST['type'];
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;
        $notes = htmlspecialchars(trim($_POST['notes']));

        if ($amount <= 0) {
            throw new Exception("المبلغ يجب أن يكون أكبر من الصفر.");
        }

        $pdo->beginTransaction();


        $stmt = $pdo->prepare("INSERT INTO transactions 
            (customer_id, type, amount, notes) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$customer_id, $type, $amount, $notes]);


        $stmt = $pdo->prepare("UPDATE customers 
            SET balance = CASE 
                WHEN ? = 'deposit' THEN balance + ? 
                ELSE balance - ? 
            END 
            WHERE id = ?");
        $stmt->execute([$type, $amount, $amount, $customer_id]);

        $pdo->commit();
        $_SESSION['success'] = "تمت إضافة العملية بنجاح";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "خطأ في الإضافة: " . $e->getMessage();
    }

    header("Location: customer_details.php?id=$customer_id");
    exit();
}
