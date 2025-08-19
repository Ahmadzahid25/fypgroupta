<?php
session_start();
error_reporting(0);
include("includes/config.php");

if (isset($_POST['submit'])) {
    $icnumber = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT * FROM users WHERE icnumber = ?");
    $stmt->bind_param("s", $icnumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['login'] = $row['icnumber'];
        $_SESSION['id'] = $row['id'];
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 1;

        mysqli_query($con, "INSERT INTO userlog(uid, username, userip, status) VALUES ('".$_SESSION['id']."', '".$_SESSION['login']."', '$uip', '$status')");

        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("Location: http://$host$uri/dashboard.php");
        exit();
    } else {
        $_SESSION['login'] = $_POST['username'];
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 0;
        mysqli_query($con, "INSERT INTO userlog(username, userip, status) VALUES ('".$_SESSION['login']."', '$uip', '$status')");
        $errormsg = "Nombor IC atau kata laluan salah.";
    }
}

if (isset($_POST['change'])) {
    $icnumber = mysqli_real_escape_string($con, $_POST['icnumber']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $con->prepare("SELECT * FROM users WHERE icnumber = ? AND contactno = ?");
    $stmt->bind_param("ss", $icnumber, $contact);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $stmt = $con->prepare("UPDATE users SET password = ? WHERE icnumber = ? AND contactno = ?");
        $stmt->bind_param("sss", $password, $icnumber, $contact);
        $stmt->execute();
        $msg = "Kata laluan berjaya ditukar.";
    } else {
        $errormsg = "Nombor IC atau nombor telefon tidak sah.";
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>PTA | Log Masuk Pengguna</title>
    <link rel="icon" href="logopta.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <script type="text/javascript">
    function valid() {
        if (document.forgot.password.value !== document.forgot.confirmpassword.value) {
            alert("Kata laluan dan pengesahan tidak sepadan!");
            document.forgot.confirmpassword.focus();
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
<div id="login-page">
    <div class="container">
        <h3 align="center" style="color:#fff">
            <a href="https://ptadmin.tvetikmb.com/" style="color:#fff">PTA Service Management System</a>
        </h3>
        <hr/>
        <form class="form-login" name="login" method="post">
            <h2 class="form-login-heading">Log Masuk Sekarang</h2>

            <p style="color:red"><?php if ($errormsg) echo htmlentities($errormsg); ?></p>
            <p style="color:green"><?php if ($msg) echo htmlentities($msg); ?></p>

            <div class="login-wrap">
                <input type="text" class="form-control" name="username" placeholder="Nombor IC" required autofocus><br>
                <input type="password" class="form-control" name="password" required placeholder="Kata Laluan">
                <label class="checkbox">
                    <span class="pull-right">
                        <a data-toggle="modal" href="#myModal">Lupa Kata Laluan?</a>
                    </span>
                </label>
                <button class="btn btn-theme btn-block" name="submit" type="submit"><i class="fa fa-lock"></i> LOG MASUK</button>
                <hr>
                <div class="registration">
                    Belum berdaftar?<br/>
                    <a class="" href="registration.php">Daftar Sekarang</a>
                </div>
            </div>
        </form>

        <!-- Modal Lupa Kata Laluan -->
        <form class="form-login" name="forgot" method="post" onsubmit="return valid();">
            <div id="myModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Lupa Kata Laluan?</h4>
                        </div>
                        <div class="modal-body">
                            <p>Sila masukkan maklumat untuk menetapkan semula kata laluan anda.</p>
                            <input type="text" name="icnumber" placeholder="Nombor IC" class="form-control" required><br>
                            <input type="text" name="contact" placeholder="Nombor Telefon" class="form-control" required><br>
                            <input type="password" class="form-control" placeholder="Kata Laluan Baharu" name="password" required><br>
                            <input type="password" class="form-control" placeholder="Sahkan Kata Laluan" name="confirmpassword" required>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-default" type="button" data-dismiss="modal">Batal</button>
                            <button class="btn btn-theme" type="submit" name="change">Hantar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- Tamat Modal -->
    </div>
</div>

<!-- JS -->
<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/jquery.backstretch.min.js"></script>
<script>
    $.backstretch("assets/img/login-bg.jpg", {speed: 500});
</script>
</body>
</html>
