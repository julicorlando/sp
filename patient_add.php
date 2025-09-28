<?php
/**
 * Add Patient Form
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

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token de segurança inválido.';
    }
    
    // Sanitize and validate input
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
    
    // Validação específica para responsável quando menor/tutelado
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
    
    // Check if CPF already exists
    if (!empty($data['cpf'])) {
        $existing = $db->fetch("SELECT id FROM pacientes WHERE cpf = ?", [$data['cpf']]);
        if ($existing) {
            $errors[] = 'CPF já cadastrado.';
        }
    }
    
    // If no errors, insert patient
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO pacientes (
                        usuario_id, nome, sexo, estado_civil, data_nascimento, cpf, 
                        telefone, telefone_alternativo, endereco, email, possui_filhos,
                        atendimento, tipo_atendimento_ofertado, motivo_procura_queixa,
                        escolaridade, trabalha_no_momento, profissao, 
                        toma_algum_medicamento, qual_medicamento, disponibilidade, 
                        rede_de_apoio, contato_de_emergencia, observacoes,
                        e_menor_tutelado, responsavel_nome, responsavel_cpf, 
                        responsavel_endereco, responsavel_contato, responsavel_parentesco
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $user['id'], $data['nome'], $data['sexo'], $data['estado_civil'],
                $data['data_nascimento'] ?: null, $data['cpf'], $data['telefone'], $data['telefone_alternativo'],
                $data['endereco'], $data['email'], $data['possui_filhos'],
                $data['atendimento'], $data['tipo_atendimento_ofertado'], $data['motivo_procura_queixa'],
                $data['escolaridade'], $data['trabalha_no_momento'], $data['profissao'],
                $data['toma_algum_medicamento'], $data['qual_medicamento'], $data['disponibilidade'],
                $data['rede_de_apoio'], $data['contato_de_emergencia'], $data['observacoes'],
                $data['e_menor_tutelado'], $data['responsavel_nome'], $data['responsavel_cpf'],
                $data['responsavel_endereco'], $data['responsavel_contato'], $data['responsavel_parentesco']
            ];
            
            $db->execute($sql, $params);
            redirect('dashboard.php', 'Paciente cadastrado com sucesso!');
            
        } catch (Exception $e) {
            $errors[] = 'Erro ao cadastrar paciente: ' . $e->getMessage();
        }
    }
}

// Get select options
$options = getSelectOptions();

// Set page variables
$page_title = 'Cadastrar Paciente';
$css_files = ['cadastropacientes.css'];
$js_files = ['form-validation.js'];
$show_nav = true;

// Include header
require_once 'includes/header.php';
?>

<!-- Título da Página -->
<h1>Cadastrar Paciente</h1>

<!-- Erro/Sucesso Messages -->
<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Formulário de Cadastro -->
<main>
    <form method="post" class="form-cadastro">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <p>
            <label for="id_nome">Nome:</label>
            <input type="text" name="nome" id="id_nome" required maxlength="100"
                   value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_sexo">Sexo:</label>
            <?php echo generateSelect('sexo', $options['sexo'], $_POST['sexo'] ?? '', 'id="id_sexo" required'); ?>
        </p>
        
        <p>
            <label for="id_estado_civil">Estado civil:</label>
            <?php echo generateSelect('estado_civil', $options['estado_civil'], $_POST['estado_civil'] ?? '', 'id="id_estado_civil" required'); ?>
        </p>
        
        <p>
            <label for="id_data_nascimento">Data de nascimento:</label>
            <input type="date" name="data_nascimento" id="id_data_nascimento"
                   value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_cpf">CPF:</label>
            <input type="text" name="cpf" id="id_cpf" required maxlength="11"
                   value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                   pattern="[0-9]{11}" placeholder="Apenas números">
        </p>
        
        <p>
            <label for="id_telefone">Telefone:</label>
            <input type="text" name="telefone" id="id_telefone" required maxlength="15"
                   value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>">
        </p>
        
        <!-- Novo campo: Telefone Alternativo -->
        <p>
            <label for="id_telefone_alternativo">Telefone alternativo:</label>
            <input type="text" name="telefone_alternativo" id="id_telefone_alternativo" maxlength="15"
                   value="<?php echo htmlspecialchars($_POST['telefone_alternativo'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_endereco">Endereço:</label>
            <input type="text" name="endereco" id="id_endereco" required maxlength="255"
                   value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_email">Email:</label>
            <input type="email" name="email" id="id_email" required
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </p>
        
        <!-- Campo atualizado: Possui Filhos -->
        <p>
            <label for="id_possui_filhos">Possui Filhos?</label>
            <?php echo generateSelect('possui_filhos', $options['sim_nao'], $_POST['possui_filhos'] ?? '', 'id="id_possui_filhos" required'); ?>
        </p>
        
        <!-- Novo campo: Menor de Idade/Tutelado -->
        <p>
            <label for="id_e_menor_tutelado">É menor de idade ou tutelado?</label>
            <?php echo generateSelect('e_menor_tutelado', $options['sim_nao'], $_POST['e_menor_tutelado'] ?? '', 'id="id_e_menor_tutelado" required onchange="toggleResponsavelFields()"'); ?>
        </p>
        
        <!-- Seção de dados do responsável (mostrada apenas quando menor/tutelado = Sim) -->
        <div id="responsavel_section" style="display: none;">
            <h3>Dados do Responsável</h3>
            <p>
                <label for="id_responsavel_nome">Nome do responsável:</label>
                <input type="text" name="responsavel_nome" id="id_responsavel_nome" maxlength="100"
                       value="<?php echo htmlspecialchars($_POST['responsavel_nome'] ?? ''); ?>">
            </p>
            
            <p>
                <label for="id_responsavel_cpf">CPF do responsável:</label>
                <input type="text" name="responsavel_cpf" id="id_responsavel_cpf" maxlength="11"
                       value="<?php echo htmlspecialchars($_POST['responsavel_cpf'] ?? ''); ?>"
                       pattern="[0-9]{11}" placeholder="Apenas números">
            </p>
            
            <p>
                <label for="id_responsavel_endereco">Endereço do responsável:</label>
                <input type="text" name="responsavel_endereco" id="id_responsavel_endereco" maxlength="255"
                       value="<?php echo htmlspecialchars($_POST['responsavel_endereco'] ?? ''); ?>">
            </p>
            
            <p>
                <label for="id_responsavel_contato">Contato do responsável:</label>
                <input type="text" name="responsavel_contato" id="id_responsavel_contato" maxlength="15"
                       value="<?php echo htmlspecialchars($_POST['responsavel_contato'] ?? ''); ?>">
            </p>
            
            <p>
                <label for="id_responsavel_parentesco">Grau de parentesco:</label>
                <?php echo generateSelect('responsavel_parentesco', $options['parentesco'], $_POST['responsavel_parentesco'] ?? '', 'id="id_responsavel_parentesco"'); ?>
            </p>
        </div>
        
        <p>
            <label for="id_atendimento">Atendimento:</label>
            <?php echo generateSelect('atendimento', $options['sim_nao'], $_POST['atendimento'] ?? '', 'id="id_atendimento" required'); ?>
        </p>
        
        <!-- Campos separados conforme solicitação -->
        <p>
            <label for="id_tipo_atendimento_ofertado">Tipo de Atendimento Ofertado:</label>
            <textarea name="tipo_atendimento_ofertado" id="id_tipo_atendimento_ofertado" maxlength="500" placeholder="Descreva o tipo de atendimento que será oferecido..."><?php echo htmlspecialchars($_POST['tipo_atendimento_ofertado'] ?? ''); ?></textarea>
        </p>
        
        <p>
            <label for="id_motivo_procura_queixa">Motivo da Procura/Queixa:</label>
            <textarea name="motivo_procura_queixa" id="id_motivo_procura_queixa" maxlength="500" placeholder="Descreva o motivo da procura ou queixa do paciente..."><?php echo htmlspecialchars($_POST['motivo_procura_queixa'] ?? ''); ?></textarea>
        </p>
        
        <!-- Campo religião removido conforme solicitação -->
        
        <p>
            <label for="id_escolaridade">Escolaridade:</label>
            <?php echo generateSelect('escolaridade', $options['escolaridade'], $_POST['escolaridade'] ?? '', 'id="id_escolaridade" required'); ?>
        </p>
        
        <p>
            <label for="id_trabalha_no_momento">Trabalha no momento:</label>
            <?php echo generateSelect('trabalha_no_momento', $options['sim_nao'], $_POST['trabalha_no_momento'] ?? '', 'id="id_trabalha_no_momento" required'); ?>
        </p>
        
        <p>
            <label for="id_profissao">Profissão:</label>
            <input type="text" name="profissao" id="id_profissao" maxlength="50"
                   value="<?php echo htmlspecialchars($_POST['profissao'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_toma_algum_medicamento">Toma algum medicamento:</label>
            <?php echo generateSelect('toma_algum_medicamento', $options['sim_nao'], $_POST['toma_algum_medicamento'] ?? '', 'id="id_toma_algum_medicamento" required'); ?>
        </p>
        
        <p>
            <label for="id_qual_medicamento">Qual medicamento:</label>
            <input type="text" name="qual_medicamento" id="id_qual_medicamento" maxlength="100"
                   value="<?php echo htmlspecialchars($_POST['qual_medicamento'] ?? ''); ?>">
        </p>
        
        <p>
            <label for="id_disponibilidade">Disponibilidade:</label>
            <input type="text" name="disponibilidade" id="id_disponibilidade" maxlength="100"
                   value="<?php echo htmlspecialchars($_POST['disponibilidade'] ?? ''); ?>">
        </p>
        
        <!-- Campo rede de apoio melhorado para texto livre -->
        <p>
            <label for="id_rede_de_apoio">Rede de apoio:</label>
            <textarea name="rede_de_apoio" id="id_rede_de_apoio" maxlength="1000" placeholder="Descreva a rede de apoio do paciente (família, amigos, instituições, etc.)"><?php echo htmlspecialchars($_POST['rede_de_apoio'] ?? ''); ?></textarea>
        </p>
        
        <p>
            <label for="id_contato_de_emergencia">Contato de emergência:</label>
            <input type="text" name="contato_de_emergencia" id="id_contato_de_emergencia" maxlength="100"
                   value="<?php echo htmlspecialchars($_POST['contato_de_emergencia'] ?? ''); ?>">
        </p>
        
        <!-- Campo motivo_e_objetivo removido pois foi separado em tipo_atendimento_ofertado e motivo_procura_queixa -->
        
        <p>
            <label for="id_observacoes">Observações:</label>
            <textarea name="observacoes" id="id_observacoes" maxlength="1000"><?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>
        </p>
        
        <div class="form-buttons">
            <button type="submit" class="btn-primary">Salvar</button>
            <button type="reset" class="btn-secondary">Limpar</button>
        </div>
    </form>
</main>

<!-- JavaScript para controle de exibição dos campos do responsável -->
<script>
function toggleResponsavelFields() {
    const menorTutelado = document.getElementById('id_e_menor_tutelado').value;
    const responsavelSection = document.getElementById('responsavel_section');
    
    if (menorTutelado === 'Sim') {
        responsavelSection.style.display = 'block';
        // Tornar campos obrigatórios
        document.getElementById('id_responsavel_nome').required = true;
        document.getElementById('id_responsavel_cpf').required = true;
        document.getElementById('id_responsavel_contato').required = true;
        document.getElementById('id_responsavel_parentesco').required = true;
    } else {
        responsavelSection.style.display = 'none';
        // Remover obrigatoriedade
        document.getElementById('id_responsavel_nome').required = false;
        document.getElementById('id_responsavel_cpf').required = false;
        document.getElementById('id_responsavel_contato').required = false;
        document.getElementById('id_responsavel_parentesco').required = false;
    }
}

// Executar ao carregar a página para casos de reload com dados preenchidos
document.addEventListener('DOMContentLoaded', function() {
    toggleResponsavelFields();
});
</script>

<?php require_once 'includes/footer.php'; ?>