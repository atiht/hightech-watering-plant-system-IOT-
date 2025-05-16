<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if(isset($_POST['BtnSubmit'])) {
    include('./backend/classes/Database.class.php');
    include('./backend/classes/Program.class.php');
    $Program = new Program();
    
    $username = trim($Program->XssProtection($_POST['username']));
    $password = md5($_POST['password']);

    $login = $Program->selectOne(
        'hightechuser',
        '*',
        'username = :username AND password = :password',
        '',
        '',
        [
            ':username' => $username,
            ':password' => $password
        ]
    );
    if($login['status'] == 'error') {
        echo "<pre";
        print_r($login);
        exit;
    }
    if($login['status'] == 'success' && $login['data']) {
        $_SESSION['UserLogin'] = $login['data'];
        header("location: /hightech/");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("./structures/head.php") ?>
    <link href="https://dsil.kmutt.ac.th/service/assets/plugins/apexcharts-bundle/css/apexcharts.css" rel="stylesheet" />
    <title>WCK & IOT | Signin</title>
</head>

<body class="w-100 vh-100 d-flex justify-content-center align-items-center">
    <div class="container">
        <div class="row g-0 align-items-center justify-content-center">
            <div class="col-xl-6">
                <div class="card border-0">
                    <div class="card-body p-5">
                        <div class="card-title text-center">
                            <img src="./logo/logo.png" alt="D-CheckIn Login" class="img-fluid" width="120">
                            <h1 class="mb-4 text-dark"><strong>WCK & IOT | Signin</strong></h1>
                        </div>
                        <hr>
                        <form class="row g-3 needs-validation" method="POST" action="" novalidate>
                            <div class="col-12">
                                <label for="username" class="form-label"><strong>username :</strong></label>
                                <div class="input-group">
                                    <div class="input-group-text bg-transparent"><i class="bi bi-person-circle" style="font-size: 18pt;"></i></div>
                                    <input type="text" class="form-control" name="username" id="username" placeholder="enter username" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="password" class="form-label"><strong>password :</strong></label>
                                <div class="input-group">
                                    <div class="input-group-text bg-transparent"><i class="bi bi-lock" style="font-size: 18pt;"></i></div>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="enter password" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-grid">
                                    <button class="btn btn-dark" type="submit" id="BtnSubmit" name="BtnSubmit">signin</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("./structures/script.php") ?>
</body>

</html>