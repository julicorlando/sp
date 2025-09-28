<?php
session_start();

// Se já está logado, vai direto para o painel
if (!empty($_SESSION['admin_aprovador'])) {
    header('Location: admin_panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['senha'] === 'Bento121021@') {
        $_SESSION['admin_aprovador'] = true;
        header('Location: admin_panel.php');
        exit;
    } else {
        $erro = "Senha inválida!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Área do Administrador</title>
    <link rel="stylesheet" href="static/css/admin.css">
</head>
<body>
<div class="admin-login-container">
    <h2>Área do Administrador</h2>
    <?php if (!empty($erro)) echo '<p class="error">'.$erro.'</p>'; ?>
    <form method="post">
        <label for="senha">Senha:</label>
        <input type="password" name="senha" id="senha" required>
        <button type="submit" class="btn-admin">Entrar</button>
    </form>
</div>
</body>
</html>