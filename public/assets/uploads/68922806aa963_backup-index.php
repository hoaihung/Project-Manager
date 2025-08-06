<?php
// backup-index.php - Download current index.html
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="index_backup_' . date('Y-m-d_H-i-s') . '.html"');
header('Content-Length: ' . filesize('index.html'));

readfile('index.html');
?>
