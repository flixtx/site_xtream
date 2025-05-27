<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
//require_once 'dns.php';
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
session_start();

// Função para criptografar dados no cookie
function encrypt_cookie($data) {
    $key = 'sucodeabacaxi'; // Substitua por uma chave segura
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Função para descriptografar dados do cookie
function decrypt_cookie($data) {
    $key = 'sucodeabacaxi'; // Mesma chave usada acima
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

// Verificar cookies para login automático
if (!is_logged_in() && isset($_COOKIE['iptv_remember'])) {
    $cookie_data = decrypt_cookie($_COOKIE['iptv_remember']);
    if ($cookie_data) {
        $user_data = json_decode($cookie_data, true);
        if (isset($user_data['username'], $user_data['password'], $user_data['host'])) {
            $test_url = rtrim($user_data['host'], '/') . '/player_api.php?username=' . urlencode($user_data['username']) . '&password=' . urlencode($user_data['password']) . '&action=get_account_info';
            $ch = curl_init($test_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200 && $response) {
                $account_info = json_decode($response, true);
                if ($account_info && isset($account_info['user_info']['auth']) && $account_info['user_info']['auth'] == 1) {
                    $_SESSION['user'] = [
                        'username' => $user_data['username'],
                        'password' => $user_data['password'],
                        'host' => $user_data['host'],
                        'account_info' => $account_info['user_info']
                    ];
                    header('Location: index.php');
                    exit;
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']); // Verificar se "Manter conectado" está marcado

    // Validação básica
    if (empty($username) || empty($password) || empty($host)) {
        $error = "Por favor, preencha todos os campos.";
    } else {
        // Testa a conexão com o host usando /player_api.php
        $test_url = rtrim($host, '/') . '/player_api.php?username=' . urlencode($username) . '&password=' . urlencode($password) . '&action=get_account_info';
        $ch = curl_init($test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200 || !$response) {
            $error = "Não foi possível conectar ao host fornecido. Verifique o endereço.";
        } else {
            $account_info = json_decode($response, true);
            if ($account_info && isset($account_info['user_info']['auth']) && $account_info['user_info']['auth'] == 1) {
                $_SESSION['user'] = [
                    'username' => $username,
                    'password' => $password,
                    'host' => $host,
                    'account_info' => $account_info['user_info']
                ];

                // Salvar cookies se "Manter conectado" estiver marcado
                if ($remember) {
                    $cookie_data = json_encode([
                        'username' => $username,
                        'password' => $password,
                        'host' => $host
                    ]);
                    $encrypted_data = encrypt_cookie($cookie_data);
                    setcookie('iptv_remember', $encrypted_data, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 dias, httponly
                }

                header('Location: index.php');
                exit;
            } else {
                $error = "Usuário, senha ou host inválidos. ";
                if ($account_info && isset($account_info['user_info']['status'])) {
                    $error .= "Status da conta: " . $account_info['user_info']['status'];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IPTV</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="host" placeholder="Host (ex.: http://seuservidor.com:8080)" value="<?php echo isset($_POST['host']) ? htmlspecialchars($_POST['host']) : ''; ?>" required>
            <input type="text" name="username" placeholder="Usuário" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            <input type="password" name="password" placeholder="Senha" required>
            <div class="remember-me">
                <input type="checkbox" name="remember" id="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                <label for="remember">Manter conectado</label>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>
    <?php include 'templates/footer.php'; ?>
</body>
</html>