<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
$escolas = [];
$turmas = [];
$resEscolas = $mysqli->query('SELECT ID_ESCOLA, NOME_ESCOLA FROM ESCOLA ORDER BY NOME_ESCOLA ASC');
if ($resEscolas instanceof mysqli_result) {
  $escolas = $resEscolas->fetch_all(MYSQLI_ASSOC);
}
$resTurmas = $mysqli->query('SELECT ID_TURMA, ID_ESCOLA, NOME_TURMA FROM TURMA ORDER BY NOME_TURMA ASC');
if ($resTurmas instanceof mysqli_result) {
  $turmas = $resTurmas->fetch_all(MYSQLI_ASSOC);
}
piths_head('Cadastro - PITHS');
piths_navbar();
$err = $_GET['err'] ?? '';
$ok = $_GET['ok'] ?? '';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="piths-glass p-4">
        <div class="text-center mb-3">
          <h2 class="brand">Cadastro</h2>
          <p class="mb-0">Cadastre-se para começar a jogar e aprender Matemática!</p>
        </div>

        <?php if ($err): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($ok): ?>
          <div class="alert alert-success">Cadastro enviado! Aguarde validação 😊</div>
        <?php endif; ?>

        <form method="post" action="cadastro_action.php" autocomplete="off">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="matricula">Matrícula</label>
              <input class="form-control" type="number" id="matricula" name="matricula" required>
            </div>
            <div class="col-md-8">
              <label class="form-label" for="nome">Nome</label>
              <input class="form-control" type="text" id="nome" name="nome" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="tipo">Tipo</label>
              <select class="form-select" id="tipo" name="tipo" required>
                <option value="ALUNO">Aluno</option>
                <option value="PROF">Professor</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="id_escola">Escola</label>
              <select class="form-select" id="id_escola" name="id_escola" required>
                <option value="">Selecione a escola</option>
                <?php foreach ($escolas as $e): ?>
                  <option value="<?= (int)$e['ID_ESCOLA'] ?>">
                    <?= htmlspecialchars($e['NOME_ESCOLA'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (empty($escolas)): ?>
                <div class="form-text">Nenhuma escola encontrada.</div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="id_turma">Turma</label>
              <select class="form-select" id="id_turma" name="id_turma" required>
                <option value="">Selecione a escola primeiro</option>
                <?php foreach ($turmas as $t): ?>
                  <option value="<?= (int)$t['ID_TURMA'] ?>" data-escola="<?= (int)$t['ID_ESCOLA'] ?>">
                    <?= htmlspecialchars($t['NOME_TURMA'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (empty($turmas)): ?>
                <div class="form-text">Nenhuma turma encontrada.</div>
              <?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="birth_date">Data de nascimento</label>
              <input class="form-control" type="date" id="birth_date" name="birth_date" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="avatar">Avatar</label>
              <select class="form-select" id="avatar" name="avatar" required>
                <option value="/assets/avatarAmarelo.png">Amarelo</option>
                <option value="/assets/avatarAzul.png">Azul</option>
                <option value="/assets/avatarRosa.png">Rosa</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="senha">Senha</label>
              <input class="form-control" type="password" id="senha" name="senha" required>
            </div>
          </div>

          <div class="mt-4">
            <button class="btn btn-success btn-lg btn-fun w-100" type="submit">Enviar cadastro</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
<script>
  const escolaSelect = document.getElementById('id_escola');
  const turmaSelect = document.getElementById('id_turma');
  const turmaOptions = Array.from(turmaSelect.options).slice(1);

  function filtrarTurmas() {
    const escolaId = escolaSelect.value;
    turmaSelect.value = '';
    turmaOptions.forEach((opt) => {
      opt.hidden = escolaId && opt.dataset.escola !== escolaId;
    });
    turmaSelect.options[0].textContent = escolaId ? 'Selecione a turma' : 'Selecione a escola primeiro';
  }

  escolaSelect.addEventListener('change', filtrarTurmas);
  filtrarTurmas();
</script>
<?php piths_footer(); ?>
