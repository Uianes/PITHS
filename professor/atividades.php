<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('PROF');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$matricula = (int)($_SESSION['matricula'] ?? 0);

// Turmas do professor
$turmas = $mysqli->prepare('SELECT t.ID_TURMA, t.NOME_TURMA FROM PROF_TURMA pt JOIN TURMA t ON t.ID_TURMA=pt.ID_TURMA WHERE pt.MATRICULA=?');
$turmas->bind_param('i', $matricula);
$turmas->execute();
$turmasRes = $turmas->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_turma = (int)($_POST['id_turma'] ?? 0);
  $path = trim($_POST['path_atividade'] ?? '');

  if ($id_turma > 0 && $path !== '') {
    // cria atividade (como o schema não tem AUTO_INCREMENT, vamos gerar próximo ID)
    $next = (int)$mysqli->query('SELECT COALESCE(MAX(ID_ATIVIDADE),0) m FROM ATIVIDADE')->fetch_assoc()['m'] + 1;

    $insA = $mysqli->prepare('INSERT INTO ATIVIDADE (ID_ATIVIDADE, PATH_ATIVIDADE) VALUES (?, ?)');
    $insA->bind_param('is', $next, $path);
    $insA->execute();

    $insT = $mysqli->prepare('INSERT INTO ATIVIDADE_TURMA (ID_TURMA, ID_ATIVIDADE) VALUES (?, ?)');
    $insT->bind_param('ii', $id_turma, $next);
    $insT->execute();
  }

  header('Location: /professor/atividades.php');
  exit;
}

// Atividades já cadastradas
$ativ = $mysqli->query('SELECT ID_ATIVIDADE, PATH_ATIVIDADE FROM ATIVIDADE ORDER BY ID_ATIVIDADE DESC LIMIT 50');

piths_head('Atividades - Professor');
piths_navbar();
?>
<div class="container py-4">
  <div class="piths-glass p-4">
    <h2 class="brand mb-3">Selecionar / adicionar atividades 📚</h2>

    <div class="alert alert-info">
      Aqui você informa o <strong>PATH</strong> de um arquivo <strong>.php</strong> (atividade) que você vai colocar no projeto.
      O PITHS salva esse caminho na tabela <code>ATIVIDADE</code> e vincula na <code>ATIVIDADE_TURMA</code>.
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
        <label class="form-label">Path da atividade (ex.: /atividades/soma1.php)</label>
        <input class="form-control" name="path_atividade" placeholder="/atividades/minha_atividade.php" required>
      </div>
      <div class="col-12">
        <button class="btn btn-success btn-fun btn-lg" type="submit">Adicionar atividade para a turma</button>
        <a class="btn btn-outline-dark btn-fun btn-lg" href="/professor/index.php">Voltar</a>
      </div>
    </form>

    <hr>

    <h5 class="mb-2">Últimas atividades cadastradas</h5>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead><tr><th>ID</th><th>PATH</th></tr></thead>
        <tbody>
          <?php while($a = $ativ->fetch_assoc()): ?>
            <tr><td><?= (int)$a['ID_ATIVIDADE'] ?></td><td><code><?= htmlspecialchars($a['PATH_ATIVIDADE']) ?></code></td></tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<?php piths_footer(); ?>
