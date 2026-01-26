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
<style>
.turma-card{
  background: #d9b3ff;
  border-radius: 24px;
  min-height: 180px;
  box-shadow: 0 12px 28px rgba(0,0,0,.18);
}
.turma-card .btn{
  border-radius: 999px;
  font-weight: 700;
}
</style>
<div class="container py-4">
  <div class="piths-glass p-4">
    <h2 class="brand mb-3">Painel do Professor 👩‍🏫👨‍🏫</h2>
    <h4 class="mb-3">Minhas turmas</h4>
    <?php if ($turmasRes->num_rows === 0): ?>
      <div class="alert alert-warning">Você ainda não está vinculado a nenhuma turma (tabela PROF_TURMA).</div>
    <?php else: ?>
      <div class="row g-3">
        <?php while($t = $turmasRes->fetch_assoc()): ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="turma-card p-3 h-100 d-flex flex-column">
              <div class="text-center">
                <div class="fw-bold">Turma: <?= htmlspecialchars($t['NOME_TURMA']) ?></div>
                <div class="fw-bold">Série: <?= htmlspecialchars($t['SERIE']) ?></div>
                <div class="fw-bold">Ano: <?= (int)$t['ANO'] ?></div>
              </div>
              <div class="mt-auto d-flex justify-content-center">
                <a class="btn btn-dark" href="turma.php?id=<?= (int)$t['ID_TURMA'] ?>">Ver</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php piths_footer(); ?>
