<?php
session_start();
require 'things/admin-guard.php';

$pageTitle = 'Admin - Categories';
$message = '';

if (isset($_POST['add_category'])) {
    $newName = trim($_POST['new_name']);

    $query = $database->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
    $query->execute([$newName]);
    $sameNameCount = $query->fetchColumn();

    if ($newName == '') {
        $message = 'Please write a category name.';
    } elseif ($sameNameCount > 0) {
        $message = 'This category already exists.';
    } else {
        $query = $database->prepare('INSERT INTO categories (name) VALUES (?)');
        $query->execute([$newName]);
        $message = 'Category added.';
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
        $message = 'Please write a category name.';
    } elseif ($sameNameCount > 0) {
        $message = 'This category already exists.';
    } else {
        $query = $database->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $query->execute([$newName, $categoryId]);

        $query = $database->prepare('UPDATE vehicles SET category = ? WHERE category = ?');
        $query->execute([$newName, $oldName]);

        $message = 'Category updated.';
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
        $message = 'Cannot delete: some cars use this category.';
    } else {
        $query = $database->prepare('DELETE FROM categories WHERE id = ?');
        $query->execute([$categoryId]);
        $message = 'Category deleted.';
    }
}

$categoryRows = $database->query('SELECT categories.id, categories.name, (SELECT COUNT(*) FROM vehicles WHERE vehicles.category = categories.name) AS car_count FROM categories ORDER BY name')->fetchAll();

require 'things/header.php';
?>

<section class="wrap">
    <h1>Manage categories</h1>

    <?php if ($message != '') { ?>
        <p class="notice"><?= $message ?></p>
    <?php } ?>

    <form method="post" class="filters">
        <input name="new_name" placeholder="New category name" required>
        <button class="btn" name="add_category" value="1">Add category</button>
        <a class="btn ghost" href="admin.php">Back</a>
    </form>

    <table>
        <?php foreach ($categoryRows as $categoryRow) { ?>
            <tr>
                <td>
                    <form method="post" class="filters">
                        <input type="hidden" name="category_id" value="<?= $categoryRow['id'] ?>">
                        <input name="new_name" value="<?= ($categoryRow['name']) ?>" required>
                        <button class="btn ghost" name="rename_category" value="1">Save</button>
                        <button class="btn ghost confirm-delete" name="delete_category" value="1">Delete</button>
                    </form>
                </td>
                <td><?= $categoryRow['car_count'] ?> cars</td>
            </tr>
        <?php } ?>
    </table>
</section>

<script src="assets/js/forms.js"></script>

<?php require 'things/footer.php'; ?>