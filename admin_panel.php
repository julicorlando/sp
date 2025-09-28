<?php
session_start();
require_once 'includes/functions.php';
$db = getDB();

// Bloqueia acesso de usuários não liberados no sistema
if (isset($_SESSION['user_id'])) {
    $usuario = $db->fetch("SELECT aprovado FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if (!$usuario || !$usuario['aprovado']) {
        session_destroy();
        header('Location: login.php?error=acesso_bloqueado');
        exit;
    }
}

// Garante apenas admin acessando o painel
if (empty($_SESSION['admin_aprovador'])) {
    header('Location: admin_login.php');
    exit;
}

// Aprovar ou reprovar usuários (profissionais)
if (isset($_POST['aprovar'])) {
    $id = intval($_POST['id']);
    $db->execute("UPDATE users SET aprovado=1 WHERE id=?", [$id]);
}
if (isset($_POST['reprovar'])) {
    $id = intval($_POST['id']);
    $db->execute("UPDATE users SET aprovado=0 WHERE id=?", [$id]);
}

// Busca todos os usuários (profissionais)
$usuarios = $db->fetchAll("SELECT * FROM users");

// Busca todos os pacientes
$pacientes = $db->fetchAll("SELECT * FROM pacientes");

// Busca pagamentos dos pacientes agrupados por paciente_id
$pagamentos = $db->fetchAll("SELECT paciente_id, SUM(valor) as total FROM pagamentos GROUP BY paciente_id");
$pagamentoMap = [];
foreach ($pagamentos as $p) {
    $pagamentoMap[$p['paciente_id']] = $p['total'];
}

// Relaciona usuário -> pacientes
$userPacientes = [];
foreach ($pacientes as $paciente) {
    $userPacientes[$paciente['usuario_id']][] = $paciente;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo de Profissionais</title>
    <link rel="stylesheet" href="static/css/admin.css">
</head>
<body>
<div class="admin-panel-container">
    <h2>Painel de Aprovação de Profissionais</h2>
    <table class="admin-table">
        <tr>
            <th>Profissional</th>
            <!--<th>Email</th>-->
            <th>Nome</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($usuarios as $user): ?>
            <tr class="<?= $user['aprovado'] ? 'aprovado' : 'pendente' ?>">
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                <td><?= $user['aprovado'] ? "Liberado" : "Bloqueado" ?></td>
                <td>
                    <?php if (!$user['aprovado']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" name="aprovar" class="btn-admin">Liberar</button>
                        </form>
                    <?php else: ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" name="reprovar" class="btn-admin btn-reprovar">Bloquear</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="admin_logout.php" class="btn-admin btn-logout">Sair do Painel</a>
</div>
</body>
</html>