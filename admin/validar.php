<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('ADM');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $m = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
  if ($m > 0) {
    $up = $mysqli->prepare('UPDATE USERS SET ACTIVE=1 WHERE MATRICULA=?');
    $up->bind_param('i', $m);
    $up->execute();
  }
  header('Location: validar.php');
  exit;
}

$pendentes = $mysqli->query("SELECT u.MATRICULA, u.TIPO, u.NOME, u.ID_TURMA, u.ID_ESCOLA,
  COALESCE(t.NOME_TURMA, CONCAT('#', u.ID_TURMA)) AS TURMA_NOME,
  COALESCE(e.NOME_ESCOLA, CONCAT('#', u.ID_ESCOLA)) AS ESCOLA_NOME
  FROM USERS u
  LEFT JOIN TURMA t ON t.ID_TURMA = u.ID_TURMA
  LEFT JOIN ESCOLA e ON e.ID_ESCOLA = u.ID_ESCOLA
  WHERE u.ACTIVE=0
  ORDER BY u.NOME ASC");

piths_head('Validar cadastros - PITHS');
piths_navbar();
?>
<div class="container py-4">
  <div class="piths-glass p-4">
    <h2 class="brand mb-3">Validar cadastros ✅</h2>

    <?php if ($pendentes->num_rows === 0): ?>
      <div class="alert alert-success">Nenhum cadastro pendente 🎉</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>Matrícula</th><th>Tipo</th><th>Nome</th><th>Turma</th><th>Escola</th><th></th></tr></thead>
          <tbody>
            <?php while($u = $pendentes->fetch_assoc()): ?>
              <tr>
                <td><?= (int)$u['MATRICULA'] ?></td>
                <td><?= htmlspecialchars($u['TIPO']) ?></td>
                <td><?= htmlspecialchars($u['NOME']) ?></td>
                <td><?= htmlspecialchars($u['TURMA_NOME'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['ESCOLA_NOME'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <form method="post" class="m-0">
                    <input type="hidden" name="matricula" value="<?= (int)$u['MATRICULA'] ?>">
                    <button class="btn btn-success btn-sm btn-fun" type="submit">Validar</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <a class="btn btn-outline-dark btn-fun" href="../admin/index.php">Voltar</a>
  </div>
</div>
<?php piths_footer(); ?>
