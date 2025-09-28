<?php
/**
 * Edit Evolution Page
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

$patient_id = intval($_GET['patient_id'] ?? 0);
$evolution_id = intval($_GET['id'] ?? 0);

if (!$patient_id || !$evolution_id) {
    redirect('dashboard.php', 'Evolução ou paciente não encontrado.', 'error');
}

// Get patient (ensure belongs to user)
$sql = "SELECT id, nome FROM pacientes WHERE id = ? AND usuario_id = ?";
$paciente = $db->fetch($sql, [$patient_id, $user_id]);
if (!$paciente) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Get evolution (ensure belongs to patient)
$sql = "SELECT * FROM evolucoes WHERE id = ? AND paciente_id = ?";
$evolucao = $db->fetch($sql, [$evolution_id, $patient_id]);
if (!$evolucao) {
    redirect("evolution_list.php?patient_id=$patient_id", 'Evolução não encontrada.', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $conteudo = sanitize($_POST['conteudo'] ?? '');
    $data = $_POST['data'] ?? $evolucao['data'];

    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token de segurança inválido.';
    }
    if (empty($conteudo)) {
        $errors[] = 'Conteúdo da evolução é obrigatório.';
    }
    if (empty($data)) {
        $errors[] = 'Data é obrigatória.';
    }
    if (empty($errors)) {
        try {
            $sql = "UPDATE evolucoes SET conteudo = ?, data = ? WHERE id = ? AND paciente_id = ?";
            $db->execute($sql, [$conteudo, $data, $evolution_id, $patient_id]);
            redirect("evolution_list.php?patient_id=$patient_id", 'Evolução atualizada com sucesso!');
        } catch (Exception $e) {
            $errors[] = 'Erro ao atualizar evolução: ' . $e->getMessage();
        }
    } else {
        $evolucao['conteudo'] = $conteudo;
        $evolucao['data'] = $data;
    }
}

$page_title = 'Editar Evolução - ' . $paciente['nome'];
$css_files = ['evolucao.css'];
$show_nav = true;

require_once 'includes/header.php';
?>

<div class="edit-evolucao-centralizado">
    <h1>Editar Evolução</h1>
    <h2>Paciente: <?php echo htmlspecialchars($paciente['nome']); ?></h2>
    <p class="evolucao-data">
        <strong>Data da Evolução:</strong> <?php echo formatDate($evolucao['data'], 'd/m/Y H:i'); ?>
    </p>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <main>
        <form method="post" class="form-evolucao">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <p>
                <label for="id_data">Data:</label>
                <input type="datetime-local" name="data" id="id_data" required
                       value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($evolucao['data']))); ?>">
            </p>
            <p>
                <label for="id_conteudo">Conteúdo da Evolução:</label>
                <textarea name="conteudo" id="id_conteudo" required 
                          placeholder="Escreva a evolução do paciente aqui..."><?php echo htmlspecialchars($evolucao['conteudo'] ?? ''); ?></textarea>
            </p>
            <div class="form-buttons">
                <button type="submit" class="btn-primary">Atualizar Evolução</button>
                <a href="evolution_list.php?patient_id=<?php echo $patient_id; ?>" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>
</div>

<style>
.edit-evolucao-centralizado {
    max-width: 600px;
    margin: 40px auto;
    padding: 32px 28px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.10);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.edit-evolucao-centralizado h1,
.edit-evolucao-centralizado h2,
.edit-evolucao-centralizado .evolucao-data {
    text-align: center;
    width: 100%;
}

.form-evolucao {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.form-evolucao p {
    width: 100%;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.form-evolucao label {
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.form-evolucao input[type="datetime-local"],
.form-evolucao textarea {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.form-evolucao textarea {
    min-height: 160px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    resize: vertical;
}

.form-buttons {
    width: 100%;
    display: flex;
    gap: 18px;
    justify-content: center;
    margin-top: 30px;
}

.btn-primary,
.btn-secondary {
    padding: 12px 28px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #8cacb4;
    color: white;
}

.btn-primary:hover {
    background-color: #7a9ba4;
}

.btn-secondary {
    background-color: #757575;
    color: white;
}

.btn-secondary:hover {
    background-color: #616161;
}

.error-messages {
    width: 100%;
    margin: 20px 0;
    text-align: center;
}

.error {
    background-color: #ffebee;
    color: #d32f2f;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
    border: 1px solid #f44336;
}
.evolucao-data {
    color: #666;
    font-style: italic;
    margin-bottom: 30px;
}
</style>

<?php require_once 'includes/footer.php'; ?>