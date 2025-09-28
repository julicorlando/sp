<?php
/**
 * File Delete Handler
 * Sistema de Pacientes - PHP Migration
 */

require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$db = getDB();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
    exit;
}

$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$user || !$user['aprovado']) {
    session_destroy();
    redirect('login.php?error=acesso_bloqueado');
    exit;
}

// Only accepts POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php', 'Método inválido.', 'error');
}

// Get parameters
$patient_id = intval($_POST['patient_id'] ?? 0);
$file_id = intval($_POST['file_id'] ?? 0);
$csrf_token = $_POST['csrf_token'] ?? '';

// Verify CSRF token
if (!verifyCSRFToken($csrf_token)) {
    redirect('dashboard.php', 'Token de segurança inválido.', 'error');
}

if (!$patient_id || !$file_id) {
    redirect('dashboard.php', 'Parâmetros inválidos.', 'error');
}

// Verify patient belongs to user and file exists
$sql = "SELECT a.id, a.arquivo_path 
        FROM arquivos a 
        JOIN pacientes p ON a.paciente_id = p.id 
        WHERE a.id = ? AND a.paciente_id = ? AND p.usuario_id = ?";
$arquivo = $db->fetch($sql, [$file_id, $patient_id, $user['id']]);
if (!$arquivo) {
    redirect('dashboard.php', 'Arquivo não encontrado.', 'error');
}

// Delete file from disk
if (file_exists($arquivo['arquivo_path'])) {
    @unlink($arquivo['arquivo_path']);
}

// Delete record from database
try {
    $sql = "DELETE FROM arquivos WHERE id = ?";
    $db->execute($sql, [$file_id]);
    redirect("patient_details.php?id=$patient_id", 'Arquivo excluído com sucesso!');
} catch (Exception $e) {
    redirect("patient_details.php?id=$patient_id", 'Erro ao excluir arquivo: ' . $e->getMessage(), 'error');
}
?>