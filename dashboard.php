<?php
/**
 * Patient Dashboard - List Patients
 * Sistema de Pacientes - PHP Migration
 */

require_once 'includes/functions.php';

session_start();
$db = getDB();

// Protege o acesso: exige login e aprovação
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
    exit;
}

// Busca usuário logado
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$user || !$user['aprovado']) {
    session_destroy();
    redirect('login.php?error=acesso_bloqueado');
    exit;
}

// Busca pacientes do usuário
$sql = "SELECT id, nome, telefone, email, cpf, created_at 
        FROM pacientes 
        WHERE usuario_id = ? 
        ORDER BY nome ASC";

$pacientes = $db->fetchAll($sql, [$user['id']]);

// Função para abreviar nome: "Maria dos Santos Oliveira" => "Maria S. Oliveira"
function abreviarNome($nome) {
    $partes = explode(' ', $nome);
    $total = count($partes);
    if ($total < 3) return $nome; // Nome curto, não abrevia
    // Mantém o primeiro e o último nome, abrevia os do meio
    $abreviado = $partes[0];
    for ($i = 1; $i < $total - 1; $i++) {
        // Só abrevia se não for preposição comum
        if (!in_array(strtolower($partes[$i]), ['da','de','do','das','dos','e'])) {
            $abreviado .= ' ' . mb_substr($partes[$i], 0, 1) . '.';
        } else {
            $abreviado .= ' ' . $partes[$i];
        }
    }
    $abreviado .= ' ' . $partes[$total-1];
    return $abreviado;
}

// Set page variables
$page_title = 'Lista de Pacientes';
$css_files = ['listar.css'];
$show_nav = true;

// Include header
require_once 'includes/header.php';
?>

<header>
    <h1>Lista de Pacientes</h1>
    <!-- Navigation is handled by includes/header.php -->
</header>

<!-- Campo de busca -->
<div class="search-container">
    <input type="text" id="search" placeholder="Buscar paciente..." class="search-input">
</div>

<section class="table-container">
    <?php if (!empty($pacientes)): ?>
        <?php foreach ($pacientes as $paciente): ?>
            <ul class="paciente-item">
                <li class="paciente-nome"><?php echo htmlspecialchars(abreviarNome($paciente['nome'])); ?></li>
                <li><a href="patient_details.php?id=<?php echo $paciente['id']; ?>">Ver detalhes</a></li>
                <li><a href="patient_delete.php?id=<?php echo $paciente['id']; ?>">Excluir</a></li>
                <li><a href="patient_edit.php?id=<?php echo $paciente['id']; ?>">Editar</a></li>
                <li><a href="payment_add.php?patient_id=<?php echo $paciente['id']; ?>" class="btn btn-primary">Adicionar Pagamento</a></li>
            </ul>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty-message">Nenhum paciente cadastrado.</p>
    <?php endif; ?>
</section>

<!-- Script jQuery para busca dinâmica -->
<script>
    $(document).ready(function() {
        $('#search').on('input', function() {
            var value = $(this).val().toLowerCase();
            $('.paciente-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().includes(value));
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>