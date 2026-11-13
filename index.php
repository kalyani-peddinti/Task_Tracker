<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$q = trim((string)($_GET['q'] ?? ''));
$status = (string)($_GET['status'] ?? '');

if (!valid_status($status)) {
    $status = '';
}

/*
|--------------------------------------------------------------------------
| RBAC: Fetch tasks based on the logged-in user's role
|--------------------------------------------------------------------------
| Admin: Can view all tasks.
| User: Can view only their own tasks.
*/

$sql = 'SELECT tasks.id, tasks.user_id, tasks.title,
               tasks.description, tasks.status, tasks.priority,
               tasks.due_date, tasks.created_at, users.name AS owner_name
        FROM tasks
        INNER JOIN users ON tasks.user_id = users.id';

$conditions = [];
$params = [];

if (!is_admin()) {
    $conditions[] = 'tasks.user_id = :user_id';
    $params['user_id'] = (int)$_SESSION['user_id'];
}

if ($q !== '') {
    $conditions[] = 'tasks.title LIKE :search';
    $params['search'] = '%' . $q . '%';
}

if ($status !== '') {
    $conditions[] = 'tasks.status = :status';
    $params['status'] = $status;
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY tasks.created_at DESC, tasks.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| RBAC: Dashboard summary counts
|--------------------------------------------------------------------------
*/

$countSql = 'SELECT COUNT(*) AS total,
                    SUM(status = "Pending") AS pending,
                    SUM(status = "In Progress") AS in_progress,
                    SUM(status = "Completed") AS completed
             FROM tasks';

$countParams = [];

if (!is_admin()) {
    $countSql .= ' WHERE user_id = :user_id';
    $countParams['user_id'] = (int)$_SESSION['user_id'];
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$counts = $countStmt->fetch();

$pageTitle = is_admin() ? 'Admin Dashboard' : 'My tasks';

require __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">
            <?= is_admin() ? 'ADMIN WORKSPACE' : 'YOUR WORKSPACE' ?>
        </p>

        <h1>
            <?= is_admin() ? 'Admin Dashboard' : 'My tasks' ?>
        </h1>

        <p class="muted">
            <?= is_admin()
                ? 'Manage and monitor tasks across all users.'
                : 'Keep track of what needs to get done.' ?>
        </p>
    </div>

    <a class="btn primary" href="edit.php">+ Add task</a>
</div>

<section class="stats">

    <div class="stat">
        <span>Total tasks</span>
        <strong><?= (int)($counts['total'] ?? 0) ?></strong>
    </div>

    <div class="stat">
        <span>Pending</span>
        <strong><?= (int)($counts['pending'] ?? 0) ?></strong>
    </div>

    <div class="stat">
        <span>In progress</span>
        <strong><?= (int)($counts['in_progress'] ?? 0) ?></strong>
    </div>

    <div class="stat">
        <span>Completed</span>
        <strong><?= (int)($counts['completed'] ?? 0) ?></strong>
    </div>

</section>

<form class="filters" method="get" action="index.php">

    <input
        type="search"
        name="q"
        placeholder="Search by task title..."
        value="<?= e($q) ?>"
    >

    <select name="status" aria-label="Filter by status">

        <option value="">All statuses</option>

        <?php foreach (['Pending', 'In Progress', 'Completed'] as $option): ?>

            <option
                value="<?= e($option) ?>"
                <?= $status === $option ? 'selected' : '' ?>
            >
                <?= e($option) ?>
            </option>

        <?php endforeach; ?>

    </select>

    <button class="btn secondary" type="submit">Apply</button>

    <a class="clear-link" href="index.php">Clear</a>

</form>

<section class="task-list">

<?php if (!$tasks): ?>

    <div class="empty-state">
        <h2>No tasks found</h2>

        <p class="muted">
            Add a task or change your search/filter.
        </p>

        <a href="edit.php" class="btn primary">
            Create your first task
        </a>
    </div>

<?php else: ?>

    <?php foreach ($tasks as $task): ?>

        <article class="task-card">

            <div class="task-main">

                <div class="task-title-row">

                    <h2><?= e($task['title']) ?></h2>

                    <span class="status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>">
                        <?= e($task['status']) ?>
                    </span>

                </div>

                <p class="description">
                    <?= nl2br(e($task['description'])) ?>
                </p>

                <div class="task-details">

                    <span class="priority priority-<?= strtolower(e($task['priority'])) ?>">
                        Priority: <?= e($task['priority']) ?>
                    </span>

                    <span class="muted">
                        Due date:
                        <?= !empty($task['due_date'])
                            ? e(date('d M Y', strtotime($task['due_date'])))
                            : 'Not set' ?>
                    </span>

                </div>

                <?php if (is_admin()): ?>

                    <p class="muted">
                        Task owner: <?= e($task['owner_name']) ?>
                    </p>

                <?php endif; ?>

                <small class="muted">
                    Created <?= e(date('d M Y, h:i A', strtotime($task['created_at']))) ?>
                </small>

            </div>

            <div class="task-actions">

                <a
                    class="btn small secondary"
                    href="edit.php?id=<?= (int)$task['id'] ?>"
                >
                    Edit
                </a>

                <form
                    method="post"
                    action="task_action.php"
                    data-confirm="Are you sure you want to delete this task?"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <input type="hidden" name="action" value="delete">

                    <input
                        type="hidden"
                        name="task_id"
                        value="<?= (int)$task['id'] ?>"
                    >

                    <button class="btn small danger" type="submit">
                        Delete
                    </button>

                </form>

                <form
                    method="post"
                    action="task_action.php"
                    class="status-form"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <input type="hidden" name="action" value="status">

                    <input
                        type="hidden"
                        name="task_id"
                        value="<?= (int)$task['id'] ?>"
                    >

                    <label
                        class="sr-only"
                        for="status-<?= (int)$task['id'] ?>"
                    >
                        Change status
                    </label>

                    <select
                        id="status-<?= (int)$task['id'] ?>"
                        name="status"
                        onchange="this.form.submit()"
                    >

                        <?php foreach (['Pending', 'In Progress', 'Completed'] as $option): ?>

                            <option
                                value="<?= e($option) ?>"
                                <?= $task['status'] === $option ? 'selected' : '' ?>
                            >
                                <?= e($option) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <noscript>
                        <button class="btn small secondary" type="submit">
                            Update status
                        </button>
                    </noscript>

                </form>

            </div>

        </article>

    <?php endforeach; ?>

<?php endif; ?>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
