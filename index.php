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

$section = $_GET['section'] ?? 'live';
$category_id = $_GET['category_id'] ?? null;

$categories = [];
$content = [];

// Determina as categorias e o conteúdo com base na seção
if ($section === 'live') {
    $categories = xtream_api_call('get_live_categories') ?? [];
    $category_id = $category_id ?: ($categories[0]['category_id'] ?? null);
    $content = $category_id ? get_live_streams($category_id) : [];
} elseif ($section === 'movies') {
    $categories = xtream_api_call('get_vod_categories') ?? [];
    $category_id = $category_id ?: ($categories[0]['category_id'] ?? null);
    $content = $category_id ? get_vod_streams($category_id) : [];
} elseif ($section === 'series') {
    $categories = xtream_api_call('get_series_categories') ?? [];
    $category_id = $category_id ?: ($categories[0]['category_id'] ?? null);
    $content = $category_id ? get_series($category_id) : [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPTV Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content">
        <h2><?php echo ucfirst($section); ?> - <?php echo htmlspecialchars($categories[array_search($category_id, array_column($categories, 'category_id'))]['category_name'] ?? 'Primeira Categoria'); ?></h2>
        <?php if (empty($content)): ?>
            <p>Nenhum conteúdo disponível para esta categoria.</p>
        <?php else: ?>
            <div class="catalog">
                <?php foreach ($content as $item): ?>
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
                            <a href="live.php?stream_id=<?php echo $item['stream_id']; ?>&section=<?php echo $section; ?>&name=<?php echo htmlspecialchars($item['name']); ?>">Assistir</a>
                        <?php elseif ($section === 'movies'): ?>
                            <a href="movies.php?stream_id=<?php echo $item['stream_id']; ?>&section=<?php echo $section; ?>&name=<?php echo htmlspecialchars($item['name']); ?>">Assistir</a>
                        <?php elseif ($section === 'series'): ?>
                            <a href="series.php?series_id=<?php echo $item['series_id']; ?>&section=<?php echo $section; ?>&name=<?php echo htmlspecialchars($item['name']); ?>">Assistir</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>
