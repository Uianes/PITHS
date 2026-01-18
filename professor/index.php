<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('PROF');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$matricula = (int)($_SESSION['matricula'] ?? 0);

$turmas = $mysqli->prepare('SELECT t.ID_TURMA, t.NOME_TURMA, t.SERIE, t.ANO FROM PROF_TURMA pt JOIN TURMA t ON t.ID_TURMA=pt.ID_TURMA WHERE pt.MATRICULA=?');
$turmas->bind_param('i', $matricula);
$turmas->execute();
$turmasRes = $turmas->get_result();

piths_head('Professor - PITHS');
piths_navbar();
?>
<div class="container py-4">
  <div class="piths-glass p-4">
    <h2 class="brand mb-3">Painel do Professor 👩‍🏫👨‍🏫</h2>

    <div class="d-flex flex-wrap gap-2 mb-4">
      <a class="btn btn-primary btn-fun" href="../professor/atividades.php">Selecionar / adicionar atividades</a>
    </div>

    <h5>Minhas turmas</h5>
    <?php if ($turmasRes->num_rows === 0): ?>
      <div class="alert alert-warning">Você ainda não está vinculado a nenhuma turma (tabela PROF_TURMA).</div>
    <?php else: ?>
      <?php while($t = $turmasRes->fetch_assoc()): ?>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
              <div>
                <div class="fw-bold">Turma #<?= (int)$t['ID_TURMA'] ?> — <?= htmlspecialchars($t['NOME_TURMA']) ?></div>
                <div class="text-muted small">Série: <?= htmlspecialchars($t['SERIE']) ?> • Ano: <?= (int)$t['ANO'] ?></div>
              </div>
            </div>

            <?php
              $id_turma = (int)$t['ID_TURMA'];
              // Alunos da turma
              $al = $mysqli->query("SELECT u.MATRICULA, u.NOME, g.TOTAL_XP, g.LEVEL, g.TITLE
                                   FROM USERS u
                                   LEFT JOIN GAMIFICACAO g ON g.MATRICULA=u.MATRICULA
                                   WHERE u.TIPO='ALUNO' AND u.ACTIVE=1 AND u.ID_TURMA={$id_turma}
                                   ORDER BY u.NOME");
            ?>

            <div class="mt-3">
              <div class="fw-bold mb-2">Alunos</div>
              <?php if ($al->num_rows === 0): ?>
                <div class="text-muted">Nenhum aluno ativo nessa turma.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-sm table-striped align-middle">
                    <thead><tr><th>Nome</th><th>Matrícula</th><th>XP</th><th>Nível</th><th>Status (registros)</th></tr></thead>
                    <tbody>
                      <?php while($a = $al->fetch_assoc()): ?>
                        <?php
                          $m = (int)$a['MATRICULA'];
                          $cnt = (int)$mysqli->query("SELECT COUNT(*) c FROM ATIVIDADE_STATUS WHERE MATRICULA={$m}")->fetch_assoc()['c'];
                        ?>
                        <tr>
                          <td><?= htmlspecialchars($a['NOME']) ?></td>
                          <td><?= $m ?></td>
                          <td><?= (int)($a['TOTAL_XP'] ?? 0) ?></td>
                          <td><?= (int)($a['LEVEL'] ?? 1) ?></td>
                          <td><?= $cnt ?></td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>

  </div>
</div>
<?php piths_footer(); ?>
