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
    $praktikum_id = trim($_GET['id']);

    $sql_delete = "DELETE FROM mata_praktikum WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $praktikum_id);

    if ($stmt_delete->execute()) {
        $stmt_delete->close();
        header("Location: mata_praktikum.php?status=hapus_sukses");
        exit();
    } else {
        $stmt_delete->close();
        header("Location: mata_praktikum.php?status=hapus_gagal&error=" . urlencode($stmt_delete->error)); // Perbaikan sintaks header
        exit();
    }
} else {
    header("Location: mata_praktikum.php");
    exit();
}
?>