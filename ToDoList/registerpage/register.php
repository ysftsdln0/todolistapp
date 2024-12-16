<?php
$conn = new mysqli("localhost", "root", "root", "ToDoList");

if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // şifre doğrulama
    if ($password !== $confirm_password) {
        die("Şifreler eşleşmiyor! Lütfen tekrar deneyin.");
    }

    // şifre hashlenmesi
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // kullanıcı adı veya e-posta nın db dekşnden farklı olması
    $query = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($query);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        die("Kullanıcı adı veya e-posta zaten kayıtlı!");
    }

    // kullanıcıyı kaydet
    $query = "INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $full_name, $username, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        echo "Kayıt başarısız: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<link rel="stylesheet" href="register.css">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol!</title>
    <script>
        document.querySelector("form").addEventListener("submit", function(event) {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;

            if (password !== confirmPassword) {
                event.preventDefault();
                alert("Şifreler eşleşmiyor!");
            }
        });
    </script>
</head>
<body>
<div class="register-container">
    <h2>Kayıt Ol</h2>
    <form action="register.php" method="POST">
        <label for="fullname">İsim Soyisim</label>
        <input type="text" name="full_name" id="fullname" required>
        <label for="email">E-Mail</label>
        <input type="email" name="email" id="email" required>
        <label for="username">Kullanıcı Adı</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Şifre</label>
        <input type="password" name="password" id="password" required>
        <label for="confirm-password">Şifre (Tekrar)</label>
        <input type="password" name="confirm-password" id="confirm-password" required>
        <button type="submit">Kayıt Ol</button>
    </form>
</div>
</body>
</html>