<?php
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$db = getDB();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$patient_id = intval($_GET['patient_id'] ?? $_POST['patient_id'] ?? 0);

if (!$patient_id) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

$sql = "SELECT id FROM pacientes WHERE id = ? AND usuario_id = ?";
$paciente = $db->fetch($sql, [$patient_id, $user_id]);
if (!$paciente) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $conteudo = sanitize($_POST['conteudo'] ?? '');
    $data = $_POST['data'] ?? date('Y-m-d H:i:s');

    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token de segurança inválido.';
    }
    if (empty($conteudo)) {
        $errors[] = 'Conteúdo da evolução é obrigatório.';
    }
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO evolucoes (paciente_id, data, conteudo) VALUES (?, ?, ?)";
            $db->execute($sql, [$patient_id, $data, $conteudo]);
            redirect("evolution_list.php?patient_id=$patient_id", 'Evolução adicionada com sucesso!');
        } catch (Exception $e) {
            $errors[] = 'Erro ao adicionar evolução: ' . $e->getMessage();
        }
    }
}

// Página do formulário de adição
$page_title = 'Adicionar Evolução';
$css_files = ['evolucao.css'];
$show_nav = true;
require_once 'includes/header.php';
?>

<h1>Adicionar Evolução</h1>
<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="form-evolucao">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
    <p>
        <label for="id_data">Data:</label>
        <input type="datetime-local" name="data" id="id_data"
               value="<?php echo htmlspecialchars(date('Y-m-d\TH:i')); ?>">
    </p>
    <p>
        <label for="id_conteudo">Conteúdo da Evolução:</label>
        <textarea name="conteudo" id="id_conteudo" required 
                  placeholder="Escreva a evolução do paciente aqui..."><?php echo htmlspecialchars($_POST['conteudo'] ?? ''); ?></textarea>
    </p>
    <div class="form-buttons">
        <button type="submit" class="btn-primary">Salvar Evolução</button>
        <a href="evolution_list.php?patient_id=<?php echo $patient_id; ?>" class="btn-secondary">Cancelar</a>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>