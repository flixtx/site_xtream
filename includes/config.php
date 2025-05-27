<?php
session_start();

// Configuração do SQLite
//define('DB_FILE', __DIR__ . '/database.db');

// Função para conectar ao SQLite
function get_db_connection() {
    try {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
}

// Criação da tabela de cache
function init_database() {
    $db = get_db_connection();
    $db->exec("
        CREATE TABLE IF NOT EXISTS cache (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            request_key TEXT UNIQUE,
            response TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password TEXT,
            host TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
    error_log("Banco de dados SQLite inicializado com sucesso.");
}

// Função para chamar a API Xtream Codes com cache
function xtream_api_call($action, $params = []) {
    if (!isset($_SESSION['user']['host']) || !isset($_SESSION['user']['username']) || !isset($_SESSION['user']['password'])) {
        error_log("Credenciais ausentes na sessão.");
        return null;
    }

    $host = rtrim($_SESSION['user']['host'], '/');
    $username = $_SESSION['user']['username'];
    $password = $_SESSION['user']['password'];

    $request_key = md5($host . $action . serialize($params));
    //$db = get_db_connection();
    
    // Verifica cache
    // $stmt = $db->prepare("SELECT response FROM cache WHERE request_key = :key AND created_at > :time");
    // $stmt->execute(['key' => $request_key, 'time' => date('Y-m-d H:i:s', strtotime('-1 hour'))]);
    // $cached = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // if ($cached) {
    //     return json_decode($cached['response'], true);
    // }
    
    // Chama a API usando /player_api.php
    $url = $host . '/player_api.php?username=' . urlencode($username) . '&password=' . urlencode($password) . '&action=' . $action;
    foreach ($params as $key => $value) {
        $url .= '&' . $key . '=' . urlencode($value);
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log da resposta para depuração
    error_log("API Request: $url");
    error_log("API Response: $response");
    error_log("HTTP Code: $http_code");
    
    // Salva no cache
    // if ($response && $http_code === 200) {
    //     $stmt = $db->prepare("INSERT OR REPLACE INTO cache (request_key, response) VALUES (:key, :response)");
    //     $stmt->execute(['key' => $request_key, 'response' => $response]);
    // }
    
    return json_decode($response, true);
}

// Inicializa o banco de dados
//init_database();
?>