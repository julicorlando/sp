<?php
/**
 * Payment Delete Handler
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

// Get parameters
$patient_id = intval($_POST['patient_id'] ?? 0);
$payment_id = intval($_POST['payment_id'] ?? 0);
$csrf_token = $_POST['csrf_token'] ?? '';

// Verify CSRF token
if (!verifyCSRFToken($csrf_token)) {
    redirect('dashboard.php', 'Token de segurança inválido.', 'error');
}

if (!$patient_id || !$payment_id) {
    redirect('dashboard.php', 'Parâmetros inválidos.', 'error');
}

// Verify patient belongs to current user and payment exists
$sql = "SELECT pg.id, p.nome 
        FROM pagamentos pg 
        JOIN pacientes p ON pg.paciente_id = p.id 
        WHERE pg.id = ? AND pg.paciente_id = ? AND p.usuario_id = ?";

$pagamento = $db->fetch($sql, [$payment_id, $patient_id, $user['id']]);
if (!$pagamento) {
    redirect('dashboard.php', 'Pagamento não encontrado.', 'error');
}

// Delete payment from database
try {
    $sql = "DELETE FROM pagamentos WHERE id = ?";
    $db->execute($sql, [$payment_id]);
    redirect("patient_details.php?id=$patient_id", 'Pagamento excluído com sucesso!');
} catch (Exception $e) {
    redirect("patient_details.php?id=$patient_id", 'Erro ao excluir pagamento: ' . $e->getMessage(), 'error');
}
?>