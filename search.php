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

$query = filter_var($_GET['query'] ?? '', FILTER_UNSAFE_RAW);
$results = [];

if ($query) {
    // Buscar em todas as seções
    $results['live'] = search_content('live', $query);
    $results['movies'] = search_content('movies', $query);
    $results['series'] = search_content('series', $query);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Resultados da busca no painel IPTV">
    <title>Resultados da Busca - IPTV</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <h2>Resultados da Busca: <?php echo htmlspecialchars($query); ?></h2>
        <?php if (empty($query)): ?>
            <p>Por favor, insira um termo de busca.</p>
        <?php elseif (empty($results['live']) && empty($results['movies']) && empty($results['series'])): ?>
            <p>Nenhum resultado encontrado para "<?php echo htmlspecialchars($query); ?>".</p>
        <?php else: ?>
            <?php foreach (['live' => 'TV ao Vivo', 'movies' => 'Filmes', 'series' => 'Séries'] as $section => $title): ?>
                <?php if (!empty($results[$section])): ?>
                    <h3><?php echo $title; ?></h3>
                    <div class="catalog">
                        <?php foreach ($results[$section] as $item): ?>
                            <div class="content-item">
                                <?php
                                    $image_url = 'assets/images/placeholder.png';
                                    if (!empty($item['stream_icon'])) {
                                        $image_url = 'https://da5f663b4690-proxyimage.baby-beamup.club/proxy-image/?url=' . urlencode($item['stream_icon']);
                                    } elseif (!empty($item['cover'])) {
                                        $image_url = 'https://da5f663b4690-proxyimage.baby-beamup.club/proxy-image/?url=' . urlencode($item['cover']);
                                    }
                                ?>
                                <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <?php if ($section === 'live'): ?>
                                    <a href="live.php?stream_id=<?php echo $item['stream_id']; ?>&section=live&name=<?php echo htmlspecialchars($item['name']); ?>">Assistir</a>
                                <?php elseif ($section === 'movies'): ?>
                                    <a href="movies.php?stream_id=<?php echo $item['stream_id']; ?>&section=movies&name=<?php echo htmlspecialchars($item['name']); ?>">Assistir</a>
                                <?php elseif ($section === 'series'): ?>
                                    <a href="series.php?series_id=<?php echo $item['series_id']; ?>&section=series&name=<?php echo htmlspecialchars($item['name']); ?>">Assistir</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>
