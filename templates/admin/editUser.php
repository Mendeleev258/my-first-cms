<?php
if (!isset($_SESSION['username'])) {
    header('Location: admin.php');
    exit;
}

$user = null;
$error_message = '';
$success_message = '';

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
    
    $conn = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $active = isset($_POST['active']) ? 1 : 0;

        if (empty($username)) {
            $error_message = "Username is required.";
        } else {
            if ($user_id) {
                // Update existing user
                if (!empty($password)) {
                    // Hash the password if it's provided
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET login = ?, password = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$username, $hashed_password, $active, $user_id]);
                } else {
                    // Update without changing password
                    $stmt = $conn->prepare("UPDATE users SET login = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$username, $active, $user_id]);
                }
                $success_message = "User updated successfully.";
                
                // Fetch the updated user data
                $stmt = $conn->prepare("SELECT id, login AS username, is_active FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Create new user
                if (empty($password)) {
                    $error_message = "Password is required for new users.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (login, password, is_active) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $hashed_password, $active]);
                    $user_id = $conn->lastInsertId();
                    $success_message = "User created successfully.";
                    
                    // Fetch the newly created user data
                    $stmt = $conn->prepare("SELECT id, login AS username, is_active FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        }
    }

    // If we don't have user data yet and we have an ID, fetch it
    if (!$user && $user_id) {
        $stmt = $conn->prepare("SELECT id, login AS username, is_active FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $error_message = "User not found.";
        }
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

<div id="adminContent">
    <h1><?php echo isset($_GET['id']) ? 'Edit User' : 'Create User'; ?></h1>

    <?php if (isset($error_message) && $error_message): ?>
        <div class="errorMessage"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (isset($success_message) && $success_message): ?>
        <div class="successMessage"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form action="admin.php?action=<?php echo isset($_GET['id']) ? 'editUser' : 'editUser'; ?>" method="post">
        <?php if (isset($_GET['id'])): ?>
            <input type="hidden" name="userId" value="<?php echo (int)$_GET['id']; ?>">
        <?php endif; ?>

        <ul>
            <li>
                <label for="username">Login</label>
                <input type="text" name="username" id="username" placeholder="Username" required autofocus maxlength="50" value="<?php echo isset($user) && $user ? htmlspecialchars($user['username']) : ''; ?>" />
            </li>
            <li>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" <?php echo isset($_GET['id']) ? '' : 'required'; ?> />
                <?php if (isset($_GET['id'])): ?>
                    <p class="helpText">Leave blank to keep current password</p>
                <?php endif; ?>
            </li>
            <li>
                <label for="active">Active</label>
                <input type="checkbox" name="active" id="active" value="1" <?php echo (isset($user) && $user && $user['is_active']) ? 'checked' : ''; ?> />
            </li>
        </ul>

        <div class="buttons">
            <input type="submit" name="saveChanges" value="<?php echo isset($_GET['id']) ? 'Update User' : 'Create User'; ?>" />
            <input type="submit" formnovalidate name="cancel" value="Cancel" />
        </div>
    </form>
</div>

<?php include "templates/include/footer.php" ?>