<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function user_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_user(): void {
    if (!user_logged_in()) {
        $current = $_SERVER['REQUEST_URI'] ?? '/agro-buana-sari/index.php';
        header('Location: /agro-buana-sari/login.php?redirect=' . urlencode($current));
        exit;
    }
}

function current_user(mysqli $conn): ?array {
    if (!user_logged_in()) return null;
    $id = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT id,nama,email,no_hp,created_at FROM users WHERE id=? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}
?>
