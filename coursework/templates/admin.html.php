
<?php
try{
$sql = 'SELECT id, module_name FROM module';
    $result = $pdo->query($sql);
    $modules = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi khi thực thi câu lệnh SQL: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Module</title>
    <link rel="stylesheet" href="css\admin.css">
</head>
<body>
    <header>
        <div class="header-container">
            <nav>
                <a href="index.php" class="nav-link">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-left-fill" viewBox="0 0 16 16">
                    <path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z"/>
                  </svg> Back
                </a>
            </nav>
        </div>
    </header>
    <h1>Manage Module</h1>
    <form action="admin.php" method="post" class="module-form">
        <label for="module">Select Module:</label>
        <div class="form-group">
            <select name="module_name" id="module">
                <?php foreach ($modules as $module): ?>
                    <option value="<?php echo $module['id']; ?>"><?php echo htmlspecialchars($module['module_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <label for="new_module">Choose New Module:</label>
        <input type="text" name="new_module" id="new_module" placeholder="Enter New Module">
        <?php
            if (isset($_SESSION['message'])) {
                echo '<p class="success">' . $_SESSION['message'] . '</p>';
                unset($_SESSION['message']);
            }

            if (isset($_SESSION['error'])) {
                echo '<p class="error">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
        ?>
        <button type="submit">Confirm</button>
    </form>
</body>
</html>