<?php
/**
 * Logout Page
 * Sistema de Pacientes - PHP Migration
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        redirect('index.php', 'Token de segurança inválido.', 'error');
    }
}

// Logout user
$auth->logout();

// Redirect to homepage with message
redirect('index.php', 'Logout realizado com sucesso!');
?>