<?php
/**
 * Login Page
 * Sistema de Pacientes - PHP Migration
 */

require_once 'includes/functions.php';

// Redirect if already logged in
session_start();
$db = getDB();
if (isset($_SESSION['user_id'])) {
    // Busca usuário logado e verifica se está liberado
    $user = $db->fetch("SELECT aprovado FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if ($user && $user['aprovado']) {
        redirect('dashboard.php');
    }
    // Se não estiver liberado, faz logout
    session_destroy();
}

// Mensagem de bloqueio vinda de outro local (ex: dashboard)
$error = '';
if (isset($_GET['error']) && $_GET['error'] == 'acesso_bloqueado') {
    $error = 'Seu acesso foi bloqueado pelo administrador.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token de segurança inválido.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Busca usuário pelo username
        $user = $db->fetch("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);
        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Usuário ou senha inválidos. Tente novamente.';
        } elseif (!$user['aprovado']) {
            $error = 'Seu acesso ainda não foi liberado pelo administrador.';
        } else {
            // Login OK
            $_SESSION['user_id'] = $user['id'];
            redirect('dashboard.php', 'Login realizado com sucesso!');
        }
    }
}

// Set page variables
$page_title = 'Login';
$css_files = ['login.css'];
$show_nav = false;

// Include header
require_once 'includes/header.php';
?>

<div class="login-container">
    <h2>Entrar</h2>
    
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <p>
            <label for="id_username">Usuário:</label>
            <input type="text" name="username" id="id_username" required 
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_password">Senha:</label>
            <input type="password" name="password" id="id_password" required>
        </p>
        
        <div class="button-group">
            <button type="submit" class="btn-enter">Entrar</button>
            <button type="button" class="btn-back" onclick="goBack()">Voltar</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $(".error").hide().fadeIn(1000);

        window.goBack = function() {
            if (confirm("Tem certeza de que deseja voltar sem fazer login?")) {
                window.location.href = "index.php";
            }
        };
    });
</script>

<?php require_once 'includes/footer.php'; ?>