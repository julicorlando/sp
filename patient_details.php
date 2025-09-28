<?php
/**
 * Patient Details Page
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

// Get patient payments
$sql = "SELECT * FROM pagamentos WHERE paciente_id = ? ORDER BY data_pagamento DESC";
$pagamentos = $db->fetchAll($sql, [$patient_id]);

// Get patient files
$sql = "SELECT * FROM arquivos WHERE paciente_id = ? ORDER BY data_upload DESC";
$arquivos = $db->fetchAll($sql, [$patient_id]);

// Handle evolution form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conteudo'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $conteudo = sanitize($_POST['conteudo'] ?? '');
    
    if (verifyCSRFToken($csrf_token) && !empty($conteudo)) {
        $sql = "INSERT INTO evolucoes (paciente_id, conteudo) VALUES (?, ?)";
        try {
            $db->execute($sql, [$patient_id, $conteudo]);
            redirect("patient_details.php?id=$patient_id", 'Evolução adicionada com sucesso!');
        } catch (Exception $e) {
            $error = 'Erro ao adicionar evolução: ' . $e->getMessage();
        }
    } else {
        $error = 'Erro: dados inválidos.';
    }
}

// Set page variables
$page_title = 'Detalhes do Paciente - ' . $paciente['nome'];
$css_files = ['detalhes.css'];
$body_class = 'homepage';
$show_nav = true;

// Include header
require_once 'includes/header.php';
?>

<!-- Título da Página -->
<h2>Detalhes do Paciente</h2>

<!-- Informações do Paciente -->
<section class="patient-info">
    <h3>Informações Básicas</h3>
    <table class="info-table">
        <tbody>
            <tr><td><strong>Nome:</strong> <?php echo htmlspecialchars($paciente['nome']); ?></td></tr>
            <tr><td><strong>Sexo:</strong> <?php echo htmlspecialchars($paciente['sexo']); ?></td></tr>
            <tr><td><strong>Data de Nascimento:</strong> <?php echo formatDate($paciente['data_nascimento']); ?></td></tr>
            <tr><td><strong>Estado Civil:</strong> <?php echo htmlspecialchars($paciente['estado_civil']); ?></td></tr>
            <tr><td><strong>Escolaridade:</strong> <?php echo htmlspecialchars($paciente['escolaridade']); ?></td></tr>
            <tr><td><strong>Trabalha no Momento:</strong> <?php echo htmlspecialchars($paciente['trabalha_no_momento']); ?></td></tr>
            <tr><td><strong>Profissão:</strong> <?php echo htmlspecialchars($paciente['profissao']); ?></td></tr>
            <tr><td><strong>Filhos:</strong> <?php echo htmlspecialchars($paciente['filhos']); ?> (<?php echo htmlspecialchars($paciente['filhos_quantidade']); ?>)</td></tr>
            <tr><td><strong>Religião:</strong> <?php echo htmlspecialchars($paciente['religiao']); ?></td></tr>
            <tr><td><strong>Usa Medicamentos:</strong> <?php echo htmlspecialchars($paciente['toma_algum_medicamento']); ?></td></tr>
            <tr><td><strong>Qual:</strong> <?php echo htmlspecialchars($paciente['qual_medicamento']); ?></td></tr>
            <tr><td><strong>Rede de Apoio:</strong> <?php echo htmlspecialchars($paciente['rede_de_apoio']); ?></td></tr>
            <tr><td><strong>Contato de Emergência:</strong> <?php echo htmlspecialchars($paciente['contato_de_emergencia']); ?></td></tr>
            <tr><td><strong>Psicoterapia Anterior:</strong> <?php echo htmlspecialchars($paciente['atendimento']); ?></td></tr>
            <tr><td><strong>Detalhes:</strong> <?php echo htmlspecialchars($paciente['atendimento_tipo_tempo_motivo']); ?></td></tr>
            <tr><td><strong>Disponibilidade:</strong> <?php echo htmlspecialchars($paciente['disponibilidade']); ?></td></tr>
            <tr><td><strong>Objetivo:</strong> <?php echo htmlspecialchars($paciente['motivo_e_objetivo']); ?></td></tr>
        </tbody>
    </table>

    <!-- Informações Extras -->
    <h3>Informações Extras</h3>
    <table class="info-table">
        <tbody>
            <tr><td><strong>Endereço:</strong> <?php echo htmlspecialchars($paciente['endereco']); ?></td></tr>
            <tr><td><strong>CPF:</strong> <?php echo htmlspecialchars($paciente['cpf']); ?></td></tr>
            <tr><td><strong>Email:</strong> <?php echo htmlspecialchars($paciente['email']); ?></td></tr>
            <tr><td><strong>Telefone:</strong> <?php echo htmlspecialchars($paciente['telefone']); ?></td></tr>
            <tr><td><strong>Observações:</strong> <?php echo htmlspecialchars($paciente['observacoes']); ?></td></tr>
        </tbody>
    </table>
</section>

<!-- Informações de Pagamento -->
<section class="payment-info">
    <h3>Informações de Pagamento</h3>
    <div class="pagamento-area">
        <h2>Pagamentos de <?php echo htmlspecialchars($paciente['nome']); ?></h2>
        <a href="payment_add.php?patient_id=<?php echo $paciente['id']; ?>" class="btn btn-primary">Adicionar Pagamento</a>
        <table class="info-table">
            <thead>
                <tr>
                    <th>Data do Pagamento</th>
                    <th>Forma de Pagamento</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pagamentos)): ?>
                    <?php foreach ($pagamentos as $pagamento): ?>
                        <tr>
                            <td><?php echo formatDate($pagamento['data_pagamento']); ?></td>
                            <td><?php echo htmlspecialchars($pagamento['forma_pagamento']); ?></td>
                            <td><strong><?php echo formatCurrency($pagamento['valor']); ?></strong></td>
                            <td>
                                <form action="payment_delete.php" method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="patient_id" value="<?php echo $paciente['id']; ?>">
                                    <input type="hidden" name="payment_id" value="<?php echo $pagamento['id']; ?>">
                                    <button class="btn" type="submit" onclick="return confirm('Tem certeza que deseja excluir este pagamento?');">Excluir Pagamento</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Nenhum pagamento encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Upload de Arquivos -->
<section class="payment-info">
    <h2>Arquivos do Paciente</h2>
    <div class="pagamento-area">
        <h3>Adicionar Novo Arquivo</h3>
        <form action="file_upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="patient_id" value="<?php echo $paciente['id']; ?>">
            <input type="file" name="arquivo" required>
            <button type="submit" class="btn">Enviar Arquivo</button>
        </form>
    </div>
    <h3>Arquivos Existentes</h3>
    <table class="payment-info">
        <tbody>
            <?php if (!empty($arquivos)): ?>
                <?php foreach ($arquivos as $arquivo): ?>
                    <tr>
                        <td>
                            <a href="<?php echo htmlspecialchars($arquivo['arquivo_path']); ?>" target="_blank">
                                <?php echo htmlspecialchars($arquivo['arquivo_nome']); ?>
                            </a>
                        </td>
                        <td>
                            <form action="file_delete.php" method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="patient_id" value="<?php echo $paciente['id']; ?>">
                                <input type="hidden" name="file_id" value="<?php echo $arquivo['id']; ?>">
                                <button class="btn" type="submit" onclick="return confirm('Tem certeza que deseja excluir este arquivo?');">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Nenhum arquivo disponível.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<!-- Evoluções -->
<section class="evolucao-container">
    <h2>Evoluções do Paciente</h2>
    <div class="nova-evolucao">
        <h3>Adicionar Nova Evolução</h3>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <textarea name="conteudo" placeholder="Escreva a evolução do paciente aqui..." required></textarea>
            <button type="submit" class="btn">Salvar Evolução</button>
        </form>
    </div>
</section>

<!-- Navegação Inferior -->
<nav class="bottom-nav">
    <ul class="nav-links">
        <li><a href="dashboard.php">Voltar</a></li>
        <li><a href="evolution_list.php?patient_id=<?php echo $paciente['id']; ?>">Ver Evoluções</a></li>
        <li><a href="patient_delete.php?id=<?php echo $paciente['id']; ?>">Excluir Paciente</a></li>
        <li><a href="patient_edit.php?id=<?php echo $paciente['id']; ?>">Editar Paciente</a></li>
        <li><a href="payment_list.php?patient_id=<?php echo $paciente['id']; ?>">Ver Pagamentos</a></li>
        <li>
            <form action="logout.php" method="post" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <button class="btt" type="submit">Sair</button>
            </form>
        </li>
    </ul>
</nav>
<script>
    $(document).ready(function() {
        $('h2').hide().fadeIn(1000);
    });
</script>
<?php require_once 'includes/footer.php'; ?>