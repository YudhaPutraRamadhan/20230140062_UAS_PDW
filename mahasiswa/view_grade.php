<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Lihat Nilai Laporan';
$activePage = 'my_courses';

require_once '../config.php';
require_once 'templates/header_mahasiswa.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$modul_id = null;
$judul_modul = '';
$nama_praktikum = '';
$nilai = null;
$feedback = '';
$file_laporan = '';
$tanggal_upload = '';
$praktikum_id_terkait = null;

if (isset($_GET['modul_id']) && !empty(trim($_GET['modul_id']))) {
    $modul_id = trim($_GET['modul_id']);

    $sql_laporan = "SELECT
                        l.file_laporan, l.tanggal_upload, l.nilai, l.feedback, l.status,
                        m.judul_modul, m.praktikum_id, mp.nama_praktikum
                    FROM
                        laporan l
                    JOIN
                        modul m ON l.modul_id = m.id
                    JOIN
                        mata_praktikum mp ON m.praktikum_id = mp.id
                    WHERE
                        l.modul_id = ? AND l.user_id = ?";
    $stmt_laporan = $conn->prepare($sql_laporan);
    $stmt_laporan->bind_param("ii", $modul_id, $user_id);
    $stmt_laporan->execute();
    $result_laporan = $stmt_laporan->get_result();

    if ($result_laporan->num_rows === 1) {
        $row = $result_laporan->fetch_assoc();
        $judul_modul = $row['judul_modul'];
        $nama_praktikum = $row['nama_praktikum'];
        $nilai = $row['nilai'];
        $feedback = $row['feedback'];
        $file_laporan = $row['file_laporan'];
        $tanggal_upload = date('d M Y H:i', strtotime($row['tanggal_upload']));
        $praktikum_id_terkait = $row['praktikum_id'];

        if ($row['status'] !== 'graded') {
            $nilai = "Belum Dinilai";
            $feedback = "Laporan Anda belum dinilai oleh asisten.";
        }

    } else {
        header("Location: my_courses.php?status=laporan_not_found_for_modul");
        exit();
    }
    $stmt_laporan->close();

} else {
    header("Location: my_courses.php");
    exit();
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Nilai Laporan Modul:</h2>
    <h3 class="text-xl text-blue-700 mb-4"><?php echo htmlspecialchars($nama_praktikum); ?> - <?php echo htmlspecialchars($judul_modul); ?></h3>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Detail Laporan Anda</h3>
        <p class="mb-2"><strong>File Laporan Anda:</strong>
            <?php if (!empty($file_laporan)): ?>
                <a href="../<?php echo htmlspecialchars($file_laporan); ?>" target="_blank" class="text-blue-500 hover:underline">
                    Unduh Laporan Anda (<?php echo basename($file_laporan); ?>)
                </a>
            <?php else: ?>
                Tidak ada file laporan yang diunggah.
            <?php endif; ?>
        </p>
        <p class="mb-4"><strong>Tanggal Unggah:</strong> <?php echo htmlspecialchars($tanggal_upload); ?></p>

        <hr class="my-4">

        <h3 class="text-xl font-bold text-gray-800 mb-4">Hasil Penilaian</h3>
        <div class="mb-4">
            <p class="mb-2"><strong>Nilai Anda:</strong>
                <span class="text-3xl font-extrabold <?php echo ($nilai === "Belum Dinilai") ? 'text-gray-500' : ($nilai >= 60 ? 'text-green-600' : 'text-red-600'); ?>">
                    <?php echo htmlspecialchars($nilai); ?>
                </span>
                <?php if ($nilai !== "Belum Dinilai"): ?>
                    <span class="text-gray-600 text-sm">/ 100</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="mb-4">
            <p class="mb-2"><strong>Feedback Asisten:</strong></p>
            <div class="bg-gray-100 p-4 rounded-md border border-gray-200">
                <p class="text-gray-800 whitespace-pre-wrap"><?php echo htmlspecialchars($feedback); ?></p>
            </div>
        </div>
    </div>

    <div class="text-center mt-8">
        <a href="detail_praktikum.php?id=<?php echo htmlspecialchars($praktikum_id_terkait); ?>" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Kembali ke Detail Praktikum
        </a>
    </div>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
if ($conn->ping()) {
    $conn->close();
}
?>