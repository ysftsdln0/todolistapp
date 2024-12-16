<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Veritabanı bağlantısı
$conn = new mysqli("localhost", "root", "root", "ToDoList");
if ($conn->connect_error) {
    die("<p class='error'>Veritabanı bağlantı hatası: " . htmlspecialchars($conn->connect_error) . "</p>");
}
$conn->set_charset("utf8mb4");

// oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// not silme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_note'])) {
    $note_id = intval($_POST['note_id']);

    $query = "DELETE FROM notes WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ii", $note_id, $user_id);
        if ($stmt->execute()) {
            header("Location: mainpage.php");
            exit;
        } else {
            $error_message = "Not silinirken bir hata oluştu: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $error_message = "Silme sorgusu hazırlanırken bir hata oluştu: " . htmlspecialchars($conn->error);
    }
}

// not ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_note'])) {
    $note_text = trim($_POST['note_text'] ?? ''); // Formdan gelen not
    $priority = intval($_POST['priority'] ?? 0); // Formdan gelen öncelik değeri

    if (empty($note_text) || $priority < 1 || $priority > 10) {
        $error_message = "Lütfen geçerli bir not metni ve öncelik girin (1-10 arasında bir değer).";
    } else {
        $query = "INSERT INTO notes (user_id, note_text, priority, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("isi", $user_id, $note_text, $priority);
            if ($stmt->execute()) {
                header("Location: mainpage.php");
                exit;
            } else {
                $error_message = "Not eklenirken bir hata oluştu: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $error_message = "Sorgu hazırlama sırasında bir hata oluştu: " . htmlspecialchars($conn->error);
        }
    }
}

// notları çekme
$query = "SELECT id, note_text, priority, created_at FROM notes WHERE user_id = ? ORDER BY priority DESC, created_at DESC";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $notes = $stmt->get_result();
} else {
    $error_message = "Notlar yüklenirken bir hata oluştu: " . htmlspecialchars($conn->error);
    $notes = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yapılacaklar Listesi</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .error { color: red; font-weight: bold; }
        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .task-left {
            display: flex;
            align-items: center;
            flex-grow: 1;
        }
        .task-text {
            margin-right: 10px;
        }
        .task-priority {
            margin-right: 10px;
            color: #666;
        }
        .task-date {
            font-size: 0.8em;
            color: #888;
            margin-right: 10px;
        }
        .delete-btn {
            color: red;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        .delete-btn:hover {
            color: darkred;
        }
    </style>
</head>
<body>
<div class="container">
    <nav class="navbar">
        <a href="mainpage.php" class="nav-left"><i class="fas fa-home"></i> Ana Sayfa</a>
        <span class="nav-center"><i class="fas fa-list"></i> Yapılacaklar Listesi</span>
        <div class="nav-right">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </div>
    </nav>

    <h1><i class="fas fa-tasks"></i> Yapılacaklar Listesi</h1>

    <?php if (isset($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <div class="login-container">
        <form method="POST" action="mainpage.php">
            <input type="text" name="note_text" placeholder="Yeni not ekleyin..." required>
            <input type="number" name="priority" placeholder="Öncelik (1-10)" min="1" max="10" required>
            <button type="submit" name="add_note"><i class="fas fa-plus-circle"></i> Ekle</button>
        </form>
    </div>

    <ul id="task-list">
        <?php if ($notes && $notes->num_rows > 0): ?>
            <?php while ($note = $notes->fetch_assoc()): ?>
                <li class="task-item">
                    <div class="task-left">
                        <span class="task-text"><?php echo htmlspecialchars($note['note_text']); ?></span>
                        <span class="task-priority">(Öncelik: <?php echo intval($note['priority']); ?>)</span>
                        <span class="task-date"><?php echo date('d-m-Y H:i', strtotime($note['created_at'])); ?></span>
                    </div>
                    <form method="POST" action="mainpage.php" style="display:inline;">
                        <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                        <button type="submit" name="delete_note" class="delete-btn" onclick="return confirm('Bu notu silmek istediğinizden emin misiniz?');">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Henüz bir not eklenmemiş.</p>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>