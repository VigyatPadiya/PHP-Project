<?php
session_start();
require 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

// Handle delete actions
if (isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $type = $_GET['type'];
    $id = $_GET['id'];
    
    if ($action === 'delete') {
        if ($type === 'conversion') {
            // Delete conversion record
            $delete_sql = "DELETE FROM file_history WHERE id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Update stats
                $update_sql = "UPDATE admin_stats SET total_conversions = total_conversions - 1, total_files = total_files - 1 WHERE id = 1";
                $conn->query($update_sql);
                
                $_SESSION['message'] = "Conversion record deleted successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting conversion record.";
                $_SESSION['message_type'] = "error";
            }
        } elseif ($type === 'user') {
            // Check if user is trying to delete themselves
            if ($id == $_SESSION['user_id']) {
                $_SESSION['message'] = "You cannot delete your own account.";
                $_SESSION['message_type'] = "error";
            } else {
                // First delete user's conversion history
                $delete_history_sql = "DELETE FROM file_history WHERE user_id = ?";
                $stmt = $conn->prepare($delete_history_sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                // Then delete the user
                $delete_user_sql = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($delete_user_sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    // Update stats
                    $update_sql = "UPDATE admin_stats SET total_users = total_users - 1 WHERE id = 1";
                    $conn->query($update_sql);
                    
                    $_SESSION['message'] = "User deleted successfully.";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error deleting user.";
                    $_SESSION['message_type'] = "error";
                }
            }
        }
        
        // Redirect to avoid resubmission
        header("Location: admin.php");
        exit;
    }
}

// Get statistics
// Get live statistics
$total_users_sql = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = $conn->query($total_users_sql);
$total_users = $total_users_result->fetch_assoc()['total_users'];

$total_conversions_sql = "SELECT COUNT(*) AS total_conversions FROM file_history";
$total_conversions_result = $conn->query($total_conversions_sql);
$total_conversions = $total_conversions_result->fetch_assoc()['total_conversions'];

// If each record = 1 file, then total_files = total_conversions
$total_files = $total_conversions;


// Get recent conversions
$recent_sql = "SELECT fh.*, u.username 
               FROM file_history fh 
               JOIN users u ON fh.user_id = u.id 
               ORDER BY fh.converted_at DESC 
               LIMIT 10";
$recent_result = $conn->query($recent_sql);

// Get all users
$users_sql = "SELECT id, username, created_at, is_admin FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - Flexi File Converter</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #0f172a, #1e293b); color: #f1f5f9; }
    .glass-card { background: rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); backdrop-filter: blur(12px); }
    .message-success { background: rgba(72, 187, 120, 0.2); border: 1px solid rgba(72, 187, 120, 0.5); }
    .message-error { background: rgba(245, 101, 101, 0.2); border: 1px solid rgba(245, 101, 101, 0.5); }
  </style>
</head>
<body class="min-h-screen flex flex-col">

<!-- Navbar -->
<nav class="navbar">
  <!-- Logo / Title -->
  <div class="text-xl font-semibold text-white">FlexiFileConverter</div>

  <!-- Desktop Menu -->
  <div class="hidden md:flex space-x-6 text-white font-medium">
    <a href="index.php" class="hover:text-sky-400">Home</a>
    <a href="about.php" class="hover:text-sky-400">About Us</a>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['is_admin']): ?>
            <a href="admin.php" class="hover:text-sky-400 text-sky-400"> Admin</a>
            <?php endif; ?>
            <a href="history.php" class="hover:text-sky-400"> History</a>
      <a href="logout.php" class="hover:text-sky-400"> Logout</a>
    <?php else: ?>
      <a href="login.php" class="hover:text-sky-400"> Login</a>
    <?php endif; ?>
  </div>

  <!-- Hamburger (Mobile Only) -->
  <div class="md:hidden">
    <button id="menu-toggle" class="text-white focus:outline-none">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  <!-- Mobile Menu Dropdown -->
  <div id="mobile-menu" class="absolute top-full right-4 mt-2 w-40 bg-slate-800 rounded-lg shadow-lg py-2 flex-col space-y-2 text-white font-medium hidden md:hidden z-50">
    <a href="index.php" class="block px-4 py-2 hover:bg-slate-700">Home</a>
    <a href="about.php" class="block px-4 py-2 hover:bg-slate-700">About Us</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['is_admin']): ?>
            <a href="admin.php" class="block px-4 py-2 hover:bg-slate-700 bg-slate-700"> Admin</a>
            <?php endif; ?>
            <a href="history.php" class="block px-4 py-2 hover:bg-slate-700"> History</a>
      <a href="logout.php" class="block px-4 py-2 hover:bg-slate-700"> Logout</a>
    <?php else: ?>
      <a href="login.php" class="block px-4 py-2 hover:bg-slate-700"> Login</a>
    <?php endif; ?>
  </div>
</nav>

  <!-- Main -->
  <main class="flex-1 p-6">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-3xl font-bold mb-8 text-sky-400">Admin Dashboard</h1>
      
      <!-- Display messages -->
      <?php if (isset($_SESSION['message'])): ?>
        <div class="glass-card rounded-xl p-4 mb-6 <?php echo $_SESSION['message_type'] === 'success' ? 'message-success' : 'message-error'; ?>">
          <p><?php echo $_SESSION['message']; ?></p>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
      <?php endif; ?>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="glass-card rounded-2xl shadow-xl p-6">
          <h3 class="text-lg font-semibold mb-2 text-sky-300">Total Conversions</h3>
          <p class="text-3xl font-bold"><?= $total_conversions ?></p>
        </div>
        
        <div class="glass-card rounded-2xl shadow-xl p-6">
          <h3 class="text-lg font-semibold mb-2 text-sky-300">Total Users</h3>
          <p class="text-3xl font-bold"><?= $total_users ?></p>
        </div>
        
        <div class="glass-card rounded-2xl shadow-xl p-6">
          <h3 class="text-lg font-semibold mb-2 text-sky-300">Total Files</h3>
          <p class="text-3xl font-bold"><?= $total_files ?></p>
        </div>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Conversions -->
        <div class="glass-card rounded-2xl shadow-xl p-6">
          <h2 class="text-xl font-bold mb-4 text-sky-400">Recent Conversions</h2>
          
          <?php if ($recent_result->num_rows > 0): ?>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="bg-slate-800">
                    <th class="p-3 text-left">User</th>
                    <th class="p-3 text-left">File</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $recent_result->fetch_assoc()): ?>
                  <tr class="border-b border-slate-700">
                    <td class="p-3"><?= htmlspecialchars($row['username']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['file_name']) ?></td>
                    <td class="p-3"><?= $row['converted_at'] ?></td>
                    <td class="p-3">
                      <a href="admin.php?action=delete&type=conversion&id=<?= $row['id'] ?>" 
                         class="text-red-400 hover:text-red-300 text-sm"
                         onclick="return confirm('Are you sure you want to delete this conversion record?')">
                         Delete
                      </a>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="glass-card rounded-xl p-4 text-center">
              <p>No conversions found.</p>
            </div>
          <?php endif; ?>
        </div>
        
        <!-- User Management -->
        <div class="glass-card rounded-2xl shadow-xl p-6">
          <h2 class="text-xl font-bold mb-4 text-sky-400">User Management</h2>
          
          <?php if ($users_result->num_rows > 0): ?>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="bg-slate-800">
                    <th class="p-3 text-left">Username</th>
                    <th class="p-3 text-left">Joined</th>
                    <th class="p-3 text-left">Role</th>
                    <th class="p-3 text-left">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($user = $users_result->fetch_assoc()): ?>
                  <tr class="border-b border-slate-700">
                    <td class="p-3"><?= htmlspecialchars($user['username']) ?></td>
                    <td class="p-3"><?= $user['created_at'] ?></td>
                    <td class="p-3">
                      <span class="px-2 py-1 rounded-full text-xs <?= $user['is_admin'] ? 'bg-purple-500/20 text-purple-300' : 'bg-sky-500/20 text-sky-300' ?>">
                        <?= $user['is_admin'] ? 'Admin' : 'User' ?>
                      </span>
                    </td>
                    <td class="p-3">
                      <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="admin.php?action=delete&type=user&id=<?= $user['id'] ?>" 
                           class="text-red-400 hover:text-red-300 text-sm"
                           onclick="return confirm('Are you sure you want to delete this user? This will also delete all their conversion records.')">
                           Delete
                        </a>
                      <?php else: ?>
                        <span class="text-slate-500 text-sm">Current user</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="glass-card rounded-xl p-4 text-center">
              <p>No users found.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <footer class="text-center text-xs text-slate-400 py-4">
    © <script>document.write(new Date().getFullYear())</script> Flexi File Converter
  </footer>

<script>
  // Hamburger menu toggle
  const toggleBtn = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');

  toggleBtn.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
  });
</script>

</body>
</html>