<?php
/**
 * Edit Patient Form
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
$sql = "SELECT * FROM pacientes WHERE id = ? AND usuario_id = ?";
$paciente = $db->fetch($sql, [$patient_id, $user['id']]);
if (!$paciente) {
    redirect('dashboard.php', 'Paciente não encontrado.', 'error');
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token de segurança inválido.';
    }
    
    // Sanitize and validate input - Updated fields list
    $data = [];
    $fields = [
        'nome', 'sexo', 'estado_civil', 'data_nascimento', 'cpf', 'telefone', 'telefone_alternativo',
        'endereco', 'email', 'possui_filhos', 'atendimento', 
        'tipo_atendimento_ofertado', 'motivo_procura_queixa', 'escolaridade', 
        'trabalha_no_momento', 'profissao', 'toma_algum_medicamento', 
        'qual_medicamento', 'disponibilidade', 'rede_de_apoio', 
        'contato_de_emergencia', 'observacoes',
        // Novos campos para menor/tutelado
        'e_menor_tutelado', 'responsavel_nome', 'responsavel_cpf', 
        'responsavel_endereco', 'responsavel_contato', 'responsavel_parentesco'
    ];
    
    foreach ($fields as $field) {
        $data[$field] = sanitize($_POST[$field] ?? '');
    }
    
    // Validate required fields
    $required_fields = ['nome', 'sexo', 'estado_civil', 'cpf', 'telefone', 'endereco', 'email'];
    $required_errors = validateRequired($required_fields, $data);
    $errors = array_merge($errors, $required_errors);
    
    // Validate CPF
    if (!empty($data['cpf']) && !validateCPF($data['cpf'])) {
        $errors[] = 'CPF inválido.';
    }
    
    // Validação específica para responsável quando menor/tutelado - EDIT
    if ($data['e_menor_tutelado'] === 'Sim') {
        $required_responsavel = ['responsavel_nome', 'responsavel_cpf', 'responsavel_contato', 'responsavel_parentesco'];
        foreach ($required_responsavel as $field) {
            if (empty($data[$field])) {
                $errors[] = 'Quando paciente é menor/tutelado, todos os dados do responsável são obrigatórios.';
                break;
            }
        }
        // Validar CPF do responsável
        if (!empty($data['responsavel_cpf']) && !validateCPF($data['responsavel_cpf'])) {
            $errors[] = 'CPF do responsável inválido.';
        }
    }
    
    // Validate phone
    if (!empty($data['telefone']) && !validatePhone($data['telefone'])) {
        $errors[] = 'Telefone inválido.';
    }
    
    // Validate email
    if (!empty($data['email']) && !validateEmail($data['email'])) {
        $errors[] = 'Email inválido.';
    }
    
    // Check if CPF already exists (but allow current patient's CPF)
    if (!empty($data['cpf'])) {
        $existing = $db->fetch("SELECT id FROM pacientes WHERE cpf = ? AND id != ?", [$data['cpf'], $patient_id]);
        if ($existing) {
            $errors[] = 'CPF já cadastrado para outro paciente.';
        }
    }
    
    // If no errors, update patient
    if (empty($errors)) {
        try {
            $sql = "UPDATE pacientes SET 
                        nome = ?, sexo = ?, estado_civil = ?, data_nascimento = ?, cpf = ?, 
                        telefone = ?, telefone_alternativo = ?, endereco = ?, email = ?, possui_filhos = ?,
                        atendimento = ?, tipo_atendimento_ofertado = ?, motivo_procura_queixa = ?,
                        escolaridade = ?, trabalha_no_momento = ?, profissao = ?, 
                        toma_algum_medicamento = ?, qual_medicamento = ?, disponibilidade = ?, 
                        rede_de_apoio = ?, contato_de_emergencia = ?, observacoes = ?,
                        e_menor_tutelado = ?, responsavel_nome = ?, responsavel_cpf = ?,
                        responsavel_endereco = ?, responsavel_contato = ?, responsavel_parentesco = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND usuario_id = ?";
            
            $params = [
                $data['nome'], $data['sexo'], $data['estado_civil'],
                $data['data_nascimento'] ?: null, $data['cpf'], $data['telefone'], $data['telefone_alternativo'],
                $data['endereco'], $data['email'], $data['possui_filhos'],
                $data['atendimento'], $data['tipo_atendimento_ofertado'], $data['motivo_procura_queixa'],
                $data['escolaridade'], $data['trabalha_no_momento'], $data['profissao'],
                $data['toma_algum_medicamento'], $data['qual_medicamento'], $data['disponibilidade'],
                $data['rede_de_apoio'], $data['contato_de_emergencia'], $data['observacoes'],
                $data['e_menor_tutelado'], $data['responsavel_nome'], $data['responsavel_cpf'],
                $data['responsavel_endereco'], $data['responsavel_contato'], $data['responsavel_parentesco'],
                $patient_id, $user['id']
            ];
            
            $db->execute($sql, $params);
            redirect("patient_details.php?id=$patient_id", 'Paciente atualizado com sucesso!');
            
        } catch (Exception $e) {
            $errors[] = 'Erro ao atualizar paciente: ' . $e->getMessage();
        }
    }
    
    // If errors, use POST data for form values
    if (!empty($errors)) {
        $paciente = array_merge($paciente, $data);
    }
}

// Get select options
$options = getSelectOptions();

// Set page variables
$page_title = 'Editar Paciente - ' . $paciente['nome'];
$css_files = ['cadastropacientes.css'];
$js_files = ['form-validation.js'];
$show_nav = true;

// Include header
require_once 'includes/header.php';
?>

<!-- Título da Página -->
<h1>Editar Paciente</h1>

<!-- Erro/Sucesso Messages -->
<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Formulário de Edição -->
<main>
    <form method="post" class="form-cadastro">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <p>
            <label for="id_nome">Nome:</label>
            <input type="text" name="nome" id="id_nome" required maxlength="100"
                   value="<?php echo htmlspecialchars($paciente['nome']); ?>">
        </p>
        
        <p>
            <label for="id_sexo">Sexo:</label>
            <?php echo generateSelect('sexo', $options['sexo'], $paciente['sexo'], 'id="id_sexo" required'); ?>
        </p>
        
        <p>
            <label for="id_estado_civil">Estado civil:</label>
            <?php echo generateSelect('estado_civil', $options['estado_civil'], $paciente['estado_civil'], 'id="id_estado_civil" required'); ?>
        </p>
        
        <p>
            <label for="id_data_nascimento">Data de nascimento:</label>
            <input type="date" name="data_nascimento" id="id_data_nascimento"
                   value="<?php echo htmlspecialchars($paciente['data_nascimento']); ?>">
        </p>
        
        <p>
            <label for="id_cpf">CPF:</label>
            <input type="text" name="cpf" id="id_cpf" required maxlength="11"
                   value="<?php echo htmlspecialchars($paciente['cpf']); ?>"
                   pattern="[0-9]{11}" placeholder="Apenas números">
        </p>
        
        <p>
            <label for="id_telefone">Telefone:</label>
            <input type="text" name="telefone" id="id_telefone" required maxlength="15"
                   value="<?php echo htmlspecialchars($paciente['telefone']); ?>">
        </p>
        
        <!-- Novo campo: Telefone Alternativo -->
        <p>
            <label for="id_telefone_alternativo">Telefone alternativo:</label>
            <input type="text" name="telefone_alternativo" id="id_telefone_alternativo" maxlength="15"
                   value="<?php echo htmlspecialchars($paciente['telefone_alternativo'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_endereco">Endereço:</label>
            <input type="text" name="endereco" id="id_endereco" required maxlength="255"
                   value="<?php echo htmlspecialchars($paciente['endereco']); ?>">
        </p>
        
        <p>
            <label for="id_email">Email:</label>
            <input type="email" name="email" id="id_email" required
                   value="<?php echo htmlspecialchars($paciente['email']); ?>">
        </p>
        
        <p>
            <label for="id_filhos">Filhos:</label>
            <?php echo generateSelect('filhos', $options['sim_nao'], $paciente['filhos'], 'id="id_filhos" required'); ?>
        </p>
        
        <p>
            <label for="id_filhos_quantidade">Quantidade de filhos:</label>
            <input type="text" name="filhos_quantidade" id="id_filhos_quantidade" maxlength="10"
                   value="<?php echo htmlspecialchars($paciente['filhos_quantidade']); ?>">
        </p>
        
        <p>
            <label for="id_atendimento">Atendimento:</label>
            <?php echo generateSelect('atendimento', $options['sim_nao'], $paciente['atendimento'], 'id="id_atendimento" required'); ?>
        </p>
        
        <p>
            <label for="id_atendimento_tipo_tempo_motivo">Tipo/Tempo/Motivo do atendimento:</label>
            <textarea name="atendimento_tipo_tempo_motivo" id="id_atendimento_tipo_tempo_motivo" maxlength="500"><?php echo htmlspecialchars($paciente['atendimento_tipo_tempo_motivo']); ?></textarea>
        </p>
        
        <p>
            <label for="id_religiao">Religião:</label>
            <input type="text" name="religiao" id="id_religiao" maxlength="20"
                   value="<?php echo htmlspecialchars($paciente['religiao']); ?>">
        </p>
        
        <p>
            <label for="id_escolaridade">Escolaridade:</label>
            <?php echo generateSelect('escolaridade', $options['escolaridade'], $paciente['escolaridade'], 'id="id_escolaridade" required'); ?>
        </p>
        
        <p>
            <label for="id_trabalha_no_momento">Trabalha no momento:</label>
            <?php echo generateSelect('trabalha_no_momento', $options['sim_nao'], $paciente['trabalha_no_momento'], 'id="id_trabalha_no_momento" required'); ?>
        </p>
        
        <p>
            <label for="id_profissao">Profissão:</label>
            <input type="text" name="profissao" id="id_profissao" maxlength="50"
                   value="<?php echo htmlspecialchars($paciente['profissao']); ?>">
        </p>
        
        <p>
            <label for="id_toma_algum_medicamento">Toma algum medicamento:</label>
            <?php echo generateSelect('toma_algum_medicamento', $options['sim_nao'], $paciente['toma_algum_medicamento'], 'id="id_toma_algum_medicamento" required'); ?>
        </p>
        
        <p>
            <label for="id_qual_medicamento">Qual medicamento:</label>
            <input type="text" name="qual_medicamento" id="id_qual_medicamento" maxlength="100"
                   value="<?php echo htmlspecialchars($paciente['qual_medicamento']); ?>">
        </p>
        
        <p>
            <label for="id_disponibilidade">Disponibilidade:</label>
            <input type="text" name="disponibilidade" id="id_disponibilidade" maxlength="100"
                   value="<?php echo htmlspecialchars($paciente['disponibilidade']); ?>">
        </p>
        
        <p>
            <label for="id_rede_de_apoio">Rede de apoio:</label>
            <input type="text" name="rede_de_apoio" id="id_rede_de_apoio" maxlength="255"
                   value="<?php echo htmlspecialchars($paciente['rede_de_apoio']); ?>">
        </p>
        
        <p>
            <label for="id_contato_de_emergencia">Contato de emergência:</label>
            <input type="text" name="contato_de_emergencia" id="id_contato_de_emergencia" maxlength="100"
                   value="<?php echo htmlspecialchars($paciente['contato_de_emergencia']); ?>">
        </p>
        
        <p>
            <label for="id_motivo_e_objetivo">Motivo e objetivo:</label>
            <textarea name="motivo_e_objetivo" id="id_motivo_e_objetivo" maxlength="500"><?php echo htmlspecialchars($paciente['motivo_e_objetivo']); ?></textarea>
        </p>
        
        <p>
            <label for="id_observacoes">Observações:</label>
            <textarea name="observacoes" id="id_observacoes" maxlength="1000"><?php echo htmlspecialchars($paciente['observacoes']); ?></textarea>
        </p>
        
        <div class="form-buttons">
            <button type="submit" class="btn-primary">Atualizar</button>
            <a href="patient_details.php?id=<?php echo $patient_id; ?>" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>