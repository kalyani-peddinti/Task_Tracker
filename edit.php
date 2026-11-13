<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$task =  [ 'title' => '','description' => '','status' => 'Pending','priority' => 'Medium','due_date' => ''];

$errors = [];

if ($id !== null) {
    if (is_admin()) {
    $stmt = $pdo->prepare(
        'SELECT id, user_id, title, description, status, priority, due_date
         FROM tasks
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute(['id' => $id]);

} else {
    $stmt = $pdo->prepare(
        'SELECT id, user_id, title, description, status, priority, due_date
         FROM tasks
         WHERE id = :id AND user_id = :user_id
         LIMIT 1'
    );

    $stmt->execute([
        'id' => $id,
        'user_id' => (int)$_SESSION['user_id']
    ]);
}
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        set_flash('error', 'Task not found or you do not have permission to edit it.');
        header('Location: index.php');
        exit;
    }
    $task = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $task['title'] = trim((string)($_POST['title'] ?? ''));
    $task['description'] = trim((string)($_POST['description'] ?? ''));
    $task['status'] = (string)($_POST['status'] ?? 'Pending');
    $task['priority'] = (string)($_POST['priority'] ?? 'Medium');
    $task['due_date'] = trim((string)($_POST['due_date'] ?? ''));

    if (!valid_priority($task['priority'])) {
    $errors[] = 'Choose a valid priority.';
}

    if ($task['due_date'] !== '') {
    $date = DateTime::createFromFormat('!Y-m-d', $task['due_date']);

    if (!$date || $date->format('Y-m-d') !== $task['due_date']) {
        $errors[] = 'Enter a valid due date.';
    }
}

     // Required field validation
if ($task['title'] === '') {
    $errors[] = 'Please enter a task title.';
}

if ($task['description'] === '') {
    $errors[] = 'Please enter a task description.';
}

if ($task['status'] === '') {
    $errors[] = 'Please select a task status.';
} elseif (!valid_status($task['status'])) {
    $errors[] = 'Please select a valid task status.';
}

if ($task['due_date'] === '') {
    $errors[] = 'Please select a due date for your task.';
}

// Length validation
if (mb_strlen($task['title']) > 150) {
    $errors[] = 'Task title must be 150 characters or fewer.';
}

if (mb_strlen($task['description']) > 5000) {
    $errors[] = 'Task description must be 5,000 characters or fewer.';
}

// Priority validation
if (!valid_priority($task['priority'])) {
    $errors[] = 'Please select a valid priority.';
}

// Due date validation
if ($task['due_date'] !== '') {
    $date = DateTime::createFromFormat('!Y-m-d', $task['due_date']);

    if (!$date || $date->format('Y-m-d') !== $task['due_date']) {
        $errors[] = 'Please enter a valid due date.';
    }
}

    if (!$errors) {
        if ($id === null) {
            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, description, status, priority, due_date) VALUES (:user_id, :title, :description, :status, :priority, :due_date)');

    $stmt->execute([
    'user_id' => (int)$_SESSION['user_id'],
    'title' => $task['title'],
    'description' => $task['description'],
    'status' => $task['status'],
    'priority' => $task['priority'],
    'due_date' => $task['due_date'] !== '' ? $task['due_date'] : null]);
            set_flash('success', 'Task added successfully.');
        } else {
            if (is_admin()) {

    $stmt = $pdo->prepare(
        'UPDATE tasks
         SET title = :title,
             description = :description,
             status = :status,
             priority = :priority,
             due_date = :due_date
         WHERE id = :id'
    );

    $stmt->execute([
        'title' => $task['title'],
        'description' => $task['description'],
        'status' => $task['status'],
        'priority' => $task['priority'],
        'due_date' => $task['due_date'],
        'id' => $id
    ]);

} else {

    $stmt = $pdo->prepare(
        'UPDATE tasks
         SET title = :title,
             description = :description,
             status = :status,
             priority = :priority,
             due_date = :due_date
         WHERE id = :id AND user_id = :user_id'
    );

    $stmt->execute([
        'title' => $task['title'],
        'description' => $task['description'],
        'status' => $task['status'],
        'priority' => $task['priority'],
        'due_date' => $task['due_date'],
        'id' => $id,
        'user_id' => (int)$_SESSION['user_id']
    ]);
}
            set_flash('success', 'Task updated successfully.');
        }
        header('Location: index.php');
        exit;
    }
}

$pageTitle = $id === null ? 'Add task' : 'Edit task';
require __DIR__ . '/includes/header.php';
?>
<section class="form-card">
    <a class="back-link" href="index.php">← Back to tasks</a>
    <h1><?= $id === null ? 'Create a task' : 'Edit task' ?></h1>
    <p class="muted">Add clear details so you know exactly what to do.</p>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>
    Task Title <span class="required">*</span>
    <input
        type="text"
        name="title"
        maxlength="150"
        required
        value="<?= e($task['title']) ?>"
        placeholder="e.g. Prepare project presentation">
</label>

<label>
    Description <span class="required">*</span>
    <textarea
        name="description"
        maxlength="5000"
        rows="5"
        required
        placeholder="Enter your task description"><?= e($task['description']) ?></textarea>
</label>

<label>
    Status <span class="required">*</span>
    <select name="status" required>
        <option value="">Select status</option>

        <?php foreach (['Pending', 'In Progress', 'Completed'] as $option): ?>
            <option
                value="<?= e($option) ?>"
                <?= $task['status'] === $option ? 'selected' : '' ?>>
                <?= e($option) ?>
            </option>
        <?php endforeach; ?>
    </select>
</label>

<label>
    Priority
    <select name="priority">
        <?php foreach (['Low', 'Medium', 'High'] as $option): ?>
            <option
                value="<?= e($option) ?>"
                <?= $task['priority'] === $option ? 'selected' : '' ?>>
                <?= e($option) ?>
            </option>
        <?php endforeach; ?>
    </select>
</label>

<label>
    Due Date <span class="required">*</span>
    <input
        type="date"
        name="due_date"
        required
        value="<?= e($task['due_date'] ?? '') ?>">
</label>
        <div class="form-actions"><button class="btn primary" type="submit"><?= $id === null ? 'Save task' : 'Update task' ?></button><a class="btn secondary" href="index.php">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
