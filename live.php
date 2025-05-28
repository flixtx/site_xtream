<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$stream_id = filter_var($_GET['stream_id'] ?? null, FILTER_VALIDATE_INT);
if (!$stream_id) {
    header('Location: index.php');
    exit;
}

// Ajustar formato da URL do stream (verifique com seu servidor)
$stream_url = "https://zoreu-proxy.hf.space/proxy/hls/manifest.m3u8?d=" . rtrim($_SESSION['user']['host'], '/') . "/" . urlencode($_SESSION['user']['username']) . "/" . urlencode($_SESSION['user']['password']) . "/" . $stream_id . ".m3u8&api_password=abracadabra";
error_log("Stream URL: $stream_url");

// Obtém categorias para o sidebar
$categories = xtream_api_call('get_live_categories') ?? [];
if (!is_array($categories)) {
    $categories = [];
    error_log("Erro: Categorias de canais ao vivo inválidas");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistir TV ao Vivo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://vjs.zencdn.net/8.16.1/video-js.css" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <h2><?php echo htmlspecialchars($_GET['name'] ?? 'Canal'); ?></h2>
        <video id="player" class="video-js vjs-default-skin" controls autoplay></video>
        <div id="epg">
            <h3>Guia de Programação (EPG)</h3>
            <?php
            $epg = get_epg($stream_id);
            if ($epg && is_array($epg)) {
                foreach ($epg as $program) {
                    $start = htmlspecialchars($program['start'] ?? 'N/A');
                    $end = htmlspecialchars($program['end'] ?? 'N/A');
                    $title = htmlspecialchars($program['title'] ?? 'Sem título');
                    echo "<p><strong>$start - $end</strong>: $title</p>";
                }
            } else {
                echo "<p>EPG não disponível.</p>";
            }
            ?>
        </div>
    </div>
    <?php include 'templates/footer.php'; ?>
    <script src="https://vjs.zencdn.net/8.16.1/video.min.js"></script>
    <script>
        const player = videojs('player', {
            controls: true,
            autoplay: true,
            sources: [{
                src: <?php echo json_encode($stream_url); ?>,
                type: 'application/vnd.apple.mpegurl'
            }],
            errorDisplay: true
        });

        player.on('error', function () {
            console.error('Erro ao carregar o stream:', player.error());
            // O Video.js exibe automaticamente uma mensagem de erro padrão
        });
    </script>
</body>
</html>
