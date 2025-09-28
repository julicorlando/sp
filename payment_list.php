<?php
/**
 * Payment List Page
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

// Get patient ID
$patient_id = intval($_GET['patient_id'] ?? 0);

if (!$patient_id) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Get patient details (ensure it belongs to current user)
$sql = "SELECT id, nome FROM pacientes WHERE id = ? AND usuario_id = ?";
$paciente = $db->fetch($sql, [$patient_id, $user['id']]);
if (!$paciente) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Get patient payments
$sql = "SELECT * FROM pagamentos WHERE paciente_id = ? ORDER BY data_pagamento DESC";
$pagamentos = $db->fetchAll($sql, [$patient_id]);

// Calculate total
$total = 0;
foreach ($pagamentos as $pagamento) {
    $total += $pagamento['valor'];
}

// Set page variables
$page_title = 'Pagamentos - ' . $paciente['nome'];
$css_files = ['pagamento.css'];
$show_nav = true;

// Include header
require_once 'includes/header.php';
?>

<!-- Título da Página -->
<h1>Pagamentos do Paciente</h1>
<h2>Paciente: <?php echo htmlspecialchars($paciente['nome']); ?></h2>

<!-- Navegação -->
<div class="navigation-links">
    <a href="patient_details.php?id=<?php echo $patient_id; ?>" class="btn">Voltar aos Detalhes</a>
    <a href="payment_add.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">Adicionar Pagamento</a>
    <a href="dashboard.php" class="btn">Lista de Pacientes</a>
</div>

<!-- Resumo -->
<section class="payment-summary">
    <div class="summary-card">
        <h3>Resumo</h3>
        <p><strong>Total de Pagamentos:</strong> <?php echo count($pagamentos); ?></p>
        <p><strong>Valor Total:</strong> <?php echo formatCurrency($total); ?></p>
    </div>
</section>

<!-- Lista de Pagamentos -->
<section class="payments-container">
    <?php if (!empty($pagamentos)): ?>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Forma de Pagamento</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagamentos as $pagamento): ?>
                    <tr>
                        <td><?php echo formatDate($pagamento['data_pagamento']); ?></td>
                        <td><?php echo htmlspecialchars($pagamento['forma_pagamento']); ?></td>
                        <td class="value"><?php echo formatCurrency($pagamento['valor']); ?></td>
                        <td>
                            <form action="payment_delete.php" method="post" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                                <input type="hidden" name="payment_id" value="<?php echo $pagamento['id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger" 
                                        onclick="return confirm('Tem certeza que deseja excluir este pagamento?');">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-message">
            <p>Nenhum pagamento registrado para este paciente.</p>
            <a href="payment_add.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-primary">
                Adicionar Primeiro Pagamento
            </a>
        </div>
    <?php endif; ?>
</section>

<style>
.payments-container {
    max-width: 900px;
    margin: 30px auto;
}

.payment-summary {
    max-width: 900px;
    margin: 30px auto;
}

.summary-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.summary-card h3 {
    margin-bottom: 15px;
    color: #8cacb4;
}

.summary-card p {
    margin: 5px 0;
    font-size: 16px;
}

.payments-table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.payments-table th,
.payments-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.payments-table th {
    background: #8cacb4;
    color: white;
    font-weight: bold;
}

.payments-table tr:hover {
    background: #f5f5f5;
}

.payments-table .value {
    font-weight: bold;
    color: #2e7d32;
}

.navigation-links {
    text-align: center;
    margin: 30px 0;
}

.navigation-links .btn {
    margin: 0 10px;
}

.btn {
    background-color: #8cacb4;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #7a9ba4;
}

.btn-small {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-primary {
    background-color: #2196f3;
}

.btn-primary:hover {
    background-color: #1976d2;
}

.btn-danger {
    background-color: #f44336;
}

.btn-danger:hover {
    background-color: #d32f2f;
}

.empty-message {
    text-align: center;
    padding: 40px;
    color: #666;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.empty-message p {
    margin-bottom: 20px;
    font-style: italic;
}

h1, h2 {
    text-align: center;
    color: #8cacb4;
}

h2 {
    font-size: 1.3em;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .payments-table {
        font-size: 14px;
    }
    
    .payments-table th,
    .payments-table td {
        padding: 10px;
    }
    
    .navigation-links .btn {
        margin: 5px;
        display: inline-block;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>