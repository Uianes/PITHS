<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';

$tipo = $_SESSION['tipo'] ?? '';
$id_turma = (int)($_SESSION['id_turma'] ?? 0);

$id_atividade = (int)($_GET['id_atividade'] ?? 0);
$pathParam = trim((string)($_GET['path'] ?? ''));
$step = (string)($_GET['step'] ?? 'video');
$step = in_array($step, ['video', 'texto', 'quiz'], true) ? $step : 'video';

$projectRoot = realpath(__DIR__ . '/..');
$baseDir = realpath(__DIR__ . '/../atividades');

$path = '';
if ($id_atividade > 0) {
  $stmt = $mysqli->prepare('SELECT PATH_ATIVIDADE FROM ATIVIDADE WHERE ID_ATIVIDADE=?');
  $stmt->bind_param('i', $id_atividade);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $path = $row['PATH_ATIVIDADE'] ?? '';

  if ($tipo === 'ALUNO') {
    $check = $mysqli->prepare('SELECT 1 FROM ATIVIDADE_TURMA WHERE ID_TURMA=? AND ID_ATIVIDADE=?');
    $check->bind_param('ii', $id_turma, $id_atividade);
    $check->execute();
    $checkRes = $check->get_result();
    if ($checkRes->num_rows === 0) {
      http_response_code(403);
      echo 'Acesso negado.';
      exit;
    }
  }
} elseif ($pathParam !== '') {
  $path = $pathParam;
}

$path = ltrim($path, '/');
if ($path === '' || !$projectRoot || !$baseDir) {
  http_response_code(404);
  echo 'Atividade não encontrada.';
  exit;
}

$abs = realpath($projectRoot . '/' . $path);
if (!$abs || strpos($abs, $baseDir) !== 0 || !is_dir($abs)) {
  http_response_code(404);
  echo 'Atividade não encontrada.';
  exit;
}

$fileMap = [
  'video' => 'video.html',
  'texto' => 'texto.html',
  'quiz' => 'quiz.html',
];

$fileName = $fileMap[$step] ?? 'video.html';
$absFile = $abs . '/' . $fileName;
$hasFile = is_file($absFile);


$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$basePath = $projectRoot ? rtrim(str_replace($docRoot, '', $projectRoot), '/') : '';
$publicBase = $basePath . '/' . $path;

if ($hasFile) {
  $suffix = '';
  if ($id_atividade > 0) {
    $suffix = '?id_atividade=' . $id_atividade . '&step=' . urlencode($step);
  }
  header('Location: ' . $publicBase . '/' . $fileName . $suffix);
  exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Atividade não encontrada</title>
  <style>
    body{font-family:Arial, sans-serif;margin:24px;background:#fff;color:#111;}
    .box{border:1px solid #ccc;border-radius:12px;padding:16px;max-width:600px;}
  </style>
</head>
<body>
  <div class="box">
    <strong>Arquivo não encontrado:</strong> <code><?= htmlspecialchars($fileName) ?></code>
  </div>
</body>
</html>
