<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$section = filter_var($_GET['section'] ?? 'live', FILTER_UNSAFE_RAW);
?>
<header class="header">
    <div class="logo">
        <h1>IPTV</h1>
        <nav class="header-nav" aria-label="Navegação principal">
            <a href="index.php?section=live" <?php echo $section === 'live' ? 'class="active"' : ''; ?>>TV ao Vivo</a>
            <a href="index.php?section=movies" <?php echo $section === 'movies' ? 'class="active"' : ''; ?>>Filmes</a>
            <a href="index.php?section=series" <?php echo $section === 'series' ? 'class="active"' : ''; ?>>Séries</a>
            <a href="account.php">Informação da Conta</a>
        </nav>
    </div>
    <div class="search-container">
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="Buscar canais, filmes ou séries..." aria-label="Buscar conteúdo" required>
            <button type="submit" class="search-btn" aria-label="Buscar">
                <img src="assets/images/search-icon.png" alt="Lupa" class="search-icon">
            </button>
        </form>
    </div>
    <nav class="nav">
        <a href="logout.php" class="logout-btn">Sair</a>
    </nav>
</header>
