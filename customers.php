<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';


$stmt = $pdo->query("SELECT * FROM customers");
$customers = $stmt->fetchAll();


$totalBalance = 0;
foreach ($customers as $customer) {
    $totalBalance += $customer['balance'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العملاء</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }

    </style>
</head>

<body>
    <?php include __DIR__ . '/header.php'; ?>

    <div class="container mt-4 animate-fade-in">

        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">الرصيد الكلي للعملاء</h3>
            </div>
            <div class="card-body">
                <h4 class="text-center text-dark" id="total-balance"><?= formatBalance($totalBalance) ?></h4>
            </div>
        </div>


        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">قائمة العملاء</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-dark">الاسم</th>
                                <th class="text-dark">الهاتف</th>
                                <th class="text-dark">الرصيد</th>
                                <th class="text-dark">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td class="text-dark"><?= htmlspecialchars($customer['name']) ?></td>
                                    <td class="text-dark"><?= htmlspecialchars($customer['phone']) ?></td>
                                    <td class="text-dark"><?= formatBalance($customer['balance']) ?></td>
                                    <td>
                                        <a href="./customer-details.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-primary">التفاصيل</a>
                                        <button class="btn btn-sm btn-outline-danger delete-customer-btn" data-id="<?= $customer['id'] ?>" data-balance="<?= $customer['balance'] ?>">حذف</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // حذف العميل باستخدام SweetAlert2
            const deleteButtons = document.querySelectorAll('.delete-customer-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const customerId = this.getAttribute('data-id');
                    const customerBalance = parseFloat(this.getAttribute('data-balance'));

                    Swal.fire({
                        title: 'هل أنت متأكد؟',
                        text: "سيتم حذف هذا العميل بشكل دائم!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، احذف!',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // إرسال طلب Ajax للحذف
                            fetch('./delete-customer.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ id: customerId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // تحديث الرصيد الكلي
                                    const totalBalanceElement = document.getElementById('total-balance');
                                    let currentTotalBalance = parseFloat(totalBalanceElement.textContent.replace(/[^0-9.-]+/g, ""));
                                    totalBalanceElement.textContent = formatBalance(currentTotalBalance - customerBalance);

                                    // إزالة الصف من الجدول
                                    this.closest('tr').remove();

                                    Swal.fire(
                                        'تم الحذف!',
                                        'تم حذف العميل بنجاح.',
                                        'success'
                                    );
                                } else {
                                    Swal.fire(
                                        'خطأ!',
                                        'حدث خطأ أثناء الحذف.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });
            });

            // تنسيق الرصيد
            function formatBalance(balance) {
                return balance.toLocaleString('ar-EG', { style: 'currency', currency: 'EGP' });
            }
        });
    </script>
</body>

</html>