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

$filePath = BASE_PATH . '/' . ltrim($doc['file_path'], '/\\');

// Anti Path Traversal Validation
if (file_exists($filePath) && is_file($filePath) && isSafePath($filePath, BASE_PATH)) {
    $mime = mime_content_type($filePath) ?: 'application/octet-stream';

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    $safeName = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', $doc['title']) ?: 'dokumen';
    header('Content-Disposition: attachment; filename="' . $safeName . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $doc['file_ext']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    setFlash('error', 'File fisik dokumen tidak ditemukan atau tidak valid.');
    redirect(BASE_URL . '/unduhan');
}
