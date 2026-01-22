<?php
$pontos = isset($_GET['pontos']) ? (int)$_GET['pontos'] : 0;
$tempo  = isset($_GET['tempo']) ? (int)$_GET['tempo'] : 0;
$back   = isset($_GET['back']) ? $_GET['back'] : "../../aluno/minhas_atividades.php";

function fmtTempo($s){
  $m = floor($s/60); $r = $s%60;
  if ($m <= 0) return $r."s";
  return $m."m ".$r."s";
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PITHS • Concluído</title>
  <link rel="stylesheet" href="atividade.css">
</head>
<body>

<div class="page">
  <div class="center" style="padding-top:80px;">
    <div class="text-area" style="max-width:1100px;">
      <h1 style="font-size:64px; margin:0 0 10px;"><b>Parabéns!</b></h1>
      <p style="font-size:34px;"><b>Você ganhou <?php echo (int)$pontos; ?> pontos!</b></p>
      <p style="font-size:18px; opacity:0.8;">Tempo total: <?php echo htmlspecialchars(fmtTempo($tempo)); ?></p>
    </div>

    <div class="nav-row">
      <a class="btn" href="<?php echo htmlspecialchars($back); ?>">Voltar para atividades</a>
    </div>
  </div>
</div>

<script>
  // redireciona automático após 2.5s (como você pediu)
  setTimeout(function(){
    window.location.href = <?php echo json_encode($back); ?>;
  }, 2500);
</script>

</body>
</html>