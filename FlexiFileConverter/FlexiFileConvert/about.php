<?php // public/about.php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About Us - Flexi File Converter</title>
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
      <a href="about.php" class="hover:text-sky-400 text-sky-400">About Us</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['is_admin']): ?>
          <a href="admin.php" class="hover:text-sky-400"> Admin</a>
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
          <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="absolute top-full right-4 mt-2 w-40 bg-slate-800 rounded-lg shadow-lg py-2 flex-col space-y-2 text-white font-medium hidden md:hidden z-50">
      <a href="index.php" class="block px-4 py-2 hover:bg-slate-700">Home</a>
      <a href="about.php" class="block px-4 py-2 hover:bg-slate-700 bg-slate-700">About Us</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($_SESSION['is_admin']): ?>
          <a href="admin.php" class="block px-4 py-2 hover:bg-slate-700"> Admin</a>
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
    <div class="max-w-4xl mx-auto">
      <div class="glass-card rounded-2xl shadow-xl p-8 mb-8">
        <h1 class="text-3xl font-bold mb-6 text-sky-400 text-center">About Flexi File Converter</h1>

        <p class="mb-6 text-lg">
          Flexi File Converter is a powerful, user-friendly tool designed to help you convert your files between various formats with ease.
          Whether you need to convert documents, spreadsheets, presentations, or images, our platform provides a seamless experience.
        </p>

        <div class="grid md:grid-cols-2 gap-8 mb-8">
          <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-semibold mb-4 text-sky-400">Our Mission</h2>
            <p>
              To simplify file conversion for everyone, eliminating the need for multiple software installations
              and providing a secure, reliable platform that works across all your devices.
            </p>
          </div>

          <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-semibold mb-4 text-sky-400">Why Choose Us</h2>
            <ul class="list-disc pl-5 space-y-2">
              <li>Fast and efficient conversions</li>
              <li>Support for numerous file formats</li>
              <li>Secure processing with no file storage</li>
              <li>User-friendly interface</li>
              <li>No registration required for basic use</li>
            </ul>
          </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4 text-sky-400">Supported Formats</h2>

        <div class="overflow-x-auto mb-8">
          <table class="w-full">
            <thead>
              <tr class="bg-slate-800">
                <th class="p-3 text-left">Category</th>
                <th class="p-3 text-left">Input Formats</th>
                <th class="p-3 text-left">Output Formats</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-slate-700">
                <td class="p-3 font-medium">Documents</td>
                <td class="p-3">PDF, DOC, DOCX</td>
                <td class="p-3">PDF, DOCX, ODT, RTF, TXT, HTML, EPUB</td>
              </tr>
              <tr class="border-b border-slate-700">
                <td class="p-3 font-medium">Spreadsheets</td>
                <td class="p-3">XLS, XLSX, ODS, CSV</td>
                <td class="p-3">PDF, XLSX, ODS, CSV, HTML</td>
              </tr>
              <tr class="border-b border-slate-700">
                <td class="p-3 font-medium">Presentations</td>
                <td class="p-3">PPT, PPTX, ODP</td>
                <td class="p-3">PDF, PPTX, ODP, PNG, JPG</td>
              </tr>
              <tr>
                <td class="p-3 font-medium">Images</td>
                <td class="p-3">PNG, JPG, JPEG</td>
                <td class="p-3">PDF, PNG, JPG</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="glass-card rounded-xl p-6">
          <h2 class="text-xl font-semibold mb-4 text-sky-400">Get Started</h2>
          <p class="mb-4">
            Ready to convert your files? It's simple! Just head to our <a href="index.php" class="text-sky-400 hover:underline">home page</a>,
            upload your file, select your desired output format, and let Flexi File Converter do the rest.
          </p>
          <a href="index.php" class="inline-block py-2 px-6 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold shadow-lg transition">
            Convert Files Now
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