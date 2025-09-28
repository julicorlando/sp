<?php
/**
 * Common Functions and Utilities
 * Sistema de Pacientes - PHP Migration
 */

// Inclui a configuração e classe Database
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate required fields
 */
function validateRequired($fields, $data) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "Campo {$field} é obrigatório";
        }
    }
    return $errors;
}

/**
 * Validate CPF format
 */
function validateCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return false;
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

/**
 * Validate phone number format
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format currency for display
 */
function formatCurrency($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect with message
 */
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : null;
    $type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return $message ? ['message' => $message, 'type' => $type] : null;
}

/**
 * File upload handler
 */
function handleFileUpload($file, $upload_dir = MEDIA_PATH . 'uploads/') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file was uploaded'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File too large'];
        default:
            return ['success' => false, 'message' => 'Unknown upload error'];
    }
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    return [
        'success' => true, 
        'filename' => $filename,
        'filepath' => $filepath,
        'original_name' => $file['name']
    ];
}

/**
 * Get select options for forms
 */
function getSelectOptions() {
    return [
        'sexo' => [
            'Masc' => 'Masculino',
            'Fem' => 'Feminino', 
            'O' => 'Outro'
        ],
        'estado_civil' => [
            'Não Informado' => 'Não Informado',
            'Casado(a)' => 'Casado(a)',
            'Solteiro(a)' => 'Solteiro(a)',
            'Divorciado(a)' => 'Divorciado(a)',
            'Viúvo(a)' => 'Viúvo(a)'
        ],
        'sim_nao' => [
            'Sim' => 'Sim',
            'Não' => 'Não'
        ],
        'escolaridade' => [
            'Fundamental' => 'Fundamental',
            'Médio' => 'Médio',
            'Superior Completo' => 'Superior Completo',
            'Superior incompleto' => 'Superior incompleto',
            'Pós-Graduação' => 'Pós-Graduação',
            'Mestrado' => 'Mestrado',
            'Doutorado' => 'Doutorado'
        ],
        'forma_pagamento' => [
            'Pix' => 'Pix',
            'Dinheiro' => 'Dinheiro',
            'Cartão de Crédito' => 'Cartão de Crédito',
            'Cartão de Débito' => 'Cartão de Débito'
        ]
    ];
}

/**
 * Generate select HTML
 */
function generateSelect($name, $options, $selected = '', $attributes = '') {
    $html = "<select name=\"$name\" $attributes>";
    foreach ($options as $value => $label) {
        $selectedAttr = ($value == $selected) ? 'selected' : '';
        $html .= "<option value=\"$value\" $selectedAttr>$label</option>";
    }
    $html .= "</select>";
    return $html;
}

/**
 * Pagination helper
 */
function paginate($total_records, $records_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_records / $records_per_page);
    $pagination = [];
    if ($current_page > 1) {
        $pagination['prev'] = $base_url . '?page=' . ($current_page - 1);
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        $pagination['pages'][] = [
            'number' => $i,
            'url' => $base_url . '?page=' . $i,
            'current' => ($i == $current_page)
        ];
    }
    if ($current_page < $total_pages) {
        $pagination['next'] = $base_url . '?page=' . ($current_page + 1);
    }
    return $pagination;
}

/**
 * Validate username format
 */
function validateUsername($username) {
    // Aceita letras, números, _ , entre 3 e 150 caracteres
    return preg_match('/^[a-zA-Z0-9_]{3,150}$/', $username);
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'A senha deve conter pelo menos 8 caracteres.';
    }
    if (preg_match('/^\d+$/', $password)) {
        $errors[] = 'A senha não pode ser inteiramente numérica.';
    }
    // Adicione outras regras conforme desejar
    return $errors;
}
?>