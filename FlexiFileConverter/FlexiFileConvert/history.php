<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT file_name, converted_at FROM file_history WHERE user_id=? ORDER BY converted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Conversion History - Flexi File Converter</title>
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

  <!-- ✅ Navbar -->
  <nav class="navbar">
    <!-- Logo / Title -->
    <div class="text-xl font-semibold text-white">FlexiFileConverter</div>

    <!-- Desktop Menu -->
    <div class="hidden md:flex space-x-6 text-white font-medium">
      <a href="index.php" class="hover:text-sky-400">Home</a>
      <a href="about.php" class="hover:text-sky-400">About Us</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['is_admin']): ?>
          <a href="admin.php" class="hover:text-sky-400"> Admin</a>
        <?php endif; ?>
        <a href="history.php" class="hover:text-sky-400 text-sky-400">History</a>
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
          <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="absolute top-full right-4 mt-2 w-40 bg-slate-800 rounded-lg shadow-lg py-2 flex-col space-y-2 text-white font-medium hidden md:hidden z-50">
      <a href="index.php" class="block px-4 py-2 hover:bg-slate-700">Home</a>
      <a href="about.php" class="block px-4 py-2 hover:bg-slate-700">About Us</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="history.php" class="block px-4 py-2 hover:bg-slate-700 bg-slate-700"> History</a>
        <?php if ($_SESSION['is_admin']): ?>
          <a href="admin.php" class="block px-4 py-2 hover:bg-slate-700"> Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="block px-4 py-2 hover:bg-slate-700"> Logout</a>
      <?php else: ?>
        <a href="login.php" class="block px-4 py-2 hover:bg-slate-700"> Login</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Main -->
  <main class="flex-1 p-6">
    <div class="max-w-4xl mx-auto">
      <div class="glass-card rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold mb-6 text-sky-400">
          <?= htmlspecialchars($_SESSION['username']) ?>'s Conversion History
        </h2>

        <?php if ($result->num_rows > 0): ?>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-slate-800">
                  <th class="p-3 text-left">File Name</th>
                  <th class="p-3 text-left">Converted At</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr class="border-b border-slate-700">
                    <td class="p-3"><?= htmlspecialchars($row['file_name']) ?></td>
                    <td class="p-3"><?= $row['converted_at'] ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="glass-card rounded-xl p-6 text-center">
            <p>No conversion history found.</p>
          </div>
        <?php endif; ?>

        <div class="mt-6">
          <a href="index.php" class="inline-block py-2 px-6 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold shadow-lg transition">
            Convert Another File
          </a>
        </div>
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