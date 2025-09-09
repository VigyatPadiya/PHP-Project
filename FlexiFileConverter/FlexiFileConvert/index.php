<?php // public/index.php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Flexi File Converter</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f172a, #1e293b);
      color: #f1f5f9;
    }

    .dropzone.dragover {
      border-color: #38bdf8;
      background: rgba(56, 189, 248, .1);
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(12px);
    }
  </style>
</head>

<body class="min-h-screen flex flex-col">

  <!--  Navbar -->
  <nav class="navbar">
    <!-- Logo / Title -->
    <div class="text-xl font-semibold text-white">FlexiFileConverter</div>

    <!-- Desktop Menu -->
    <div class="hidden md:flex space-x-6 text-white font-medium">
      <a href="index.php" class="hover:text-sky-400 text-sky-400">Home</a>
      <a href="about.php" class="hover:text-sky-400">About Us</a>

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
      <a href="index.php" class="block px-4 py-2 hover:bg-slate-700 bg-slate-700">Home</a>
      <a href="about.php" class="block px-4 py-2 hover:bg-slate-700">About Us</a>

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
  <main class="flex-1 flex items-center justify-center p-6">

    <div class="max-w-3xl w-full glass-card rounded-2xl shadow-xl p-8">
      <h2 class="text-xl font-bold mb-6 text-sky-400">Upload & Convert</h2>

      <form id="convertForm" action="convert.php" method="POST" enctype="multipart/form-data" class="space-y-6">

        <!-- Dropzone -->
        <div id="dropzone" class="dropzone border-2 border-dashed border-sky-400 rounded-2xl p-10 text-center cursor-pointer">
          <input id="fileInput" type="file" name="file" class="hidden" required>
          <div class="flex flex-col items-center gap-3 pointer-events-none">
            <p class="text-sky-400">[ Drag & Drop File ]</p>
            <p class="text-sm text-slate-400">or click to browse</p>
            <p id="fileName" class="text-sm text-sky-400 font-medium hidden"></p>
          </div>
        </div>

        <!-- Hidden Source (auto-detected) -->
        <input type="hidden" id="source" name="source">

        <!-- Target -->
        <div>
          <label for="target" class="block text-sm font-semibold text-sky-400 mb-2">Convert To</label>
          <select id="target" name="target" class="w-full rounded-xl bg-slate-800 border border-sky-400 text-sky-300">
            <option disabled selected>Upload a file first...</option>
          </select>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold shadow-lg transition">
          Convert Now
        </button>
      </form>
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

    // Conversion rules
    const rules = {
      pdf: ["html", "png", "jpg"],
      word: ["pdf", "odt", "rtf", "txt", "html", "png", "jpg"],
      excel: ["pdf", "xlsx", "ods", "csv", "html"],
      ppt: ["pdf", "pptx", "odp", "png", "jpg"],
      text: ["pdf", "docx", "odt", "html"],
      image: ["pdf", "png", "jpg"]
    };

    // Map extensions to groups
    const extGroups = {
      pdf: ["pdf"],
      word: ["doc", "docx"],
      excel: ["xls", "xlsx", "ods", "csv"],
      ppt: ["ppt", "pptx", "odp"],
      text: ["txt", "rtf"],
      image: ["png", "jpg", "jpeg"]
    };

    const dropzone = document.getElementById("dropzone");
    const fileInput = document.getElementById("fileInput");
    const fileName = document.getElementById("fileName");
    const sourceField = document.getElementById("source");
    const targetSelect = document.getElementById("target");

    function detectSource(ext) {
      ext = ext.toLowerCase();
      for (let group in extGroups) {
        if (extGroups[group].includes(ext)) return group;
      }
      return null;
    }

    function updateTargets(group) {
      targetSelect.innerHTML = "";
      if (!group) {
        const opt = document.createElement("option");
        opt.textContent = "Unsupported file type";
        opt.disabled = true;
        targetSelect.appendChild(opt);
        return;
      }
      rules[group].forEach(ext => {
        const opt = document.createElement("option");
        opt.value = ext;
        opt.textContent = ext.toUpperCase();
        targetSelect.appendChild(opt);
      });
    }

    function handleFile(file) {
      fileName.textContent = file.name;
      fileName.classList.remove("hidden");
      const ext = file.name.split(".").pop();
      const group = detectSource(ext);
      sourceField.value = group || "";
      updateTargets(group);
    }

    dropzone.addEventListener("click", () => fileInput.click());
    dropzone.addEventListener("dragover", (e) => {
      e.preventDefault();
      dropzone.classList.add("dragover");
    });
    dropzone.addEventListener("dragleave", () => dropzone.classList.remove("dragover"));
    dropzone.addEventListener("drop", (e) => {
      e.preventDefault();
      dropzone.classList.remove("dragover");
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFile(e.dataTransfer.files[0]);
      }
    });
    fileInput.addEventListener("change", () => {
      if (fileInput.files.length) handleFile(fileInput.files[0]);
    });
  </script>

</body>

</html>