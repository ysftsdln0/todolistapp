<?php
session_start();

$conn = new mysqli("localhost", "root", "root", "ToDoList");

// veritabanı bağlantı kontrolü
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // ID oluşturma
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header("Location: mainpage.php");
                exit;
            } else {
                $error_message = "Hatalı şifre!";
            }
        } else {
            $error_message = "Kullanıcı bulunamadı!";
        }
    } else {
        $error_message = "Bir hata oluştu, lütfen tekrar deneyin.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap!</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 1em;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="login-container">
    <h2>Giriş Yap</h2>

    <!-- hata Mesajı -->
    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <label for="username">
            <i class="fas fa-user"></i>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Kullanıcı Adı
        </label>
        <input type="text" id="username" name="username" placeholder="Kullanıcı Adınızı Giriniz" required>
        <label for="password">
            <i class="fas fa-lock"></i>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Şifre
        </label>
        <input type="password" id="password" name="password" placeholder="Şifrenizi Giriniz" required>
        <button type="submit">Giriş Yap</button>
        <a href="register.php" class="button">Üyeliğiniz Yoksa Kayıt Olun</a>
    </form>
    <div class="footer">
        Made by Yusuf Efe Tasdelen
    </div>
</div>
</body>
</html>