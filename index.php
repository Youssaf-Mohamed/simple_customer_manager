<?php
session_start();
require_once __DIR__ . "/config.php";

// فحص الكوكي "rememberme" لتسجيل الدخول تلقائيًا إذا كان موجودًا
if (!isset($_SESSION['user_id']) && isset($_COOKIE['rememberme'])) {
    // تنسيق الكوكي: "user_id:token"
    list($userId, $token) = explode(':', $_COOKIE['rememberme'], 2);

    // استعلام لجلب المستخدم مع التأكد من أن الكوكي لم تنتهِ صلاحيته
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND remember_expiry > NOW()");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // التحقق من صحة الرمز المُخزن باستخدام password_verify
    if ($user && password_verify($token, $user['remember_token'])) {
        $_SESSION['user_id'] = $user['id'];
        header("location: customers.php?msg=login_successfully");
        exit();
    }
}

// معالجة بيانات تسجيل الدخول عند إرسال الفورم
if (isset($_POST["submit"]) && $_POST["submit"] === "submit") {
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = trim($_POST["password"]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([
        "email" => $email,
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user["password"])) {
        // حفظ حالة تسجيل الدخول في الجلسة
        $_SESSION['user_id'] = $user['id'];

        // التحقق من اختيار المستخدم لخيار "تذكرني"
        if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
            // إنشاء رمز عشوائي
            $token = bin2hex(random_bytes(16));
            // إعداد قيمة الكوكي بتنسيق "user_id:token"
            $cookieValue = $user['id'] . ':' . $token;
            $expiryTime = time() + (86400 * 30); // مدة الصلاحية 30 يومًا

            // ضبط الكوكي مع خاصيتي Secure وHttpOnly (لاحظ أن Secure يعمل فقط عبر HTTPS)
            setcookie("rememberme", $cookieValue, $expiryTime, "/", "", true, true);

            // حفظ الرمز المولد (مشفّر) مع تاريخ الانتهاء في قاعدة البيانات
            $stmtUpdate = $pdo->prepare("UPDATE users SET remember_token = :token, remember_expiry = :expiry WHERE id = :id");
            $stmtUpdate->execute([
                "token"  => password_hash($token, PASSWORD_DEFAULT),
                "expiry" => date('Y-m-d H:i:s', $expiryTime),
                "id"     => $user['id']
            ]);
        }
        header("location: customers.php?msg=login_successfully");
        exit();
    } else {
        $msg = "Invalid username or password";
        header("location: index.php?msg=login_failed&error=" . urlencode($msg));
        exit();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <title>Login Page</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <section class="vh-100">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 text-black">
                    <div class="px-5 ms-xl-4">
                        <i class="fas fa-crow fa-2x me-3 pt-5 mt-xl-4" style="color: #709085;"></i>
                        <span class="h1 fw-bold mb-0">Logo</span>
                    </div>
                    <div class="d-flex align-items-center h-custom-2 px-5 ms-xl-4 mt-5 pt-5 pt-xl-0 mt-xl-n5">
                        <form style="width: 23rem;" method="POST" action="index.php">
                            <h3 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Log in</h3>

                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="email" id="form2Example18" name="email" class="form-control form-control-lg" required />
                                <label class="form-label" for="form2Example18">Email address</label>
                            </div>

                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="password" id="form2Example28" name="password" class="form-control form-control-lg" required />
                                <label class="form-label" for="form2Example28">Password</label>
                            </div>

                            <!-- إضافة خيار "تذكرني" -->
                            <div class="form-check mb-4">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <div class="pt-1 mb-4">
                                <button data-mdb-button-init data-mdb-ripple-init class="btn btn-info btn-lg btn-block" name="submit" value="submit" type="submit">
                                    Login
                                </button>
                            </div>

                            <p class="small mb-5 pb-lg-2"><a class="text-muted" href="#!">Forgot password?</a></p>
                            <p>Don't have an account? <a href="register.php" class="link-info">Register here</a></p>
                        </form>

                        <?php
                        if (isset($_GET["msg"]) && $_GET["msg"] == "login_field") {
                        ?>
                            <div class="alert alert-primary" role="alert">
                                <strong>Error</strong> Login Failed. Please check!
                            </div>
                        <?php
                        }
                        ?>

                    </div>
                </div>

                <div class="col-sm-6 px-0 d-none d-sm-block">
                    <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/img3.webp"
                        alt="Login image" class="w-100 vh-100" style="object-fit: cover; object-position: left;">
                </div>
            </div>
        </div>
    </section>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
</body>

</html>