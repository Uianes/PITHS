<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('ADM');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

function admin_redirect(string $param, string $message): void {
  $qs = http_build_query([$param => $message]);
  header("Location: index.php?{$qs}");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'add_turma') {
    $nome = trim($_POST['nome_turma'] ?? '');
    $serie = trim($_POST['serie'] ?? '');
    $ano = isset($_POST['ano']) ? (int)$_POST['ano'] : 0;
    $idEscola = isset($_POST['id_escola']) ? (int)$_POST['id_escola'] : 0;

    if ($nome === '' || $serie === '' || $ano <= 0 || $idEscola <= 0) {
      admin_redirect('err', 'Preencha todos os campos da turma.');
    }

    $chk = $mysqli->prepare('SELECT COUNT(*) c FROM ESCOLA WHERE ID_ESCOLA=?');
    $chk->bind_param('i', $idEscola);
    $chk->execute();
    $exists = (int)$chk->get_result()->fetch_assoc()['c'];
    if ($exists === 0) {
      admin_redirect('err', 'Escola inv\u00e1lida.');
    }

    $ins = $mysqli->prepare('INSERT INTO TURMA (ANO, NOME_TURMA, SERIE, ID_ESCOLA) VALUES (?,?,?,?)');
    $ins->bind_param('issi', $ano, $nome, $serie, $idEscola);
    $ins->execute();
    admin_redirect('ok', 'Turma adicionada.');
  }

  if ($action === 'update_turma') {
    $idTurma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;
    $nome = trim($_POST['nome_turma'] ?? '');
    $serie = trim($_POST['serie'] ?? '');
    $ano = isset($_POST['ano']) ? (int)$_POST['ano'] : 0;
    $idEscola = isset($_POST['id_escola']) ? (int)$_POST['id_escola'] : 0;

    if ($idTurma <= 0 || $nome === '' || $serie === '' || $ano <= 0 || $idEscola <= 0) {
      admin_redirect('err', 'Dados da turma inv\u00e1lidos.');
    }

    $chk = $mysqli->prepare('SELECT COUNT(*) c FROM ESCOLA WHERE ID_ESCOLA=?');
    $chk->bind_param('i', $idEscola);
    $chk->execute();
    $exists = (int)$chk->get_result()->fetch_assoc()['c'];
    if ($exists === 0) {
      admin_redirect('err', 'Escola inv\u00e1lida.');
    }

    $up = $mysqli->prepare('UPDATE TURMA SET ANO=?, NOME_TURMA=?, SERIE=?, ID_ESCOLA=? WHERE ID_TURMA=?');
    $up->bind_param('issii', $ano, $nome, $serie, $idEscola, $idTurma);
    $up->execute();
    admin_redirect('ok', 'Turma atualizada.');
  }

  if ($action === 'delete_turma') {
    $idTurma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;
    if ($idTurma <= 0) {
      admin_redirect('err', 'Turma inv\u00e1lida.');
    }

    $deps = [
      'USERS' => 'SELECT COUNT(*) c FROM USERS WHERE ID_TURMA=?',
      'PROF_TURMA' => 'SELECT COUNT(*) c FROM PROF_TURMA WHERE ID_TURMA=?',
      'ATIVIDADE_TURMA' => 'SELECT COUNT(*) c FROM ATIVIDADE_TURMA WHERE ID_TURMA=?',
    ];
    foreach ($deps as $table => $sql) {
      $chk = $mysqli->prepare($sql);
      $chk->bind_param('i', $idTurma);
      $chk->execute();
      $count = (int)$chk->get_result()->fetch_assoc()['c'];
      if ($count > 0) {
        admin_redirect('err', "N\u00e3o foi poss\u00edvel excluir: turma vinculada em {$table}.");
      }
    }

    $del = $mysqli->prepare('DELETE FROM TURMA WHERE ID_TURMA=?');
    $del->bind_param('i', $idTurma);
    $del->execute();
    admin_redirect('ok', 'Turma exclu\u00edda.');
  }

  if ($action === 'update_aluno') {
    $matricula = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
    $nome = trim($_POST['nome'] ?? '');
    $idEscola = isset($_POST['id_escola']) ? (int)$_POST['id_escola'] : 0;
    $idTurma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;

    if ($matricula <= 0 || $nome === '' || $idEscola <= 0 || $idTurma <= 0) {
      admin_redirect('err', 'Dados do aluno inv\u00e1lidos.');
    }

    $chk = $mysqli->prepare('SELECT ID_ESCOLA FROM TURMA WHERE ID_TURMA=?');
    $chk->bind_param('i', $idTurma);
    $chk->execute();
    $row = $chk->get_result()->fetch_assoc();
    if (!$row || (int)$row['ID_ESCOLA'] !== $idEscola) {
      admin_redirect('err', 'A turma n\u00e3o pertence \u00e0 escola selecionada.');
    }

    $up = $mysqli->prepare("UPDATE USERS SET NOME=?, ID_ESCOLA=?, ID_TURMA=? WHERE MATRICULA=? AND TIPO='ALUNO'");
    $up->bind_param('siii', $nome, $idEscola, $idTurma, $matricula);
    $up->execute();
    admin_redirect('ok', 'Aluno atualizado.');
  }

  if ($action === 'add_prof_turma') {
    $matricula = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
    $idTurma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;

    if ($matricula <= 0 || $idTurma <= 0) {
      admin_redirect('err', 'Dados do professor/turma inválidos.');
    }

    $chkProf = $mysqli->prepare("SELECT COUNT(*) c FROM USERS WHERE MATRICULA=? AND TIPO='PROF' AND ACTIVE=1");
    $chkProf->bind_param('i', $matricula);
    $chkProf->execute();
    $profExists = (int)$chkProf->get_result()->fetch_assoc()['c'];
    if ($profExists === 0) {
      admin_redirect('err', 'Professor inválido.');
    }

    $chkTurma = $mysqli->prepare('SELECT COUNT(*) c FROM TURMA WHERE ID_TURMA=?');
    $chkTurma->bind_param('i', $idTurma);
    $chkTurma->execute();
    $turmaExists = (int)$chkTurma->get_result()->fetch_assoc()['c'];
    if ($turmaExists === 0) {
      admin_redirect('err', 'Turma inválida.');
    }

    $ins = $mysqli->prepare('INSERT IGNORE INTO PROF_TURMA (ID_TURMA, MATRICULA) VALUES (?, ?)');
    $ins->bind_param('ii', $idTurma, $matricula);
    $ins->execute();
    if ($ins->affected_rows === 0) {
      admin_redirect('ok', 'Professor já estava vinculado a essa turma.');
    }
    admin_redirect('ok', 'Professor vinculado à turma.');
  }

  if ($action === 'remove_prof_turma') {
    $matricula = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
    $idTurma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;

    if ($matricula <= 0 || $idTurma <= 0) {
      admin_redirect('err', 'Dados do vínculo inválidos.');
    }

    $del = $mysqli->prepare('DELETE FROM PROF_TURMA WHERE ID_TURMA=? AND MATRICULA=?');
    $del->bind_param('ii', $idTurma, $matricula);
    $del->execute();
    admin_redirect('ok', 'Vínculo removido.');
  }

  if ($action === 'delete_aluno') {
    $matricula = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
    if ($matricula <= 0) {
      admin_redirect('err', 'Aluno inv\u00e1lido.');
    }

    $mysqli->begin_transaction();
    $ok = true;

    $delStatus = $mysqli->prepare('DELETE FROM ATIVIDADE_STATUS WHERE MATRICULA=?');
    $delStatus->bind_param('i', $matricula);
    $ok = $ok && $delStatus->execute();

    $delGami = $mysqli->prepare('DELETE FROM GAMIFICACAO WHERE MATRICULA=?');
    $delGami->bind_param('i', $matricula);
    $ok = $ok && $delGami->execute();

    $delUser = $mysqli->prepare("DELETE FROM USERS WHERE MATRICULA=? AND TIPO='ALUNO'");
    $delUser->bind_param('i', $matricula);
    $ok = $ok && $delUser->execute();

    if ($ok) {
      $mysqli->commit();
    } else {
      $mysqli->rollback();
      admin_redirect('err', 'Falha ao excluir aluno.');
    }
    admin_redirect('ok', 'Aluno exclu\u00eddo.');
  }
}

$flashErr = $_GET['err'] ?? '';
$flashOk = $_GET['ok'] ?? '';

$k_pendentes = (int)$mysqli->query("SELECT COUNT(*) c FROM USERS WHERE ACTIVE = 0")->fetch_assoc()['c'];
$k_turmas = (int)$mysqli->query("SELECT COUNT(*) c FROM TURMA")->fetch_assoc()['c'];
$k_status = (int)$mysqli->query("SELECT COUNT(*) c FROM ATIVIDADE_STATUS")->fetch_assoc()['c'];
$k_alunos = (int)$mysqli->query("SELECT COUNT(*) c FROM USERS WHERE TIPO='ALUNO' AND ACTIVE=1")->fetch_assoc()['c'];

$topxp = $mysqli->query("SELECT u.NOME, g.TOTAL_XP, g.LEVEL, g.TITLE FROM GAMIFICACAO g JOIN USERS u ON u.MATRICULA=g.MATRICULA WHERE u.ACTIVE=1 ORDER BY g.TOTAL_XP DESC LIMIT 10");
$escolas = $mysqli->query("SELECT ID_ESCOLA, NOME_ESCOLA FROM ESCOLA ORDER BY NOME_ESCOLA ASC")->fetch_all(MYSQLI_ASSOC);
$turmas = $mysqli->query("SELECT t.ID_TURMA, t.ANO, t.NOME_TURMA, t.SERIE, t.ID_ESCOLA, e.NOME_ESCOLA
  FROM TURMA t JOIN ESCOLA e ON e.ID_ESCOLA=t.ID_ESCOLA
  ORDER BY t.ANO DESC, t.NOME_TURMA ASC")->fetch_all(MYSQLI_ASSOC);
$profs = $mysqli->query("SELECT MATRICULA, NOME FROM USERS WHERE TIPO='PROF' AND ACTIVE=1 ORDER BY NOME ASC")->fetch_all(MYSQLI_ASSOC);
$profTurmas = $mysqli->query("SELECT pt.MATRICULA, pt.ID_TURMA, u.NOME AS NOME_PROF, t.NOME_TURMA, t.SERIE, t.ANO
  FROM PROF_TURMA pt
  JOIN USERS u ON u.MATRICULA=pt.MATRICULA
  JOIN TURMA t ON t.ID_TURMA=pt.ID_TURMA
  WHERE u.TIPO='PROF'
  ORDER BY u.NOME ASC, t.ANO DESC, t.NOME_TURMA ASC")->fetch_all(MYSQLI_ASSOC);
$alunos = $mysqli->query("SELECT u.MATRICULA, u.NOME, u.ID_TURMA, u.ID_ESCOLA, t.NOME_TURMA, e.NOME_ESCOLA
  FROM USERS u
  LEFT JOIN TURMA t ON t.ID_TURMA=u.ID_TURMA
  LEFT JOIN ESCOLA e ON e.ID_ESCOLA=u.ID_ESCOLA
  WHERE u.TIPO='ALUNO' AND u.ACTIVE=1
  ORDER BY u.NOME ASC")->fetch_all(MYSQLI_ASSOC);

piths_head('Admin - PITHS');
piths_navbar();
?>
<div class="container py-4">
  <div class="piths-glass p-4">
    <h2 class="brand mb-3">Painel do Administrador 🛠️</h2>

    <?php if ($flashErr): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($flashOk): ?>
      <div class="alert alert-success"><?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fw-bold">Cadastros pendentes</div><div class="display-6"><?= $k_pendentes ?></div></div></div></div>
      <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fw-bold">Turmas</div><div class="display-6"><?= $k_turmas ?></div></div></div></div>
      <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fw-bold">Registros (status)</div><div class="display-6"><?= $k_status ?></div></div></div></div>
      <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fw-bold">Alunos ativos</div><div class="display-6"><?= $k_alunos ?></div></div></div></div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
      <a class="btn btn-warning btn-fun" href="../admin/validar.php">Validar cadastros</a>
    </div>

    <h5 class="mb-2">Turmas</h5>
    <div class="p-3 border rounded-3 mb-3 bg-white">
      <form method="post" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="add_turma">
        <div class="col-md-3">
          <label class="form-label">Nome</label>
          <input class="form-control form-control-sm" type="text" name="nome_turma" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Série</label>
          <input class="form-control form-control-sm" type="text" name="serie" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Ano</label>
          <input class="form-control form-control-sm" type="number" name="ano" min="2000" max="2100" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Escola</label>
          <select class="form-select form-select-sm" name="id_escola" required>
            <option value="">Selecione</option>
            <?php foreach ($escolas as $e): ?>
              <option value="<?= (int)$e['ID_ESCOLA'] ?>"><?= htmlspecialchars($e['NOME_ESCOLA'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-1">
          <button class="btn btn-success btn-sm btn-fun w-100" type="submit">Adicionar</button>
        </div>
      </form>
    </div>

    <div class="table-responsive mb-4">
      <table class="table table-striped align-middle">
        <thead><tr><th>ID</th><th>Nome</th><th>Série</th><th>Ano</th><th>Escola</th><th></th><th></th></tr></thead>
        <tbody>
          <?php foreach ($turmas as $t): ?>
            <?php $turmaFormId = 'turma-form-' . (int)$t['ID_TURMA']; ?>
            <tr>
              <td>
                #<?= (int)$t['ID_TURMA'] ?>
                <form method="post" id="<?= $turmaFormId ?>">
                  <input type="hidden" name="action" value="update_turma">
                  <input type="hidden" name="id_turma" value="<?= (int)$t['ID_TURMA'] ?>">
                </form>
              </td>
              <td>
                <input class="form-control form-control-sm" type="text" name="nome_turma" form="<?= $turmaFormId ?>" value="<?= htmlspecialchars($t['NOME_TURMA'], ENT_QUOTES, 'UTF-8') ?>" required>
              </td>
              <td>
                <input class="form-control form-control-sm" type="text" name="serie" form="<?= $turmaFormId ?>" value="<?= htmlspecialchars($t['SERIE'], ENT_QUOTES, 'UTF-8') ?>" required>
              </td>
              <td>
                <input class="form-control form-control-sm" type="number" name="ano" min="2000" max="2100" form="<?= $turmaFormId ?>" value="<?= (int)$t['ANO'] ?>" required>
              </td>
              <td>
                <select class="form-select form-select-sm" name="id_escola" form="<?= $turmaFormId ?>" required>
                  <?php foreach ($escolas as $e): ?>
                    <option value="<?= (int)$e['ID_ESCOLA'] ?>" <?= ((int)$e['ID_ESCOLA'] === (int)$t['ID_ESCOLA']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($e['NOME_ESCOLA'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <button class="btn btn-outline-dark btn-sm btn-fun" type="submit" form="<?= $turmaFormId ?>">Salvar</button>
              </td>
              <td>
                <form method="post" onsubmit="return confirm('Excluir esta turma?');">
                  <input type="hidden" name="action" value="delete_turma">
                  <input type="hidden" name="id_turma" value="<?= (int)$t['ID_TURMA'] ?>">
                  <button class="btn btn-outline-danger btn-sm btn-fun" type="submit">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <h5 class="mb-2">Professores por turma</h5>
    <div class="p-3 border rounded-3 mb-3 bg-white">
      <form method="post" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="add_prof_turma">
        <div class="col-md-6">
          <label class="form-label">Professor</label>
          <select class="form-select form-select-sm" name="matricula" required>
            <option value="">Selecione</option>
            <?php foreach ($profs as $p): ?>
              <option value="<?= (int)$p['MATRICULA'] ?>"><?= htmlspecialchars($p['NOME'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$p['MATRICULA'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label">Turma</label>
          <select class="form-select form-select-sm" name="id_turma" required>
            <option value="">Selecione</option>
            <?php foreach ($turmas as $t): ?>
              <option value="<?= (int)$t['ID_TURMA'] ?>"><?= htmlspecialchars($t['NOME_TURMA'], ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($t['SERIE'], ENT_QUOTES, 'UTF-8') ?> • <?= (int)$t['ANO'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-1">
          <button class="btn btn-success btn-sm btn-fun w-100" type="submit">Vincular</button>
        </div>
      </form>
    </div>

    <div class="table-responsive mb-4">
      <table class="table table-striped align-middle">
        <thead><tr><th>Professor</th><th>Turma</th><th>Ano</th><th></th></tr></thead>
        <tbody>
          <?php if (empty($profTurmas)): ?>
            <tr><td colspan="4" class="text-muted">Nenhum vínculo cadastrado.</td></tr>
          <?php else: ?>
            <?php foreach ($profTurmas as $pt): ?>
              <tr>
                <td><?= htmlspecialchars($pt['NOME_PROF'], ENT_QUOTES, 'UTF-8') ?> (#<?= (int)$pt['MATRICULA'] ?>)</td>
                <td><?= htmlspecialchars($pt['NOME_TURMA'], ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($pt['SERIE'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$pt['ANO'] ?></td>
                <td>
                  <form method="post" onsubmit="return confirm('Remover vínculo deste professor com esta turma?');">
                    <input type="hidden" name="action" value="remove_prof_turma">
                    <input type="hidden" name="matricula" value="<?= (int)$pt['MATRICULA'] ?>">
                    <input type="hidden" name="id_turma" value="<?= (int)$pt['ID_TURMA'] ?>">
                    <button class="btn btn-outline-danger btn-sm btn-fun" type="submit">Remover</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <h5 class="mb-2">Alunos ativos</h5>
    <div class="table-responsive mb-4">
      <table class="table table-striped align-middle">
        <thead><tr><th>Matrícula</th><th>Nome</th><th>Escola</th><th>Turma</th><th></th><th></th></tr></thead>
        <tbody>
          <?php foreach ($alunos as $a): ?>
            <?php $alunoFormId = 'aluno-form-' . (int)$a['MATRICULA']; ?>
            <tr>
              <td>
                <?= (int)$a['MATRICULA'] ?>
                <form method="post" id="<?= $alunoFormId ?>">
                  <input type="hidden" name="action" value="update_aluno">
                  <input type="hidden" name="matricula" value="<?= (int)$a['MATRICULA'] ?>">
                </form>
              </td>
              <td>
                <input class="form-control form-control-sm" type="text" name="nome" form="<?= $alunoFormId ?>" value="<?= htmlspecialchars($a['NOME'], ENT_QUOTES, 'UTF-8') ?>" required>
              </td>
              <td>
                <select class="form-select form-select-sm" name="id_escola" form="<?= $alunoFormId ?>" required>
                  <?php foreach ($escolas as $e): ?>
                    <option value="<?= (int)$e['ID_ESCOLA'] ?>" <?= ((int)$e['ID_ESCOLA'] === (int)$a['ID_ESCOLA']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($e['NOME_ESCOLA'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <select class="form-select form-select-sm" name="id_turma" form="<?= $alunoFormId ?>" required>
                  <?php foreach ($turmas as $t): ?>
                    <option value="<?= (int)$t['ID_TURMA'] ?>" <?= ((int)$t['ID_TURMA'] === (int)$a['ID_TURMA']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($t['NOME_TURMA'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <button class="btn btn-outline-dark btn-sm btn-fun" type="submit" form="<?= $alunoFormId ?>">Salvar</button>
              </td>
              <td>
                <form method="post" onsubmit="return confirm('Excluir este aluno?');">
                  <input type="hidden" name="action" value="delete_aluno">
                  <input type="hidden" name="matricula" value="<?= (int)$a['MATRICULA'] ?>">
                  <button class="btn btn-outline-danger btn-sm btn-fun" type="submit">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <h5 class="mb-2">Top XP 🏆</h5>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead><tr><th>Nome</th><th>XP</th><th>Nível</th><th>Título</th></tr></thead>
        <tbody>
          <?php while($row = $topxp->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['NOME']) ?></td>
              <td><?= (int)$row['TOTAL_XP'] ?></td>
              <td><?= (int)$row['LEVEL'] ?></td>
              <td><?= htmlspecialchars($row['TITLE']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php piths_footer(); ?>
