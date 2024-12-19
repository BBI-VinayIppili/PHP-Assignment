<?php
session_start();

// Initialize tasks array in the session if not already set
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Handle form submission for adding or updating a task
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = isset($_POST['task_id']) ? $_POST['task_id'] : null;
    $task_name = filter_input(INPUT_POST, 'task_name', FILTER_SANITIZE_STRING);
    $task_desc = filter_input(INPUT_POST, 'task_desc', FILTER_SANITIZE_STRING);
    $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_STRING);

    if ($task_name && $priority) {
        if ($task_id && isset($_SESSION['tasks'][$task_id])) {
            // Update existing task
            $_SESSION['tasks'][$task_id]['task_name'] = $task_name;
            $_SESSION['tasks'][$task_id]['task_desc'] = $task_desc;
            $_SESSION['tasks'][$task_id]['priority'] = $priority;
        } else {
            // Add new task
            $new_task_id = uniqid();
            $_SESSION['tasks'][$new_task_id] = [
                'task_name' => $task_name,
                'task_desc' => $task_desc,
                'priority' => $priority,
            ];
        }
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    unset($_SESSION['tasks'][$delete_id]);
}

// Handle edit action (populate the form)
$edit_task = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    if (isset($_SESSION['tasks'][$edit_id])) {
        $edit_task = $_SESSION['tasks'][$edit_id];
        $edit_task['task_id'] = $edit_id;
    }
}

// Handle theme preference
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light-theme'; // Default to 'light-theme'
if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];
    setcookie('theme', $theme, time() + 86400 * 30, '/'); 
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <link rel="stylesheet" href="style.css">
     <!-- Include Font Awesome -->
     <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="<?php echo htmlspecialchars($theme); ?>">
    <div class="navbar">
        <h1>Task Management System</h1>
        <!-- Theme Toggle -->
        <button class="theme-toggle-btn" 
                onclick="toggleTheme('<?php echo $theme === 'light-theme' ? 'dark-theme' : 'light-theme'; ?>');">
            <?php if ($theme === 'light-theme'): ?>
                <i class="fas fa-moon"></i> 
            <?php else: ?>
                <i class="fas fa-sun"></i> 
            <?php endif; ?>
        </button>
    </div>

    <div class="container">
        <!-- Form for Adding/Updating Tasks -->
        <form action="index.php" method="POST" class="task-form">
            <input type="hidden" name="task_id" value="<?php echo isset($edit_task['task_id']) ? htmlspecialchars($edit_task['task_id']) : ''; ?>">
            <div class="form-group">
                <label for="task-name">Task Name:</label>
                <input type="text" id="task-name" name="task_name" placeholder="Enter task name" 
                    value="<?php echo isset($edit_task['task_name']) ? htmlspecialchars($edit_task['task_name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="task-desc">Task Description (Optional):</label>
                <textarea id="task-desc" name="task_desc" placeholder="Enter task description"><?php echo isset($edit_task['task_desc']) ? htmlspecialchars($edit_task['task_desc']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="priority">Priority:</label>
                <select id="priority" name="priority" required>
                    <option value="High" <?php echo (isset($edit_task['priority']) && $edit_task['priority'] === 'High') ? 'selected' : ''; ?>>High</option>
                    <option value="Medium" <?php echo (isset($edit_task['priority']) && $edit_task['priority'] === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="Low" <?php echo (isset($edit_task['priority']) && $edit_task['priority'] === 'Low') ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>
            <button type="submit" class="add-btn"><?php echo $edit_task ? 'Update Task' : 'Add Task'; ?></button>
        </form>

        <!-- Table to Show Tasks -->
        <h2>Task List</h2>
        <table class="task-table">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($_SESSION['tasks'])): ?>
                    <?php foreach ($_SESSION['tasks'] as $task_id => $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task_id); ?></td>
                            <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                            <td><?php echo htmlspecialchars($task['task_desc']); ?></td>
                            <td><?php echo htmlspecialchars($task['priority']); ?></td>
                            <td>
                                <a href="index.php?edit_id=<?php echo urlencode($task_id); ?>" class="edit-btn">Edit</a>
                                <a href="index.php?delete_id=<?php echo urlencode($task_id); ?>" class="delete-btn">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No tasks added yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleTheme(theme) {
            document.body.className = theme;
            // Redirect to save the theme in a cookie
            window.location.href = `index.php?theme=${theme}`;
        }
    </script>
</body>
</html>
