<?php
require_once 'config.php';
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/php-error.log'); // Cria o log na pasta do projeto
// error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

// Verifica se o usuário está logado
function is_logged_in() {
    return isset($_SESSION['user']['username']) && isset($_SESSION['user']['password']) && isset($_SESSION['user']['host']);
}

// Obtém informações da conta
function get_account_info($username, $password, $host) {
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND password = :password AND host = :host");
    $stmt->execute(['username' => $username, 'password' => $password, 'host' => $host]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        return ['user_info' => [
            'auth' => 1,
            'username' => $user['username'],
            'password' => $user['password'],
            'created_at' => $user['created_at'],
            'exp_date' => 'N/A',
            'max_connections' => 1,
            'active_cons' => 0
        ]];
    }
    
    $_SESSION['user'] = [
        'username' => $username,
        'password' => $password,
        'host' => $host
    ];
    
    $account_info = xtream_api_call('get_account_info', ['username' => $username, 'password' => $password]);
    
    if (!$account_info || !isset($account_info['user_info']['auth']) || $account_info['user_info']['auth'] != 1) {
        unset($_SESSION['user']);
        return null;
    }
    
    return $account_info;
}

// Obtém lista de canais ao vivo
function get_live_streams($category_id = null) {
    $params = $category_id ? ['category_id' => $category_id] : [];
    return xtream_api_call('get_live_streams', $params);
}

// Obtém lista de filmes
function get_vod_streams($category_id = null) {
    $params = $category_id ? ['category_id' => $category_id] : [];
    return xtream_api_call('get_vod_streams', $params);
}

// Obtém lista de séries
function get_series($category_id = null) {
    $params = $category_id ? ['category_id' => $category_id] : [];
    return xtream_api_call('get_series', $params);
}

// Obtém EPG
function get_epg($stream_id) {
    $response = xtream_api_call('get_short_epg', ['stream_id' => $stream_id]);

    // Log para depuração
    error_log("EPG Response for stream_id $stream_id: " . json_encode($response));

    if (isset($response['epg_listings']) && is_array($response['epg_listings'])) {
        return array_map(function($program) {
            $startTimestamp = $program['start_timestamp'] ?? null;
            $endTimestamp   = $program['stop_timestamp'] ?? null;

            $start = $startTimestamp ? date('d/m/Y H:i', $startTimestamp) : 'N/A';
            $end   = $endTimestamp   ? date('d/m/Y H:i', $endTimestamp)   : 'N/A';

            $title = isset($program['title']) ? base64_decode($program['title'], true) : 'Sem título';
            if (!$title || trim($title) === '') {
                $title = 'Sem título';
            }

            $description = isset($program['description']) ? base64_decode($program['description'], true) : '';
            if (!$description || trim($description) === '') {
                $description = '';
            }

            return [
                'start' => $start,
                'end' => $end,
                'title' => $title,
                'description' => $description
            ];
        }, $response['epg_listings']);
    }

    return [];
}

// No final de functions.php, antes do fechamento do
function search_content($section, $query) {
    $results = [];
    if ($section === 'live') {
        $streams = get_live_streams();
        foreach ($streams as $stream) {
            if (stripos($stream['name'], $query) !== false) {
                $results[] = $stream;
            }
        }
    } elseif ($section === 'movies') {
        $streams = get_vod_streams();
        foreach ($streams as $stream) {
            if (stripos($stream['name'], $query) !== false) {
                $results[] = $stream;
            }
        }
    } elseif ($section === 'series') {
        $streams = get_series();
        foreach ($streams as $stream) {
            if (stripos($stream['name'], $query) !== false) {
                $results[] = $stream;
            }
        }
    }
    return $results;
}

?>