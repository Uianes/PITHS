<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('ALUNO');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$matricula = (int)($_SESSION['matricula'] ?? 0);
$id_turma = (int)($_SESSION['id_turma'] ?? 0);

$ativ = $mysqli->prepare('SELECT a.ID_ATIVIDADE, a.PATH_ATIVIDADE FROM ATIVIDADE_TURMA atx JOIN ATIVIDADE a ON a.ID_ATIVIDADE=atx.ID_ATIVIDADE WHERE atx.ID_TURMA=? ORDER BY a.ID_ATIVIDADE DESC');
$ativ->bind_param('i', $id_turma);
$ativ->execute();
$ativRes = $ativ->get_result();

piths_head('Aluno - PITHS');
piths_navbar();
?>
<div class="container py-4">
  <div class="piths-glass p-4">
    <h2 class="brand mb-3">Minhas atividades 🎯</h2>

    <?php if ($ativRes->num_rows === 0): ?>
      <div class="alert alert-warning">
        <strong>Aguardando seu professor selecionar as atividades</strong>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php while($a = $ativRes->fetch_assoc()): ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body">
                <div class="fw-bold mb-2">Atividade #<?= (int)$a['ID_ATIVIDADE'] ?></div>
                <div class="small text-muted mb-3"><code><?= htmlspecialchars($a['PATH_ATIVIDADE']) ?></code></div>
                <a class="btn btn-primary btn-fun w-100" href="<?= htmlspecialchars($a['PATH_ATIVIDADE']) ?>">Abrir atividade</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>

  </div>
</div>
<?php piths_footer(); ?>
