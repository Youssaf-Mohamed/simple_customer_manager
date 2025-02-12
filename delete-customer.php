<?php

header('Content-Type: application/json');


require_once __DIR__ . '/config.php';


$rawInput = file_get_contents("php://input");
if (!$rawInput) {
    echo json_encode([
        'success' => false, 
        'message' => 'لم يتم استقبال أي بيانات'
    ]);
    exit;
}


$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ في تحليل JSON: ' . json_last_error_msg()
    ]);
    exit;
}


if (isset($input['id']) && is_numeric($input['id'])) {
    $id = (int)$input['id'];
    
    try {

        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = :id");
        $result = $stmt->execute(['id' => $id]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'لم يتم العثور على العميل'
            ]);
        }
    } catch (PDOException $e) {

        error_log('Error deleting customer: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'حدث خطأ أثناء الحذف'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'ID العميل غير موجود أو غير صالح'
    ]);
}
?>
