<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

//$account_info = $_SESSION['user']['account_info'];
$url_api = rtrim($_SESSION['user']['host'], '/') . '/player_api.php?username=' . urlencode($_SESSION['user']['username']) . '&password=' . urlencode($_SESSION['user']['password']);


$ch = curl_init($url_api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    $error = "Não foi possível conectar ao host fornecido. Verifique o endereço.";
    echo "<script>alert('$error');</script>";
    exit;
} else {
    $account_info_ = json_decode($response, true);
    if ($account_info_ && isset($account_info_['user_info']['auth']) && $account_info_['user_info']['auth'] == 1) {
        $account_info = $account_info_['user_info'];
    } else {
        $error = "Usuário, senha ou host inválidos. ";
        if ($account_info_ && isset($account_info_['user_info']['status'])) {
            $error .= "Status da conta: " . $account_info_['user_info']['status'];
        }
        echo "<script>alert('$error');</script>";
        exit;
    }
}


// Define o fuso horário para São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Formata as datas, se existirem
$created_at = isset($account_info['created_at']) && is_numeric($account_info['created_at']) 
    ? date('d/m/Y H:i:s', $account_info['created_at']) 
    : 'N/A';
$exp_date = isset($account_info['exp_date']) && is_numeric($account_info['exp_date']) 
    ? date('d/m/Y H:i:s', $account_info['exp_date']) 
    : 'N/A';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informação da Conta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <h2>Informação da Conta</h2>
        <p><strong>Usuário:</strong> <?php echo htmlspecialchars($_SESSION['user']['username']); ?></p>
        <p><strong>Senha:</strong> <?php echo htmlspecialchars($_SESSION['user']['password']); ?></p>
        <p><strong>Data de Criação:</strong> <?php echo htmlspecialchars($created_at); ?></p>
        <p><strong>Data de Expiração:</strong> <?php echo htmlspecialchars($exp_date); ?></p>
        <p><strong>Conexões Máximas:</strong> <?php echo htmlspecialchars($account_info['max_connections'] ?? 'N/A'); ?></p>
        <p><strong>Conexões Ativas:</strong> <?php echo htmlspecialchars($account_info['active_cons'] ?? 'N/A'); ?></p>
    </div>
    <?php include 'templates/footer.php'; ?>
</body>
</html>