<?php 
    include __DIR__ . '/database/konfig.php';
    session_start();
    $_SESSION['error'] = false;
    if(isset($_POST['login'])) {
        $email = $_POST['email'];
        $pass = $_POST['pass'];

        $cek = mysqli_query($kon, "SELECT * FROM users WHERE email = '$email' limit 1");
        if(mysqli_num_rows($cek) > 0) {
            $user = mysqli_fetch_assoc($cek);
            if(password_verify($pass, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                header('location: index.php');
            }else {
                $_SESSION['error'] = true;
            }
        }else {
            $_SESSION['error'] = true;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    <form action="" method="post">
        <input type="text" name="email">
        <input type="password" name="pass">
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>