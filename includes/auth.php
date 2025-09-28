<?php
/**
 * Authentication System
 * Sistema de Pacientes - PHP Migration
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Authentication class to handle user login, logout, and session management
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Login user with username and password
     */
    public function login($username, $password) {
        $sql = "SELECT id, username, email, password, first_name, last_name, is_active 
                FROM users WHERE username = ? AND is_active = 1";
        
        $user = $this->db->fetch($sql, [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['login_time'] = time();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Register new user
     */
    public function register($username, $email, $password, $first_name = '', $last_name = '') {
        // Check if username already exists
        $existing = $this->db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            return ['success' => false, 'message' => 'Username already exists'];
        }
        
        // Check if email already exists
        $existing = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (username, email, password, first_name, last_name) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $this->db->execute($sql, [$username, $email, $hashed_password, $first_name, $last_name]);
            return ['success' => true, 'message' => 'User registered successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['login_time']) && 
               (time() - $_SESSION['login_time']) < SESSION_TIMEOUT;
    }
    
    /**
     * Get current user ID
     */
    public function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get current user info
     */
    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name']
        ];
    }
    
    /**
     * Require login - redirect to login page if not authenticated
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    /**
     * Update last activity timestamp
     */
    public function updateActivity() {
        if ($this->isLoggedIn()) {
            $_SESSION['login_time'] = time();
        }
    }
}

/**
 * Password validation
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

/**
 * Email validation
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Username validation
 */
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,150}$/', $username);
}

// Create global auth instance
$auth = new Auth();
?>