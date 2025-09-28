<?php
/**
 * Add Payment Form
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

$errors = [];

// Handle form submission - Updated with new payment fields
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $valor = sanitize($_POST['valor'] ?? '');
    $forma_pagamento = sanitize($_POST['forma_pagamento'] ?? '');
    // Novos campos de pagamento
    $recibo_receita_saude = sanitize($_POST['recibo_receita_saude'] ?? 'Não');
    $tipo_pagamento = sanitize($_POST['tipo_pagamento'] ?? 'Particular');
    $valor_intermediado = sanitize($_POST['valor_intermediado'] ?? '');
    $observacoes_pagamento = sanitize($_POST['observacoes_pagamento'] ?? '');
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token de segurança inválido.';
    }
    
    // Validate required fields
    if (empty($valor)) {
        $errors[] = 'Valor é obrigatório.';
    } elseif (!is_numeric($valor) || floatval($valor) <= 0) {
        $errors[] = 'Valor deve ser um número positivo.';
    }
    
    if (empty($forma_pagamento)) {
        $errors[] = 'Forma de pagamento é obrigatória.';
    }
    
    // Validar valor intermediado se tipo não for particular
    if ($tipo_pagamento !== 'Particular' && !empty($valor_intermediado)) {
        if (!is_numeric($valor_intermediado) || floatval($valor_intermediado) < 0) {
            $errors[] = 'Valor intermediado deve ser um número válido.';
        }
    }
    
    // If no errors, insert payment
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO pagamentos (
                        paciente_id, valor, forma_pagamento, recibo_receita_saude, 
                        tipo_pagamento, valor_intermediado, observacoes_pagamento
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $patient_id, floatval($valor), $forma_pagamento, $recibo_receita_saude,
                $tipo_pagamento, 
                ($valor_intermediado !== '' ? floatval($valor_intermediado) : null),
                $observacoes_pagamento
            ];
            $db->execute($sql, $params);
            redirect("patient_details.php?id=$patient_id", 'Pagamento adicionado com sucesso!');
        } catch (Exception $e) {
            $errors[] = 'Erro ao adicionar pagamento: ' . $e->getMessage();
        }
    }
}

// Get select options
$options = getSelectOptions();

// Set page variables
$page_title = 'Adicionar Pagamento - ' . $paciente['nome'];
$css_files = ['pagamento.css'];
$show_nav = true;

// Include header
require_once 'includes/header.php';
?>

<!-- Título da Página -->
<h1>Adicionar Pagamento</h1>
<h2>Paciente: <?php echo htmlspecialchars($paciente['nome']); ?></h2>

<!-- Erro Messages -->
<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Formulário de Pagamento -->
<main>
    <form method="post" class="form-pagamento">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <p>
            <label for="id_valor">Valor (R$):</label>
            <input type="number" name="valor" id="id_valor" required min="0.01" step="0.01"
                   value="<?php echo htmlspecialchars($_POST['valor'] ?? ''); ?>"
                   placeholder="0,00">
        </p>
        
        <p>
            <label for="id_forma_pagamento">Forma de pagamento:</label>
            <?php echo generateSelect('forma_pagamento', $options['forma_pagamento'], $_POST['forma_pagamento'] ?? '', 'id="id_forma_pagamento" required'); ?>
        </p>
        
        <!-- Novos campos conforme melhorias solicitadas -->
        <p>
            <label for="id_tipo_pagamento">Tipo de pagamento:</label>
            <?php echo generateSelect('tipo_pagamento', $options['tipo_pagamento'], $_POST['tipo_pagamento'] ?? 'Particular', 'id="id_tipo_pagamento" required onchange="toggleValorIntermediado()"'); ?>
        </p>
        
        <p id="valor_intermediado_section" style="display: none;">
            <label for="id_valor_intermediado">Valor intermediado (R$):</label>
            <input type="number" name="valor_intermediado" id="id_valor_intermediado" min="0" step="0.01"
                   value="<?php echo htmlspecialchars($_POST['valor_intermediado'] ?? ''); ?>"
                   placeholder="Valor quando há convênio ou clínica">
        </p>
        
        <p>
            <label for="id_recibo_receita_saude">Recibo emitido via Receita Saúde:</label>
            <?php echo generateSelect('recibo_receita_saude', $options['sim_nao'], $_POST['recibo_receita_saude'] ?? 'Não', 'id="id_recibo_receita_saude"'); ?>
        </p>
        
        <p>
            <label for="id_observacoes_pagamento">Observações sobre o pagamento:</label>
            <textarea name="observacoes_pagamento" id="id_observacoes_pagamento" maxlength="500" placeholder="Observações específicas sobre este pagamento..."><?php echo htmlspecialchars($_POST['observacoes_pagamento'] ?? ''); ?></textarea>
        </p>
        
        <div class="form-buttons">
            <button type="submit" class="btn-primary">Salvar Pagamento</button>
            <a href="patient_details.php?id=<?php echo $patient_id; ?>" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</main>

<!-- JavaScript para controle de exibição do valor intermediado -->
<script>
function toggleValorIntermediado() {
    const tipoPagamento = document.getElementById('id_tipo_pagamento').value;
    const valorIntermediado = document.getElementById('valor_intermediado_section');
    
    if (tipoPagamento === 'Convênio' || tipoPagamento === 'Clínica') {
        valorIntermediado.style.display = 'block';
    } else {
        valorIntermediado.style.display = 'none';
        document.getElementById('id_valor_intermediado').value = '';
    }
}

// Executar ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    toggleValorIntermediado();
});
</script>

<?php require_once 'includes/footer.php'; ?>