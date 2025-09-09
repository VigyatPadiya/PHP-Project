<?php
session_start();
require 'config.php';

$job = $_GET['job'] ?? '';
$file = $_GET['file'] ?? '';
$path = realpath(dirname(__DIR__) . "/storage/jobs/$job/$file");

if (!$job || !$file || !$path || !file_exists($path)) {
  http_response_code(404);
  exit("File not found.");
}

$size = round(filesize($path) / 1024, 2); // KB
$ext  = pathinfo($path, PATHINFO_EXTENSION);

// ✅ Save file conversion history if user is logged in
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  $sql = "INSERT INTO file_history (user_id, file_name) VALUES (?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $user_id, $file);
  $stmt->execute();
}
// ✅ Update admin statistics
$sql = "UPDATE admin_stats SET 
        total_conversions = total_conversions + 1,
        total_files = total_files + 1 
        WHERE id = 1";
$conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Your File is Ready - Flexi Converter</title>
  <script src="https://cdn.tailwindcss.com"></script>
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

<body class="min-h-screen flex items-center justify-center p-6">

  <div class="max-w-lg w-full glass-card rounded-2xl shadow-xl p-8 text-center">
    <h1 class="text-2xl font-bold text-sky-400 mb-4"> Conversion Successful!</h1>
    <p class="text-sm text-slate-300 mb-6">Your file is ready to download.</p>

    <!-- File Info -->
    <div class="bg-slate-800 rounded-xl p-4 mb-6 text-left">
      <p><span class="font-semibold text-sky-300"> File:</span> <?= htmlspecialchars($file) ?></p>
      <p><span class="font-semibold text-sky-300"> Type:</span> <?= strtoupper($ext) ?></p>
      <p><span class="font-semibold text-sky-300"> Size:</span> <?= $size ?> KB</p>
    </div>

    <!-- Download Button -->
    <a href="../storage/jobs/<?= urlencode($job) ?>/<?= urlencode($file) ?>"
      download
      class="block w-full py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold shadow-lg transition mb-4">
      ⬇ Download File
    </a>

    <!-- Convert Another -->
    <a href="index.php" class="text-sky-400 hover:text-sky-300 text-sm"> Convert another file</a>

    <!-- Show history link only if logged in -->
    <?php if (isset($_SESSION['user_id'])): ?>
      <br><a href="history.php" class="text-sky-400 hover:text-sky-300 text-sm"> View My History</a>
    <?php endif; ?>
  </div>

</body>

</html>