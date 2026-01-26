<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('ALUNO');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$matricula = (int)($_SESSION['matricula'] ?? 0);
$id_turma  = (int)($_SESSION['id_turma'] ?? 0);

// Busca atividades + status do aluno (se existir)
$sql = "
SELECT
  a.ID_ATIVIDADE,
  a.PATH_ATIVIDADE,
  s.STATUS,
  s.TEMPO,
  s.TENTATIVAS
FROM ATIVIDADE_TURMA atx
JOIN ATIVIDADE a ON a.ID_ATIVIDADE=atx.ID_ATIVIDADE
LEFT JOIN ATIVIDADE_STATUS s
  ON s.ID_ATIVIDADE = a.ID_ATIVIDADE
 AND s.MATRICULA = ?
WHERE atx.ID_TURMA=?
ORDER BY a.ID_ATIVIDADE DESC
";
$ativ = $mysqli->prepare($sql);
$ativ->bind_param('ii', $matricula, $id_turma);
$ativ->execute();
$ativRes = $ativ->get_result();

// Regras de ícone:
// - COMPLETO => ok
// - REFAZER  => danger (se existir no enum)
// - INCOMPLETO ou null => lamp
// - fallback sem REFAZER: se tentativas >= 3 => danger
function activity_badge($status, $tentativas) {
  $tentativas = (int)$tentativas;

  if ($status === 'COMPLETO') return ['✅','success','Atividade finalizada'];
  if ($status === 'REFAZER')  return ['⚠️','danger','Precisa ser refeita'];

  // fallback para bancos que NÃO têm REFAZER:
  if ($status === 'INCOMPLETO' && $tentativas >= 3) {
    return ['⚠️','danger','Precisa ser refeita'];
  }

  return ['💡','warning','Ainda não concluída'];
}

piths_head('Aluno - PITHS');
piths_navbar();
?>
<style>
/* botão circular */
.piths-circle-btn{
  width:56px;height:56px;border-radius:999px;
  display:flex;align-items:center;justify-content:center;
  font-size:24px;font-weight:700;
  box-shadow:0 10px 20px rgba(0,0,0,.18);
  text-decoration:none;
}
.piths-circle-btn:hover{ transform: translateY(-1px); }
</style>

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
          <?php
            $id = (int)$a['ID_ATIVIDADE'];
            $path = (string)$a['PATH_ATIVIDADE'];
            $status = $a['STATUS'] ?? null;
            $tempo = $a['TEMPO'] ?? null;
            $tent = (int)($a['TENTATIVAS'] ?? 0);

            list($ico, $bs, $label) = activity_badge($status, $tent);

            // abre sempre pelo runner (fluxo vídeo->texto->questões->fim)
            $openUrl = "../runner/atividade.php?id_atividade=".$id."&step=video";
          ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body d-flex flex-column">
                <?php
                  $activityName = trim(basename($path));
                  if ($activityName === '') {
                    $activityName = 'Atividade';
                  }
                ?>
                <div class="fw-bold mb-1"><?= htmlspecialchars($activityName) ?></div>

                <div class="mt-auto d-flex align-items-center justify-content-between">
                  <div class="small">
                    <div class="text-muted"><?= htmlspecialchars($label) ?></div>
                    <?php if ($tempo): ?>
                      <div class="text-muted">⏱️ <?= htmlspecialchars($tempo) ?></div>
                    <?php endif; ?>
                    <?php if ($tent > 0): ?>
                      <div class="text-muted">🔁 Tentativas: <?= $tent ?></div>
                    <?php endif; ?>
                  </div>

                  <a class="piths-circle-btn bg-<?= $bs ?> text-white"
                     href="<?= htmlspecialchars($openUrl) ?>"
                     title="<?= htmlspecialchars($label) ?>"
                     aria-label="<?= htmlspecialchars($label) ?>">
                    <?= $ico ?>
                  </a>
                </div>

              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>

  </div>
</div>
<?php piths_footer(); ?>
