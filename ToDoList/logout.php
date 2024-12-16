<?php
session_start();

// Oturumu sonlandır
session_unset();
session_destroy();


header("Location: index.php");
exit;