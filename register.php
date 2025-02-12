<?php
session_start();
require_once __DIR__ . "/config.php";


if (isset($_POST["submit"]) && $_POST["submit"] === "register") {
    if (!empty($_POST["username"]) && !empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["confirm_password"])) {

        $username = htmlspecialchars(trim($_POST["username"]));
        $email = htmlspecialchars(trim($_POST["email"]));
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $check_agree = isset($_POST["check_agree"]) ? $_POST["check_agree"] : null;

        $hash_password = password_hash($password, PASSWORD_DEFAULT);




        $error = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = "Invalid email format.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ? OR email = ? ");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount()) {
                $error[] = "Username or email already exists.";
            }
        }

        if ($password !== $confirm_password) {
            $error[] = "Password and confirm password must be the same.";
        }

        if ($check_agree !== "checked") {
            $error[] = "You must agree to our terms and conditions.";
        }

        if (!empty($error)) {
            $_SESSION["error_msg"] = implode(" | ", $error);
            header("Location: register.php");
            exit();
        }


        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hash_password);

        if ($stmt->execute()) {
            $_SESSION["success_msg"] = "Registration successful.";
            header("Location: index.php");
        } else {
            $_SESSION["error_msg"] = "Registration failed.";
            header("Location: register.php");
        }
        exit();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <section class="vh-100 bg-image"
        style="background-image: url('https://mdbcdn.b-cdn.net/img/Photos/new-templates/search-box/img4.webp');">
        <div class="mask d-flex align-items-center h-100 gradient-custom-3">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-9 col-lg-7 col-xl-6">
                        <div class="card" style="border-radius: 15px;">
                            <div class="card-body p-5">
                                <h2 class="text-uppercase text-center mb-5">Create an account</h2>







                                <form method="POST" action="register.php">

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="text" id="form3Example1cg" name="username" class="form-control form-control-lg" />
                                        <label class="form-label" for="form3Example1cg">Your Name</label>
                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="email" id="form3Example3cg" name="email" class="form-control form-control-lg" />
                                        <label class="form-label" for="form3Example3cg">Your Email</label>
                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="password" id="form3Example4cg" name="password" class="form-control form-control-lg" />
                                        <label class="form-label" for="form3Example4cg">Password</label>
                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="password" id="form3Example4cdg" name="confirm_password" class="form-control form-control-lg" />
                                        <label class="form-label" for="form3Example4cdg">Repeat your password</label>
                                    </div>

                                    <div class="form-check d-flex justify-content-center mb-5">
                                        <input class="form-check-input me-2" name="check_agree" type="hidden" value="unchecked">
                                        <input class="form-check-input me-2" name="check_agree" type="checkbox" value="checked" id="form2Example3cg" />

                                        <label class="form-check-label" for="form2Example3g">
                                            I agree all statements in <a href="#!" class="text-body"><u>Terms of service</u></a>
                                        </label>
                                    </div>

                                    <div class="d-flex justify-content-center">
                                        <button type="submit" name="submit" value="register" data-mdb-button-init
                                            data-mdb-ripple-init class="btn btn-success btn-block btn-lg gradient-custom-4 text-body">Register</button>
                                    </div>

                                    <p class="text-center text-muted mt-5 mb-0">Have already an account? <a href="../index.php"
                                            class="fw-bold text-body"><u>Login here</u></a></p>

                                </form>

                                <?php


                                if (isset($_SESSION["error_msg"])) {
                                    $er_msg  = $_SESSION["error_msg"];




                                ?>
                                    <div
                                        class="alert alert-secondary"
                                        role="alert">
                                        <strong>Error</strong> <?= $er_msg ?>
                                    </div>





                                <?php
                                }



                                ?>













                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Bootstrap JavaScript Libraries -->
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
</body>

</html>