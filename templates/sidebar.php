<?php
$section = $_GET['section'] ?? 'live';
?>

<div class="sidebar">
    <h2>Categorias</h2>
    <?php if (!empty($categories)): ?>
        <h3><?php echo ucfirst($section); ?></h3>
        <ul>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="index.php?section=<?php echo $section; ?>&category_id=<?php echo htmlspecialchars($category['category_id']); ?>" <?php echo ($_GET['category_id'] ?? '') === $category['category_id'] ? 'class="active"' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>