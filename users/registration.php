<?php
session_start();
include('includes/config.php');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$msg = "";
$error = "";
$fullname = $contactno = $contactno2 = $icnumber = $address = "";

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Ralat penghantaran borang. Sila cuba lagi.";
    } else {
        // Sanitize inputs
        $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
        $contactno = filter_input(INPUT_POST, 'contactno', FILTER_SANITIZE_STRING);
        $contactno2 = filter_input(INPUT_POST, 'contactno2', FILTER_SANITIZE_STRING);
        $icnumber = filter_input(INPUT_POST, 'icnumber', FILTER_SANITIZE_STRING);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

        if (empty($fullname) || empty($contactno) || empty($icnumber) || empty($address)) {
            $error = "Sila isi semua medan yang diperlukan.";
        } else {
            // Check if IC number already exists
            $stmt = $con->prepare("SELECT icnumber FROM users WHERE icnumber = ?");
            $stmt->bind_param("s", $icnumber);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Nombor IC telah didaftarkan. Sila gunakan nombor IC lain.";
            } else {
                // Set password as IC number
                $hashed_password = password_hash($icnumber, PASSWORD_DEFAULT);
                $status = 1;

                // Gunakan IC sebagai userEmail
                $stmt = $con->prepare("INSERT INTO users (fullName, userEmail, password, contactno, contactno2, icnumber, address, status) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssi", $fullname, $icnumber, $hashed_password, $contactno, $contactno2, $icnumber, $address, $status);

                if ($stmt->execute()) {
                    $msg = "Pendaftaran berjaya. Anda boleh log masuk menggunakan Nombor IC sebagai ID dan kata laluan.";
                    $fullname = $contactno = $contactno2 = $icnumber = $address = "";
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error = "Pendaftaran gagal: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>PTA | Pendaftaran Pengguna</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logopta.png" type="image/png">
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
</head>
<body>
    <div id="login-page">
        <div class="container">
            <h3 align="center" style="color:#fff">
                <a href="https://ptadmin.tvetikmb.com/" style="color:#fff">PTA Service Management System</a>
            </h3>
            <hr />
            <form class="form-login" method="post">
                <h2 class="form-login-heading">Pendaftaran Pengguna</h2>

                <?php if ($msg): ?>
                    <div class="alert alert-success"><?php echo htmlentities($msg); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                <?php endif; ?>

                <div class="login-wrap">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Nama Penuh" name="fullname" required value="<?php echo htmlentities($fullname); ?>">
                    </div>

                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Nombor IC" name="icnumber" required value="<?php echo htmlentities($icnumber); ?>">
                        <small class="form-text text-muted">IC ini juga akan digunakan sebagai kata laluan anda.</small>
                    </div>

                    <div class="form-group">
                        <input type="tel" class="form-control" name="contactno" placeholder="Nombor Telefon Utama" required pattern="[0-9]+" value="<?php echo htmlentities($contactno); ?>">
                    </div>

                    <div class="form-group">
                        <input type="tel" class="form-control" name="contactno2" placeholder="Nombor Telefon Kedua (Optional)" pattern="[0-9]+" value="<?php echo htmlentities($contactno2); ?>">
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" name="address" placeholder="Alamat" required rows="4"><?php echo htmlentities($address); ?></textarea>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <button class="btn btn-theme btn-block" type="submit" name="submit">
                        <i class="fa fa-user"></i> Daftar
                    </button>
                    <hr>
                    <div class="registration">
                        Sudah berdaftar?<br/>
                        <a href="index.php">Log Masuk</a>
                    </div>
                </div>
            </form>
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
