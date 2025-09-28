<?php
/**
 * Evolution Delete Handler
 * Sistema de Pacientes - PHP Migration
 */

require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$db = getDB();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php', 'Método inválido.', 'error');
}

$evolution_id = intval($_POST['evolution_id'] ?? 0);
$patient_id = intval($_POST['patient_id'] ?? 0);
$csrf_token = $_POST['csrf_token'] ?? '';

if (!verifyCSRFToken($csrf_token)) {
    redirect('dashboard.php', 'Token de segurança inválido.', 'error');
}
if (!$evolution_id || !$patient_id) {
    redirect('dashboard.php', 'Parâmetros inválidos.', 'error');
}

// Verify evolution belongs to a patient of the current user
$sql = "SELECT e.id FROM evolucoes e JOIN pacientes p ON e.paciente_id = p.id WHERE e.id = ? AND e.paciente_id = ? AND p.usuario_id = ?";
$evolucao = $db->fetch($sql, [$evolution_id, $patient_id, $user_id]);
if (!$evolucao) {
    redirect('dashboard.php', 'Evolução não encontrada.', 'error');
}

// Delete evolution from database
try {
    $sql = "DELETE FROM evolucoes WHERE id = ?";
    $db->execute($sql, [$evolution_id]);
    redirect("evolution_list.php?patient_id=$patient_id", 'Evolução excluída com sucesso!');
} catch (Exception $e) {
    redirect("evolution_list.php?patient_id=$patient_id", 'Erro ao excluir evolução: ' . $e->getMessage(), 'error');
}
?>