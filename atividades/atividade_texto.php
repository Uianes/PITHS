<?php
$atividade_id = isset($_GET['atividade_id']) ? (int)$_GET['atividade_id'] : 1;

// total de questões (ideal do BD; fixo aqui)
$total_questoes = 10;

$total_etapas = 2 + $total_questoes + 1;
$pct = (int) round((2 / $total_etapas) * 100); // vídeo + texto concluídos

$anterior = "atividade_video.php?atividade_id=".$atividade_id;
$proximo  = "atividade_questoes.php?atividade_id=".$atividade_id;
$minhas   = "../../aluno/minhas_atividades.php";
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PITHS • Texto</title>
  <link rel="stylesheet" href="atividade.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <a id="backBtn" class="back-btn" href="<?php echo htmlspecialchars($minhas); ?>" aria-label="Voltar"></a>
    <div class="progress-wrap">
      <div class="progress-track"><div class="progress-bar" id="progressBar"></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="center">
    <div class="text-area">
      <h1>Texto complementar</h1>

      <p><b>Números naturais</b> são inteiros não negativos. Em muitos materiais incluem o <b>0</b>.</p>
      <p><b>Inteiros</b> incluem negativos, o zero e positivos. Ex: -2, -1, 0, 1, 2.</p>
      <p><b>Racionais</b> podem ser escritos como fração. Ex: 1/2, -3/4, 5 (= 5/1).</p>
      <p><b>Irracionais</b> não podem ser escritos como fração e têm decimais infinitos não periódicos. Ex: √2, π.</p>
    </div>

    <div class="nav-row">
      <a class="btn" href="<?php echo htmlspecialchars($anterior); ?>">Anterior</a>
      <a class="btn" href="<?php echo htmlspecialchars($proximo); ?>">Próximo</a>
    </div>
  </div>
</div>

<script src="atividade.js"></script>
<script>
  PITHS.setProgress(<?php echo (int)$pct; ?>);
</script>

</body>
</html>