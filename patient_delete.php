<?php
/**
 * Delete Patient Confirmation Page
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
$patient_id = intval($_GET['id'] ?? 0);

if (!$patient_id) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Get patient details (ensure it belongs to current user)
$sql = "SELECT id, nome FROM pacientes WHERE id = ? AND usuario_id = ?";
$paciente = $db->fetch($sql, [$patient_id, $user['id']]);
if (!$paciente) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

// Handle form submission (confirmation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        redirect('dashboard.php', 'Token de segurança inválido.', 'error');
    }
    
    if ($confirm === 'yes') {
        try {
            // Delete patient and all related data (due to CASCADE in database)
            $sql = "DELETE FROM pacientes WHERE id = ? AND usuario_id = ?";
            $db->execute($sql, [$patient_id, $user['id']]);
            redirect('dashboard.php', 'Paciente excluído com sucesso!');
        } catch (Exception $e) {
            redirect('dashboard.php', 'Erro ao excluir paciente: ' . $e->getMessage(), 'error');
        }
    } else {
        redirect('dashboard.php', 'Exclusão cancelada.');
    }
}

// Set page variables
$page_title = 'Confirmar Exclusão - ' . $paciente['nome'];
$css_files = ['styles.css'];
$show_nav = true;

// Include header
require_once 'includes/header.php';
?>

<div class="confirmation-container">
    <h2>Confirmar Exclusão do Paciente</h2>
    
    <div class="warning">
        <p><strong>ATENÇÃO:</strong> Você está prestes a excluir o paciente:</p>
        <p class="patient-name"><strong><?php echo htmlspecialchars($paciente['nome']); ?></strong></p>
        <p>Esta ação irá excluir <strong>permanentemente</strong>:</p>
        <ul>
            <li>Todos os dados do paciente</li>
            <li>Todos os pagamentos relacionados</li>
            <li>Todos os arquivos relacionados</li>
            <li>Todas as evoluções relacionadas</li>
        </ul>
        <p><strong>Esta ação não pode ser desfeita!</strong></p>
    </div>
    
    <form method="post" class="confirmation-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-buttons">
            <button type="submit" name="confirm" value="yes" class="btn-danger" 
                    onclick="return confirm('Tem CERTEZA ABSOLUTA que deseja excluir este paciente e todos os seus dados?');">
                SIM, EXCLUIR PERMANENTEMENTE
            </button>
            <button type="submit" name="confirm" value="no" class="btn-secondary">
                NÃO, CANCELAR
            </button>
        </div>
    </form>
    
    <div class="navigation-links">
        <a href="patient_details.php?id=<?php echo $patient_id; ?>" class="btn">Voltar aos Detalhes</a>
        <a href="dashboard.php" class="btn">Voltar à Lista</a>
    </div>
</div>

<style>
.confirmation-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.warning {
    background: #ffebee;
    border: 2px solid #f44336;
    border-radius: 5px;
    padding: 20px;
    margin: 20px 0;
    color: #d32f2f;
}

.warning ul {
    text-align: left;
    margin: 15px 0;
}

.patient-name {
    font-size: 1.3em;
    color: #1976d2;
    margin: 15px 0;
}

.confirmation-form {
    margin: 30px 0;
}

.form-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-danger {
    background-color: #f44336;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s;
}

.btn-danger:hover {
    background-color: #d32f2f;
}

.btn-secondary {
    background-color: #757575;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s;
}

.btn-secondary:hover {
    background-color: #616161;
}

.navigation-links {
    margin-top: 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.navigation-links .btn {
    background-color: #2196f3;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.navigation-links .btn:hover {
    background-color: #1976d2;
}
</style>

<?php require_once 'includes/footer.php'; ?>