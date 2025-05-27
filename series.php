<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$series_id = $_GET['series_id'] ?? null;
$episode_id = $_GET['episode_id'] ?? null;
$section = $_GET['section'] ?? 'live';
$title = $_GET['title'] ?? '';
if (!$series_id) {
    header('Location: index.php?section=series');
    exit;
}

$series = xtream_api_call('get_series_info', ['series_id' => $series_id]);
$stream_url_ = $episode_id ? rtrim($_SESSION['user']['host'], '/') . "/series/" . urlencode($_SESSION['user']['username']) . "/" . urlencode($_SESSION['user']['password']) . "/" . $episode_id . ".mp4" : '';
$stream_url = "https://zoreu-proxy.hf.space/proxy/stream?d=" .$stream_url_ . "&api_password=abracadabra";
if ($stream_url) {
    error_log("Series Stream URL: $stream_url");
}

// Obtém categorias para o sidebar
$categories = xtream_api_call('get_series_categories') ?? [];

// Prepara os dados das temporadas e episódios para o JavaScript
$seasons_data = [];
foreach ($series['episodes'] as $season => $episodes) {
    $seasons_data[$season] = array_map(function($episode) {
        return [
            'id' => $episode['id'],
            'episode_num' => $episode['episode_num'],
            'title' => $episode['title'] ?? 'Sem título'
        ];
    }, $episodes);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistir Série</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://vjs.zencdn.net/8.16.1/video-js.css" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <h2><?php echo htmlspecialchars($series['info']['name'] ?? 'Série'); ?> - <?php echo $title; ?></h2>
        <?php if ($episode_id && $stream_url): ?>
            <video id="player" class="video-js vjs-default-skin" controls autoplay></video>
        <?php endif; ?>
        <div id="info">
            <h3>Informações da Série</h3>
            <p><strong>Gênero:</strong> <?php echo htmlspecialchars($series['info']['genre'] ?? 'N/A'); ?></p>
            <p><strong>Descrição:</strong> <?php echo htmlspecialchars($series['info']['plot'] ?? 'Sem descrição'); ?></p>
            <h3>Selecionar Temporada</h3>
            <select id="season-select">
                <option value="">Selecione uma temporada</option>
                <?php foreach (array_keys($seasons_data) as $season): ?>
                    <option value="<?php echo htmlspecialchars($season); ?>">Temporada <?php echo htmlspecialchars($season); ?></option>
                <?php endforeach; ?>
            </select>
            <div id="episodes">
                <!-- Episódios serão preenchidos pelo JavaScript -->
            </div>
            <br>
            <br>
            <br>
            <?php include 'templates/footer.php'; ?>
        </div>
    </div>

    <!-- </?php include 'templates/footer.php'; ?> -->
    <script src="https://vjs.zencdn.net/8.16.1/video.min.js"></script>
    <script>
        // Passa os dados das temporadas para o JavaScript
        const seasonsData = <?php echo json_encode($seasons_data); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const seasonSelect = document.getElementById('season-select');
            const episodesDiv = document.getElementById('episodes');

            seasonSelect.addEventListener('change', (e) => {
                const season = e.target.value;
                episodesDiv.innerHTML = ''; // Limpa os episódios anteriores

                if (season && seasonsData[season]) {
                    const episodes = seasonsData[season];
                    const ul = document.createElement('ul');
                    episodes.forEach(episode => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.href = `?series_id=<?php echo $series_id; ?>&episode_id=${episode.id}&section=<?php echo $section; ?>&title=S${season}E${episode.episode_num}`;
                        a.textContent = `Episódio ${episode.episode_num}: ${episode.title}`;
                        li.appendChild(a);
                        ul.appendChild(li);
                    });
                    episodesDiv.appendChild(ul);
                }
            });

            // Seleciona a primeira temporada automaticamente, se disponível
            if (seasonSelect.options.length > 1) {
                seasonSelect.value = seasonSelect.options[1].value;
                seasonSelect.dispatchEvent(new Event('change'));
            }
        });

        <?php if ($episode_id && $stream_url): ?>
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
        <?php endif; ?>
    </script>
</body>
</html>