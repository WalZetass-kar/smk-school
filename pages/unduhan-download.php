<?php
/**
 * pages/unduhan-download.php — /unduhan/download/{id}
 * Handler pengunduhan berkas & increment counter download
 */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(BASE_URL . '/unduhan');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM downloads WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    setFlash('error', 'Dokumen tidak ditemukan.');
    redirect(BASE_URL . '/unduhan');
}

// Increment downloads count
$db->prepare("UPDATE downloads SET downloads_count = downloads_count + 1 WHERE id = ?")->execute([$id]);

$filePath = BASE_PATH . '/' . $doc['file_path'];

if (file_exists($filePath) && is_file($filePath)) {
    $filename = basename($doc['file_path']);
    $mime = mime_content_type($filePath) ?: 'application/octet-stream';

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . addslashes($doc['title'] . '.' . $doc['file_ext']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    // If file is external or relative asset URL
    redirect(BASE_URL . '/' . $doc['file_path']);
}
