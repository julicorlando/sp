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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $valor = sanitize($_POST['valor'] ?? '');
    $forma_pagamento = sanitize($_POST['forma_pagamento'] ?? '');
    
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
    
    // If no errors, insert payment
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO pagamentos (paciente_id, valor, forma_pagamento) VALUES (?, ?, ?)";
            $db->execute($sql, [$patient_id, floatval($valor), $forma_pagamento]);
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
        
        <div class="form-buttons">
            <button type="submit" class="btn-primary">Salvar Pagamento</button>
            <a href="patient_details.php?id=<?php echo $patient_id; ?>" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>