<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $user_id_to_delete = trim($_GET['id']);

    if ($user_id_to_delete == $_SESSION['user_id']) {
        header("Location: users.php?status=self_delete_error");
        exit();
    }

    $sql_delete = "DELETE FROM users WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $user_id_to_delete);

    if ($stmt_delete->execute()) {
        $stmt_delete->close();
        $conn->close();
        header("Location: users.php?status=hapus_sukses");
        exit();
    } else {
        $stmt_delete->close();
        $conn->close();
        header("Location: users.php?status=hapus_gagal&error=" . urlencode($stmt_delete->error));
        exit();
    }
} else {
    header("Location: users.php");
    exit();
}

?>