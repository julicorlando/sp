<?php
/**
 * File Upload Handler
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

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php', 'Método inválido.', 'error');
}

// Get patient ID
$patient_id = intval($_POST['patient_id'] ?? 0);
$csrf_token = $_POST['csrf_token'] ?? '';

// Verify CSRF token
if (!verifyCSRFToken($csrf_token)) {
    redirect('dashboard.php', 'Token de segurança inválido.', 'error');
}

if (!$patient_id) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Verify patient belongs to current user
$sql = "SELECT id, nome FROM pacientes WHERE id = ? AND usuario_id = ?";
$paciente = $db->fetch($sql, [$patient_id, $user['id']]);
if (!$paciente) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Check if file was uploaded
if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] === UPLOAD_ERR_NO_FILE) {
    redirect("patient_details.php?id=$patient_id", 'Nenhum arquivo foi selecionado.', 'error');
}

// Handle file upload
$upload_result = handleFileUpload($_FILES['arquivo'], "media/uploads/patient_$patient_id/");

if (!$upload_result['success']) {
    redirect("patient_details.php?id=$patient_id", $upload_result['message'], 'error');
}

// Save file information to database
try {
    $sql = "INSERT INTO arquivos (paciente_id, arquivo_nome, arquivo_path) VALUES (?, ?, ?)";
    $db->execute($sql, [
        $patient_id, 
        $upload_result['original_name'], 
        $upload_result['filepath']
    ]);
    redirect("patient_details.php?id=$patient_id", 'Arquivo enviado com sucesso!');
} catch (Exception $e) {
    if (file_exists($upload_result['filepath'])) {
        unlink($upload_result['filepath']);
    }
    redirect("patient_details.php?id=$patient_id", 'Erro ao salvar arquivo: ' . $e->getMessage(), 'error');
}
?>