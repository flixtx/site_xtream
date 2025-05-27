<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$stream_id = $_GET['stream_id'] ?? null;
if (!$stream_id) {
    header('Location: index.php?section=movies');
    exit;
}

$stream = xtream_api_call('get_vod_info', ['vod_id' => $stream_id]);
$stream_url = "https://zoreu-proxy.hf.space/proxy/stream?d=". rtrim($_SESSION['user']['host'], '/') . "/movie/" . urlencode($_SESSION['user']['username']) . "/" . urlencode($_SESSION['user']['password']) . "/" . $stream_id . ".mp4&api_password=abracadabra";
error_log("Movie Stream URL: $stream_url");

// Obtém categorias para o sidebar
$categories = xtream_api_call('get_vod_categories') ?? [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistir Filme</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://vjs.zencdn.net/8.16.1/video-js.css" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <h2><?php echo htmlspecialchars($stream['info']['name'] ?? 'Filme'); ?></h2>
        <video id="player" class="video-js vjs-default-skin" controls autoplay></video>
        <div id="info">
            <h3>Informações do Filme</h3>
            <p><strong>Gênero:</strong> <?php echo htmlspecialchars($stream['info']['genre'] ?? 'N/A'); ?></p>
            <p><strong>Duração:</strong> <?php echo htmlspecialchars($stream['info']['duration'] ?? 'N/A'); ?></p>
            <p><strong>Descrição:</strong> <?php echo htmlspecialchars($stream['info']['plot'] ?? 'Sem descrição'); ?></p>
        </div>
    </div>
    <?php include 'templates/footer.php'; ?>
    <script src="https://vjs.zencdn.net/8.16.1/video.min.js"></script>
    <script>
        const video = document.getElementById('player');
        const videoSrc = '<?php echo $stream_url; ?>';

        function loadStream() {
            const player = videojs('player', {
                controls: true,
                autoplay: true,
                sources: [{
                    src: videoSrc,
                    type: 'video/mp4'
                }],
                errorDisplay: false
            });

            player.on('error', function () {
                console.log('Erro no vídeo, tentando reconectar em 3 segundos...');
                player.dispose();
                setTimeout(loadStream, 3000);
            });
        }

        loadStream();
    </script>
</body>
</html>