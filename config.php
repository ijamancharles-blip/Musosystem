<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private $host = "localhost";
    private $db_name = "finexe_system";
    private $username = "root";
    private $password = "";
    public $conn;

    public function createColumnsIfNotExist($table, $columns) {
    try {
        foreach ($columns as $column => $definition) {
            $query = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$column'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                $alterQuery = "ALTER TABLE $table ADD COLUMN $column $definition";
                $this->conn->exec($alterQuery);
            }
        }
    } catch (PDOException $e) {
        // Silansyèzman echwe si gen ere
    }
}

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function isActive() {
    return isset($_SESSION['is_active']) && $_SESSION['is_active'] == 1;
}

function redirectIfNotLogged() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfNotActive() {
    if (!isActive()) {
        header("Location: login.php");
        exit();
    }
}

function checkUserAccess() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    
    if (!isActive()) {
        header("Location: login.php");
        exit();
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Fonction pour nettoyer les données
function nettoyer_donnees($data) {
    if (is_array($data)) {
        return array_map('nettoyer_donnees', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Fonction pour valider email
function valider_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Inclure les fonctions utilitaires
if (file_exists('functions.php')) {
    require_once 'functions.php';
}


?>