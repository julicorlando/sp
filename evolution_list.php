<?php
/**
 * Evolution List Page
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

// Get patient ID
$patient_id = intval($_GET['patient_id'] ?? 0);

if (!$patient_id) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Get patient details (ensure it belongs to current user)
$sql = "SELECT id, nome FROM pacientes WHERE id = ? AND usuario_id = ?";
$paciente = $db->fetch($sql, [$patient_id, $user_id]);

if (!$paciente) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Get evolutions
$sql = "SELECT * FROM evolucoes WHERE paciente_id = ? ORDER BY data DESC";
$evolucoes = $db->fetchAll($sql, [$patient_id]);

$page_title = 'Evoluções - ' . $paciente['nome'];
$css_files = ['evolucao.css'];
$show_nav = true;

require_once 'includes/header.php';
?>

<h1>Evoluções do Paciente</h1>
<h2>Paciente: <?php echo htmlspecialchars($paciente['nome']); ?></h2>

<div class="navigation-links">
    <a href="patient_details.php?id=<?php echo $patient_id; ?>" class="btn">Voltar aos Detalhes</a>
    <a href="dashboard.php" class="btn">Lista de Pacientes</a>
    <!--<a href="evolution_add.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">Adicionar Evolução</a>-->
</div>

<section class="evolucoes-container">
    <?php if (!empty($evolucoes)): ?>
        <?php foreach ($evolucoes as $evolucao): ?>
            <div class="evolucao-item">
                <div class="evolucao-header">
                    <span class="evolucao-data">
                        <strong>Data:</strong> <?php echo isset($evolucao['data']) ? formatDate($evolucao['data'], 'd/m/Y H:i') : ''; ?>
                    </span>
                    <div class="evolucao-actions">
                        <a href="evolution_edit.php?patient_id=<?php echo $patient_id; ?>&id=<?php echo $evolucao['id']; ?>" class="btn btn-small">Editar</a>
                        <form action="evolution_delete.php" method="post" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="evolution_id" value="<?php echo $evolucao['id']; ?>">
                            <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                            <button type="submit" class="btn btn-small btn-danger" 
                                    onclick="return confirm('Tem certeza que deseja excluir esta evolução?');">
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
                <div class="evolucao-content">
                    <?php echo nl2br(htmlspecialchars(isset($evolucao['conteudo']) ? $evolucao['conteudo'] : '')); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-message">
            <p>Nenhuma evolução registrada para este paciente.</p>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>