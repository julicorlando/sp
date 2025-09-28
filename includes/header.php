<?php
/**
 * Common Header and Navigation
 * Sistema de Pacientes - PHP Migration
 */

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$db = getDB();

// Controle de login/aprovação para navegação protegida
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;
if ($isLoggedIn) {
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    // Se usuário não está aprovado, força logout e bloqueia navegação
    if (!$user || !$user['aprovado']) {
        session_destroy();
        redirect('login.php?error=acesso_bloqueado');
        exit;
    }
}

// Get flash message if any
$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . (defined('APP_NAME') ? APP_NAME : 'Sistema de Pacientes') : (defined('APP_NAME') ? APP_NAME : 'Sistema de Pacientes'); ?></title>
    
    <!-- CSS Files -->
    <?php
    if (isset($css_files) && is_array($css_files)) {
        foreach ($css_files as $css_file) {
            $href = (strpos($css_file, '/') !== false) ? $css_file : "static/css/$css_file";
            echo '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($href) . '">' . "\n";
        }
    } else {
        echo '<link rel="stylesheet" type="text/css" href="static/css/styles.css">' . "\n";
    }
    ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Additional JS Files -->
    <?php
    if (isset($js_files) && is_array($js_files)) {
        foreach ($js_files as $js_file) {
            $src = (strpos($js_file, '/') !== false) ? $js_file : "static/js/$js_file";
            echo '<script src="' . htmlspecialchars($src) . '" defer></script>' . "\n";
        }
    }
    ?>
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">

<?php if (isset($show_nav) && $show_nav !== false): ?>
    <nav>
        <ul class="nav-links">
            <li><a href="index.php">Página Inicial</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="dashboard.php">Listar Pacientes</a></li>
                <li><a href="patient_add.php">Cadastrar Paciente</a></li>
                <!--<li><a href="register.php">Cadastrar Novo Usuário</a></li>-->
                <li>
                    <a href="logout.php" class="btn-modern sair-btn">
                        <span>Sair</span>
                        <svg height="20" width="20" viewBox="0 0 24 24" class="sair-icon">
                            <path fill="#a18cd1" d="M16 13v-2H7V8l-5 4 5 4v-3zm7-10v20a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-6h2v6h12V3H9v6H7V3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2z"/>
                        </svg>
                    </a>
                </li>
            <?php else: ?>
                <li><a href="login.php">Acesso Restrito</a></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php if ($flash): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <script>
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    </script>
<?php endif; ?>

<div class="content">