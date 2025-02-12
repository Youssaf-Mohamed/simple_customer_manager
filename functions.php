<?php
require_once __DIR__ . './config.php';

function formatBalance($balance) {
    $class = $balance < 0 ? 'text-danger' : 'text-success';
    return '<span class="'.$class.'">'.number_format($balance, 2).' جنية</span>';
}

function safe($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function getCustomerBalance($customer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT balance FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    return $stmt->fetchColumn();
}
?>
