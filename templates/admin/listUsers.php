<?php
if (!isset($_SESSION['username'])) {
    header('Location: admin.php');
    exit;
}

$users = [];
$error_message = '';

try {
    global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
    
    $conn = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT id, login AS username, is_active FROM users ORDER BY login");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

<div id="adminContent">
    <h1>User Management</h1>

    <?php if ($error_message): ?>
        <div class="errorMessage"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="buttons">
        <a href="admin.php?action=editUser" class="button">Add New User</a>
    </div>

    <table class="adminTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td>
                    <?php 
                        echo $user['is_active'] ? 'Yes' : 'No'; 
                    ?>
                </td>
                <td>
                    <a href="admin.php?action=editUser&id=<?php echo $user['id']; ?>" class="button">Edit</a>
                    <a href="admin.php?action=deleteUser&id=<?php echo $user['id']; ?>" 
                       class="button buttonDanger"
                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php endif; ?>
</div>

<?php include "templates/include/footer.php" ?>