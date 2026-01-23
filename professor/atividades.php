<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('PROF');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$matricula = (int)($_SESSION['matricula'] ?? 0);

// Turmas do professor
$turmas = $mysqli->prepare('
  SELECT t.ID_TURMA, t.NOME_TURMA
  FROM PROF_TURMA pt
  JOIN TURMA t ON t.ID_TURMA=pt.ID_TURMA
  WHERE pt.MATRICULA=?
  ORDER BY t.ID_TURMA DESC
');
$turmas->bind_param('i', $matricula);
$turmas->execute();
$turmasRes = $turmas->get_result();

// Catálogo de atividades
$cat = $mysqli->query('SELECT ID_ATIVIDADE, PATH_ATIVIDADE FROM ATIVIDADE ORDER BY ID_ATIVIDADE DESC');

// Processa POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_turma = (int)($_POST['id_turma'] ?? 0);
  $id_atividade_existente = (int)($_POST['id_atividade'] ?? 0);
  $novo_path = trim($_POST['novo_path_atividade'] ?? '');

  // valida turma pertence ao professor
  $chk = $mysqli->prepare('SELECT 1 FROM PROF_TURMA WHERE MATRICULA=? AND ID_TURMA=? LIMIT 1');
  $chk->bind_param('ii', $matricula, $id_turma);
  $chk->execute();
  $okTurma = $chk->get_result()->fetch_assoc();

  if (!$okTurma) {
    header('Location: /professor/atividades.php?err=' . urlencode('Turma inválida para este professor.'));
    exit;
  }

  // Se preencher novo path, cria no catálogo
  if ($novo_path !== '') {
    // normaliza: remove espaços e garante que não seja vazio
    $novo_path = trim($novo_path);

    // evita duplicar mesma atividade no catálogo
    $exists = $mysqli->prepare('SELECT ID_ATIVIDADE FROM ATIVIDADE WHERE PATH_ATIVIDADE=? LIMIT 1');
    $exists->bind_param('s', $novo_path);
    $exists->execute();
    $found = $exists->get_result()->fetch_assoc();

    if ($found) {
      $id_atividade = (int)$found['ID_ATIVIDADE'];
    } else {
      $insA = $mysqli->prepare('INSERT INTO ATIVIDADE (PATH_ATIVIDADE) VALUES (?)');
      $insA->bind_param('s', $novo_path);
      if (!$insA->execute()) {
        header('Location: /professor/atividades.php?err=' . urlencode('Erro ao criar atividade: ' . $insA->error));
        exit;
      }
      $id_atividade = (int)$mysqli->insert_id;
    }

  } else {
    // Se não criou nova, usa a existente selecionada
    $id_atividade = $id_atividade_existente;
  }

  if ($id_turma <= 0 || $id_atividade <= 0) {
    header('Location: /professor/atividades.php?err=' . urlencode('Selecione uma turma e uma atividade (ou informe um novo path).'));
    exit;
  }

  // vincula turma ↔ atividade
  // Se você criou a UNIQUE uq_turma_atividade, dá pra usar INSERT IGNORE.
  // Para máxima compatibilidade, fazemos checagem.
  $v = $mysqli->prepare('SELECT 1 FROM ATIVIDADE_TURMA WHERE ID_TURMA=? AND ID_ATIVIDADE=? LIMIT 1');
  $v->bind_param('ii', $id_turma, $id_atividade);
  $v->execute();
  $ja = $v->get_result()->fetch_assoc();

  if (!$ja) {
    $insT = $mysqli->prepare('INSERT INTO ATIVIDADE_TURMA (ID_TURMA, ID_ATIVIDADE) VALUES (?, ?)');
    $insT->bind_param('ii', $id_turma, $id_atividade);
    if (!$insT->execute()) {
      header('Location: /professor/atividades.php?err=' . urlencode('Erro ao vincular atividade à turma: ' . $insT->error));
      exit;
    }
  }

  header('Location: /professor/atividades.php?ok=' . urlencode('Atividade vinculada à turma com sucesso.'));
  exit;
}

// Últimos vínculos (pra professor visualizar rapidamente)
$ult = $mysqli->prepare('
  SELECT t.NOME_TURMA, a.ID_ATIVIDADE, a.PATH_ATIVIDADE
  FROM PROF_TURMA pt
  JOIN TURMA t ON t.ID_TURMA=pt.ID_TURMA
  JOIN ATIVIDADE_TURMA atx ON atx.ID_TURMA=t.ID_TURMA
  JOIN ATIVIDADE a ON a.ID_ATIVIDADE=atx.ID_ATIVIDADE
  WHERE pt.MATRICULA=?
  ORDER BY a.ID_ATIVIDADE DESC
  LIMIT 50
');
$ult->bind_param('i', $matricula);
$ult->execute();
$ultRes = $ult->get_result();

piths_head('Atividades - Professor');
piths_navbar();
?>
<div class="container py-4">
  <div class="piths-glass p-4">
    <h2 class="brand mb-3">Selecionar / adicionar atividades 📚</h2>

    <?php if (!empty($_GET['err'])): ?>
      <div class="alert alert-danger"><strong><?= htmlspecialchars($_GET['err']) ?></strong></div>
    <?php endif; ?>
    <?php if (!empty($_GET['ok'])): ?>
      <div class="alert alert-success"><strong><?= htmlspecialchars($_GET['ok']) ?></strong></div>
    <?php endif; ?>

    <div class="alert alert-info">
      <div class="fw-bold mb-1">Como funciona</div>
      <div class="small">
        • Você pode <b>selecionar uma atividade existente</b> e vincular à turma.<br>
        • Ou pode <b>cadastrar um novo path</b> (uma nova atividade) e já vincular à turma.
      </div>
    </div>

    <form method="post" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Turma</label>
        <select class="form-select" name="id_turma" required>
          <option value="">Selecione…</option>
          <?php while($t = $turmasRes->fetch_assoc()): ?>
            <option value="<?= (int)$t['ID_TURMA'] ?>">#<?= (int)$t['ID_TURMA'] ?> — <?= htmlspecialchars($t['NOME_TURMA']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-8">
        <label class="form-label">Atividade existente</label>
        <select class="form-select" name="id_atividade">
          <option value="0">Selecione uma atividade do catálogo…</option>
          <?php while($a = $cat->fetch_assoc()): ?>
            <option value="<?= (int)$a['ID_ATIVIDADE'] ?>">
              #<?= (int)$a['ID_ATIVIDADE'] ?> — <?= htmlspecialchars($a['PATH_ATIVIDADE']) ?>
            </option>
          <?php endwhile; ?>
        </select>
        <div class="form-text">Ou cadastre um novo path abaixo (opcional).</div>
      </div>

      <div class="col-12">
        <label class="form-label">Novo path de atividade (opcional)</label>
        <input class="form-control" name="novo_path_atividade" placeholder="/atividades/tipos_numeros">
        <div class="form-text">
          Se preencher, o sistema cria essa atividade no catálogo e vincula à turma.
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-success btn-fun btn-lg" type="submit">Vincular atividade à turma</button>
        <a class="btn btn-outline-dark btn-fun btn-lg" href="/professor/index.php">Voltar</a>
      </div>
    </form>

    <hr>

    <h5 class="mb-2">Vínculos recentes (suas turmas)</h5>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead><tr><th>Turma</th><th>ID</th><th>Path</th></tr></thead>
        <tbody>
          <?php while($r = $ultRes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($r['NOME_TURMA']) ?></td>
              <td><?= (int)$r['ID_ATIVIDADE'] ?></td>
              <td><code><?= htmlspecialchars($r['PATH_ATIVIDADE']) ?></code></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<?php piths_footer(); ?>