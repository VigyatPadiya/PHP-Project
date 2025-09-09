<?php
session_start();
require 'config.php';

$msg = "";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $is_admin = 0; // Default to not admin

    $sql = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $username, $password, $is_admin);

    if ($stmt->execute()) {
        // Update admin statistics for new user
        $sql = "UPDATE admin_stats SET total_users = total_users + 1 WHERE id = 1";
        $conn->query($sql);
        
        $msg = "Registration successful! Please login.";
    } else {
        $msg = "Error: Username might already exist.";
    }
}

if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE username=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['username'] = $row['username'];
      $_SESSION['is_admin'] = $row['is_admin'] == 1;
      $sql = "UPDATE admin_stats SET total_users = total_users + 1 WHERE id = 1";
      $conn->query($sql);
      header("Location: index.php");
      exit;
    } else {
      $msg = "Invalid password!";
    }
  } else {
    $msg = "User not found!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login / Register - Flexi File Converter</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f172a, #1e293b);
      color: #f1f5f9;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(12px);
    }
  </style>
</head>

<body class="min-h-screen flex flex-col">

  <!-- Navbar -->
  <nav class="navbar">
    <div class="text-xl font-semibold text-white">FlexiFileConverter</div>

    <!-- Desktop Menu -->
    <div class="hidden md:flex space-x-6 text-white font-medium">
      <a href="index.php" class="hover:text-sky-400">Home</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="history.php" class="hover:text-sky-400"> My History</a>
        <a href="logout.php" class="hover:text-sky-400"> Logout</a>
      <?php else: ?>
        <a href="login.php" class="hover:text-sky-400">Login</a>
      <?php endif; ?>
    </div>

    <!-- Hamburger (Mobile Only) -->
    <div class="md:hidden">
      <button id="menu-toggle" class="text-white focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="absolute top-full right-4 mt-2 w-40 bg-slate-800 rounded-lg shadow-lg py-2 flex-col space-y-2 text-white font-medium hidden md:hidden z-50">
      <a href="index.php" class="block px-4 py-2 hover:bg-slate-700">Home</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="history.php" class="block px-4 py-2 hover:bg-slate-700"> My History</a>
        <a href="logout.php" class="block px-4 py-2 hover:bg-slate-700"> Logout</a>
      <?php else: ?>
        <a href="login.php" class="block px-4 py-2 hover:bg-slate-700"> Login</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="flex-1 flex items-center justify-center p-6">
    <div class="max-w-md w-full glass-card rounded-2xl shadow-xl p-8">
      <h2 class="text-xl font-bold mb-6 text-sky-400 text-center">Login / Register</h2>

      <?php if ($msg): ?>
        <div class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg text-red-300 text-sm">
          <?= $msg ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <input type="text" name="username" placeholder="Username" required
            class="w-full rounded-xl bg-slate-800 border border-sky-400 text-white p-3 focus:ring-2 focus:ring-sky-500 focus:outline-none">
        </div>

        <div>
          <input type="password" name="password" placeholder="Password" required
            class="w-full rounded-xl bg-slate-800 border border-sky-400 text-white p-3 focus:ring-2 focus:ring-sky-500 focus:outline-none">
        </div>

        <div class="flex space-x-4">
          <button type="submit" name="login"
            class="flex-1 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold shadow-lg transition">
            Login
          </button>
          <button type="submit" name="register"
            class="flex-1 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold shadow-lg transition">
            Register
          </button>
        </div>
      </form>

      <div class="mt-6 text-center">
        <a href="index.php" class="text-sky-400 hover:text-sky-300 text-sm">← Back to Home</a>
      </div>
    </div>
  </main>

  <footer class="text-center text-xs text-slate-400 py-4">
    © <script>
      document.write(new Date().getFullYear())
    </script> Flexi File Converter
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