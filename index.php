<?php
/**
 * Homepage - Index
 * Sistema de Pacientes - PHP Migration
 */

// Set page variables
$page_title = 'Seu Sistema de Pacientes';
$css_files = ['homepage.css'];
$body_class = 'homepage';
$show_nav = true;

// Include common header
require_once 'includes/header.php';

// Get current user if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $db = getDB();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    // Se o usuário não estiver aprovado, força logout e bloqueia navegação
    if (!$user || !$user['aprovado']) {
        session_destroy();
        redirect('login.php?error=acesso_bloqueado');
        exit;
    }
}
?>

<div class="content modern-content">
    <h1>
        <strong>Bem-vindo(a)</strong>
        <?php echo $user ? htmlspecialchars($user['first_name']) : ''; ?>
    </h1>
    <div class="lgpd-info" style="margin:32px auto;max-width:600px;background:#fffbe8;border-radius:14px;padding:18px 22px;box-shadow:0 2px 16px #fae;">
        <p>
            Você está autenticado e o uso deste sistema está em conformidade com a <strong>Lei Geral de Proteção de Dados (LGPD)</strong>.
            Seus dados são protegidos e utilizados apenas para fins autorizados de gestão de pacientes, conforme política interna.
            O acesso às informações é restrito ao seu usuário e auditado. Ao continuar, você concorda com o tratamento responsável das informações pessoais.
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>