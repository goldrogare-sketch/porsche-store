<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/admin-guard.php';

$pageTitle = 'Admin - Categories';
$message = '';

if (isset($_POST['add_category'])) {
    $newName = trim($_POST['new_name']);

    $query = $database->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
    $query->execute([$newName]);
    $sameNameCount = $query->fetchColumn();

    if ($newName == '') {
        $message = 'Please provide a category name.';
    } elseif ($sameNameCount > 0) {
        $message = 'This category already exists.';
    } else {
        $query = $database->prepare('INSERT INTO categories (name) VALUES (?)');
        $query->execute([$newName]);
        $message = 'Category successfully added.';
    }
}

if (isset($_POST['rename_category'])) {
    $categoryId = (int) $_POST['category_id'];
    $newName = trim($_POST['new_name']);

    $query = $database->prepare('SELECT name FROM categories WHERE id = ?');
    $query->execute([$categoryId]);
    $oldName = $query->fetchColumn();

    $query = $database->prepare('SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?');
    $query->execute([$newName, $categoryId]);
    $sameNameCount = $query->fetchColumn();

    if ($newName == '') {
        $message = 'Please provide a category name.';
    } elseif ($sameNameCount > 0) {
        $message = 'This category name already exists.';
    } else {
        $query = $database->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $query->execute([$newName, $categoryId]);

        $query = $database->prepare('UPDATE vehicles SET category = ? WHERE category = ?');
        $query->execute([$newName, $oldName]);

        $message = 'Category updated successfully.';
    }
}

if (isset($_POST['delete_category'])) {
    $categoryId = (int) $_POST['category_id'];

    $query = $database->prepare('SELECT name FROM categories WHERE id = ?');
    $query->execute([$categoryId]);
    $oldName = $query->fetchColumn();

    $query = $database->prepare('SELECT COUNT(*) FROM vehicles WHERE category = ?');
    $query->execute([$oldName]);
    $carsInCategory = $query->fetchColumn();

    if ($carsInCategory > 0) {
        $message = 'Cannot delete: some vehicles are assigned to this category.';
    } else {
        $query = $database->prepare('DELETE FROM categories WHERE id = ?');
        $query->execute([$categoryId]);
        $message = 'Category removed.';
    }
}

$categoryRows = $database->query('SELECT categories.id, categories.name, (SELECT COUNT(*) FROM vehicles WHERE vehicles.category = categories.name) AS car_count FROM categories ORDER BY name')->fetchAll();

require_once __DIR__ . '/things/header.php';
?>

<section class="wrap admin-page">
    <div class="admin-header-row">
        <div>
            <span class="label-tiny">STORE STRUCTURE</span>
            <h1>Manage Vehicle Categories</h1>
            <p class="section-sub">Organize your Porsche range by body style and purpose.</p>
        </div>
        <a class="btn-ghost-luxury" href="admin.php"><i class="fa-solid fa-arrow-left"></i> Admin Hub</a>
    </div>

    <?php if ($message != ''): ?>
        <div class="notice"><?= safe($message) ?></div>
    <?php endif; ?>

    <form method="post" class="luxury-filters-bar" style="max-width: 500px; margin-bottom: 2rem;">
        <input name="new_name" placeholder="New Category Name (e.g. Cabriolet)" required>
        <button class="btn-porsche-primary" name="add_category" value="1"><i class="fa-solid fa-plus"></i> Add Category</button>
    </form>

    <div class="admin-table-container">
        <table class="luxury-admin-table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Associated Fleet</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categoryRows as $categoryRow): ?>
                    <tr>
                        <td>
                            <form method="post" style="display:flex; gap:0.6rem; align-items:center;">
                                <input type="hidden" name="category_id" value="<?= $categoryRow['id'] ?>">
                                <input name="new_name" value="<?= safe($categoryRow['name']) ?>" required style="max-width:220px;">
                                <button class="btn-table-sm" name="rename_category" value="1" title="Rename"><i class="fa-solid fa-check"></i> Save</button>
                            </form>
                        </td>
                        <td>
                            <span class="card-badge badge-category"><?= $categoryRow['car_count'] ?> models</span>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="category_id" value="<?= $categoryRow['id'] ?>">
                                <button class="btn-table-danger" name="delete_category" value="1"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
