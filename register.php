<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Name is required and must be at most 100 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, "user")');
            $stmt->execute(['name' => $name, 'email' => $email, 'hash' => $hash]);
            set_flash('success', 'Registration successful. Please log in.');
            header('Location: login.php');
            exit;
        }
    }
}

$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-card">
    <h1>Create your account</h1>
    <p class="muted">Register to manage your personal tasks.</p>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Full name<input name="name" maxlength="100" required value="<?= e($name) ?>"></label>
        <label>Email<input type="email" name="email" maxlength="190" required value="<?= e($email) ?>"></label>
        <label>Password<input type="password" name="password" minlength="8" required></label>
        <label>Confirm password<input type="password" name="confirm_password" minlength="8" required></label>
        <button class="btn primary full" type="submit">Create account</button>
    </form>
    <p class="muted center">Already registered? <a href="login.php">Log in</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
<?php
