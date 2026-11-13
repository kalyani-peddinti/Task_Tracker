<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$action = (string)($_POST['action'] ?? '');
$id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['user_id'];
$isAdmin = is_admin();

if (!$id || $id < 1) {
    set_flash('error', 'Invalid task selected.');
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| RBAC: Delete task
|--------------------------------------------------------------------------
| Admin: Can delete any task.
| User: Can delete only their own task.
*/

if ($action === 'delete') {

    if ($isAdmin) {

        $stmt = $pdo->prepare(
            'DELETE FROM tasks WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);

    } else {

        $stmt = $pdo->prepare(
            'DELETE FROM tasks WHERE id = :id AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    if ($stmt->rowCount() > 0) {
        set_flash('success', 'Task deleted successfully.');
    } else {
        set_flash('error', 'Task not found or permission denied.');
    }

/*
|--------------------------------------------------------------------------
| RBAC: Update task status
|--------------------------------------------------------------------------
| Admin: Can update any task status.
| User: Can update only their own task status.
*/

} elseif ($action === 'status') {

    $status = (string)($_POST['status'] ?? '');

    if (!valid_status($status)) {

        set_flash('error', 'Choose a valid status.');

    } else {

        if ($isAdmin) {

            $stmt = $pdo->prepare(
                'UPDATE tasks SET status = :status WHERE id = :id'
            );

            $stmt->execute([
                'status' => $status,
                'id' => $id
            ]);

            $check = $pdo->prepare(
                'SELECT id FROM tasks WHERE id = :id LIMIT 1'
            );

            $check->execute(['id' => $id]);

        } else {

            $stmt = $pdo->prepare(
                'UPDATE tasks
                 SET status = :status
                 WHERE id = :id AND user_id = :user_id'
            );

            $stmt->execute([
                'status' => $status,
                'id' => $id,
                'user_id' => $userId
            ]);

            $check = $pdo->prepare(
                'SELECT id FROM tasks
                 WHERE id = :id AND user_id = :user_id
                 LIMIT 1'
            );

            $check->execute([
                'id' => $id,
                'user_id' => $userId
            ]);
        }

        if ($check->fetch()) {
            set_flash('success', 'Task status updated.');
        } else {
            set_flash('error', 'Task not found or permission denied.');
        }
    }

} else {

    set_flash('error', 'Unknown action.');
}

header('Location: index.php');
exit;
