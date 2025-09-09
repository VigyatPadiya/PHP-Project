<?php
// public/convert.php
declare(strict_types=1);

function respond_error(string $msg): void {
  http_response_code(400);
  echo "<!doctype html><meta charset='utf-8'><link rel='stylesheet' href='https://cdn.tailwindcss.com'><div class='p-8 max-w-xl mx-auto'>";
  echo "<h1 class='text-xl font-bold mb-3'>Conversion failed</h1><p class='text-gray-600 mb-4'>".htmlspecialchars($msg)."</p>";
  echo "<a class='text-blue-600 hover:underline' href='index.php'>&larr; Back</a></div>";
  exit;
}

$maxSize = 50 * 1024 * 1024; // 50 MB
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
  respond_error("No file uploaded or upload error.");
}
if ($_FILES['file']['size'] > $maxSize) {
  respond_error("File exceeds the 50 MB limit.");
}

$target = $_POST['target'] ?? 'pdf';
$allowedTargets = ['pdf','docx','odt','rtf','txt','html','xlsx','ods','csv','pptx','odp','png','jpg'];
if (!in_array($target, $allowedTargets, true)) {
  respond_error("Unsupported target format.");
}

$uploadsBase = dirname(__DIR__) . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "jobs";
if (!is_dir($uploadsBase)) { mkdir($uploadsBase, 0775, true); }

$job = bin2hex(random_bytes(8));
$jobDir = $uploadsBase . DIRECTORY_SEPARATOR . $job;
mkdir($jobDir, 0775, true);

$originalName = $_FILES['file']['name'];
$tmpName = $_FILES['file']['tmp_name'];
$srcPath = $jobDir . DIRECTORY_SEPARATOR . $originalName;
if (!move_uploaded_file($tmpName, $srcPath)) {
  respond_error("Failed to move uploaded file.");
}

// Determine LibreOffice binary path (adjust if needed)
$soffice = trim((string) shell_exec('command -v soffice'));
if ($soffice === '') {
  // Try common locations
  $candidates = [
    '/usr/bin/soffice',
    '/usr/local/bin/soffice',
    'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
    'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe'
  ];
  foreach ($candidates as $c) {
    if (file_exists($c)) { $soffice = $c; break; }
  }
}
if ($soffice === '') {
  respond_error("LibreOffice (soffice) was not found on this server. Please install LibreOffice and ensure 'soffice' is in PATH.");
}

// Map target to LibreOffice filter (keep generic where possible)
$filterMap = [
  'pdf'  => 'pdf',
  'docx' => 'docx',
  'odt'  => 'odt',
  'rtf'  => 'rtf',
  'txt'  => 'txt:Text',
  'html' => 'html:XHTML Writer File',
  'xlsx' => 'xlsx',
  'ods'  => 'ods',
  'csv'  => 'csv:Text - txt - csv (StarCalc)',
  'pptx' => 'pptx',
  'odp'  => 'odp',
  'png'  => 'png',
  'jpg'  => 'jpg'
];

$filter = $filterMap[$target] ?? $target;

// Build command
$outDir = $jobDir;
$cmd = escapeshellarg($soffice) . " --headless --nologo --nolockcheck --nodefault --norestore " .
       "--convert-to " . escapeshellarg($filter) . " --outdir " . escapeshellarg($outDir) . " " . escapeshellarg($srcPath) . " 2>&1";

exec($cmd, $outputLines, $exitCode);

// Try to locate the output file (same basename with target extension)
$base = pathinfo($originalName, PATHINFO_FILENAME);
$outPath = $jobDir . DIRECTORY_SEPARATOR . $base . "." . $target;

// Some filters may pick slightly different extension case; normalize search
if (!file_exists($outPath)) {
  $files = glob($jobDir . DIRECTORY_SEPARATOR . $base . ".*");
  if ($files) {
    foreach ($files as $f) {
      $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
      if ($ext === strtolower($target)) { $outPath = $f; break; }
    }
  }
}

if (!file_exists($outPath) || $exitCode !== 0) {
  $details = htmlspecialchars(implode("\n", $outputLines));
  respond_error("LibreOffice failed to convert the file to .$target. Details:<pre class='bg-gray-50 p-3 rounded mt-2 text-xs overflow-auto'>$details</pre>");
}

// Render result page with download button
$sizeKB = number_format(filesize($outPath) / 1024, 1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Converted • Flexi File Converter</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
  <main class="max-w-xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
      <h1 class="text-xl font-bold mb-2">Your file is ready 🎉</h1>
      <p class="text-sm text-gray-600 mb-4">
        Converted <span class="font-medium"><?=htmlspecialchars($originalName)?></span> to <span class="font-medium">.<?=$target?></span>.
      </p>
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-5">
        <div class="text-sm text-gray-700">
          <div class="font-medium"><?=htmlspecialchars($base . "." . $target)?></div>
          <div class="text-xs text-gray-500"><?=$sizeKB?> KB</div>
        </div>
        <a class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow"
           href="<?= 'download.php?job=' . urlencode($job) . '&file=' . urlencode(basename($outPath)) ?>">
           Download
        </a>
      </div>
      <a href="index.php" class="text-blue-600 hover:underline text-sm">&larr; Convert another file</a>
    </div>
  </main>
</body>
</html>
