<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('PROF');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$matricula = (int)($_SESSION['matricula'] ?? 0);
$id_turma = (int)($_GET['id'] ?? 0);

if ($id_turma <= 0) {
  header('Location: index.php');
  exit;
}

$turmaStmt = $mysqli->prepare('SELECT t.ID_TURMA, t.NOME_TURMA, t.SERIE, t.ANO FROM PROF_TURMA pt JOIN TURMA t ON t.ID_TURMA=pt.ID_TURMA WHERE pt.MATRICULA=? AND t.ID_TURMA=?');
$turmaStmt->bind_param('ii', $matricula, $id_turma);
$turmaStmt->execute();
$turmaRes = $turmaStmt->get_result();
$turma = $turmaRes->fetch_assoc();

if (!$turma) {
  http_response_code(403);
  echo 'Acesso negado.';
  exit;
}

$projectRoot = realpath(__DIR__ . '/..');
$baseDir = realpath(__DIR__ . '/../atividades');

function normalize_activity_path(string $path, ?string $projectRoot, ?string $baseDir): ?string {
  $clean = ltrim(trim($path), '/');
  if ($clean === '' || !$projectRoot || !$baseDir) {
    return null;
  }
  $abs = realpath($projectRoot . '/' . $clean);
  if (!$abs || strpos($abs, $baseDir) !== 0 || !is_dir($abs)) {
    return null;
  }
  return $clean;
}

function format_label(string $name): string {
  $label = str_replace('_', ' ', $name);
  $label = preg_replace('/\s+/', ' ', $label) ?: $label;
  return ucwords($label);
}

$message = null;
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['path'], $_POST['action'])) {
  $path = normalize_activity_path((string)$_POST['path'], $projectRoot, $baseDir);
  $action = (string)$_POST['action'];

  if (!$path) {
    $message = 'Atividade inválida.';
    $messageType = 'danger';
  } elseif ($action === 'remove') {
    $find = $mysqli->prepare('SELECT ID_ATIVIDADE FROM ATIVIDADE WHERE PATH_ATIVIDADE=?');
    $find->bind_param('s', $path);
    $find->execute();
    $findRes = $find->get_result();
    $row = $findRes->fetch_assoc();
    $id_atividade = (int)($row['ID_ATIVIDADE'] ?? 0);

    if ($id_atividade > 0) {
      $del = $mysqli->prepare('DELETE FROM ATIVIDADE_TURMA WHERE ID_TURMA=? AND ID_ATIVIDADE=?');
      $del->bind_param('ii', $id_turma, $id_atividade);
      $del->execute();

      if ($del->affected_rows === 0) {
        $message = 'Atividade não estava adicionada nesta turma.';
        $messageType = 'info';
      } else {
        $message = 'Atividade removida com sucesso.';
        $messageType = 'success';
      }
    } else {
      $message = 'Atividade não encontrada.';
      $messageType = 'danger';
    }
  } else {
    $find = $mysqli->prepare('SELECT ID_ATIVIDADE FROM ATIVIDADE WHERE PATH_ATIVIDADE=?');
    $find->bind_param('s', $path);
    $find->execute();
    $findRes = $find->get_result();
    $id_atividade = 0;

    if ($row = $findRes->fetch_assoc()) {
      $id_atividade = (int)$row['ID_ATIVIDADE'];
    } else {
      $ins = $mysqli->prepare('INSERT INTO ATIVIDADE (PATH_ATIVIDADE) VALUES (?)');
      $ins->bind_param('s', $path);
      if ($ins->execute()) {
        $id_atividade = (int)$ins->insert_id;
      }
    }

    if ($id_atividade > 0) {
      $add = $mysqli->prepare('INSERT IGNORE INTO ATIVIDADE_TURMA (ID_TURMA, ID_ATIVIDADE) VALUES (?, ?)');
      $add->bind_param('ii', $id_turma, $id_atividade);
      $add->execute();

      if ($add->affected_rows === 0) {
        $message = 'Atividade já estava adicionada nesta turma.';
        $messageType = 'info';
      } else {
        $message = 'Atividade adicionada com sucesso.';
        $messageType = 'success';
      }
    } else {
      $message = 'Não foi possível salvar a atividade.';
      $messageType = 'danger';
    }
  }
}

$addedPaths = [];
$addedStmt = $mysqli->prepare('SELECT a.PATH_ATIVIDADE FROM ATIVIDADE_TURMA atx JOIN ATIVIDADE a ON a.ID_ATIVIDADE=atx.ID_ATIVIDADE WHERE atx.ID_TURMA=?');
$addedStmt->bind_param('i', $id_turma);
$addedStmt->execute();
$addedRes = $addedStmt->get_result();
while ($row = $addedRes->fetch_assoc()) {
  $addedPaths[$row['PATH_ATIVIDADE']] = true;
}

$activityDirs = [];
if ($baseDir) {
  $activityDirs = glob($baseDir . '/*/*', GLOB_ONLYDIR) ?: [];
  natcasesort($activityDirs);
}

$alunos = $mysqli->query("SELECT u.MATRICULA, u.NOME, g.TOTAL_XP, g.LEVEL, g.TITLE
                           FROM USERS u
                           LEFT JOIN GAMIFICACAO g ON g.MATRICULA=u.MATRICULA
                           WHERE u.TIPO='ALUNO' AND u.ACTIVE=1 AND u.ID_TURMA={$id_turma}
                           ORDER BY u.NOME");

piths_head('Turma - PITHS');
piths_navbar();
?>
<style>
.turma-card{
  background: #d9b3ff;
  border-radius: 22px;
  min-height: 160px;
  box-shadow: 0 12px 28px rgba(0,0,0,.18);
}
.turma-card .btn{
  border-radius: 999px;
  font-weight: 700;
}
.activity-card{
  background: #d9b3ff;
  border-radius: 24px;
  min-height: 200px;
  box-shadow: 0 12px 28px rgba(0,0,0,.18);
}
.activity-card .btn{
  border-radius: 999px;
  font-weight: 700;
}
</style>

<div class="container py-4">
  <div class="piths-glass p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <div>
        <h2 class="brand mb-1">Turma <?= htmlspecialchars($turma['NOME_TURMA']) ?></h2>
        <div class="text-muted">Série: <?= htmlspecialchars($turma['SERIE']) ?> • Ano: <?= (int)$turma['ANO'] ?></div>
      </div>
      <a class="btn btn-outline-dark btn-fun" href="index.php">← Voltar</a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="mb-4">
      <h5 class="mb-3">Alunos da turma</h5>
      <?php if ($alunos->num_rows === 0): ?>
        <div class="text-muted">Nenhum aluno ativo nessa turma.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Matrícula</th>
                <th>XP</th>
                <th>Nível</th>
              </tr>
            </thead>
            <tbody>
              <?php while($a = $alunos->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($a['NOME']) ?></td>
                  <td><?= (int)$a['MATRICULA'] ?></td>
                  <td><?= (int)($a['TOTAL_XP'] ?? 0) ?></td>
                  <td><?= (int)($a['LEVEL'] ?? 1) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="mb-2">
      <h4 class="mb-3">Selecionar / adicionar atividades 📚</h4>
    </div>

    <?php if (empty($activityDirs)): ?>
      <div class="alert alert-warning">Nenhuma atividade encontrada em <code>atividades/*/*</code>.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($activityDirs as $dir): ?>
          <?php
            $rel = $projectRoot ? ltrim(str_replace($projectRoot . '/', '', $dir), '/') : '';
            $eixo = format_label(basename(dirname($dir)));
            $atividadeNome = format_label(basename($dir));
            $hasVideo = is_file($dir . '/video.html');
            $hasTexto = is_file($dir . '/texto.html');
            $hasQuiz = is_file($dir . '/quiz.html');
            $objetivoPath = $dir . '/objetivo.html';
            $objetivoHtml = is_file($objetivoPath) ? file_get_contents($objetivoPath) : '';
            $missing = [];
            if (!$hasVideo) $missing[] = 'video.html';
            if (!$hasTexto) $missing[] = 'texto.html';
            if (!$hasQuiz) $missing[] = 'quiz.html';
            $alreadyAdded = isset($addedPaths[$rel]);
            $previewUrl = '../runner/atividade.php?path=' . urlencode($rel) . '&step=video';
          ?>
          <div class="col-12 col-md-6">
            <div class="activity-card p-3 h-100 d-flex flex-column">
              <div class="text-center">
                <div class="fw-bold">Eixo: <?= htmlspecialchars($eixo) ?></div>
                <div class="fw-bold">Atividade: <?= htmlspecialchars($atividadeNome) ?></div>
              </div>

              <?php if ($objetivoHtml !== ''): ?>
                <div class="mt-2 text-center small">
                  <?= $objetivoHtml ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($missing)): ?>
                <div class="text-center text-muted small mt-2">Faltando: <?= htmlspecialchars(implode(', ', $missing)) ?></div>
              <?php endif; ?>

              <div class="mt-auto d-flex justify-content-center gap-3">
                <a class="btn btn-outline-dark" href="<?= htmlspecialchars($previewUrl) ?>" target="_blank" rel="noopener">Ver</a>
                <form method="post" class="mb-0">
                  <input type="hidden" name="path" value="<?= htmlspecialchars($rel) ?>">
                  <?php if ($alreadyAdded): ?>
                    <input type="hidden" name="action" value="remove">
                    <button class="btn btn-outline-dark" type="submit">Remover</button>
                  <?php else: ?>
                    <input type="hidden" name="action" value="add">
                    <button class="btn btn-dark" type="submit">Adicionar</button>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php piths_footer(); ?>
