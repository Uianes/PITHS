<?php
// Exemplo: você pode receber ?atividade_id=1
$atividade_id = isset($_GET['atividade_id']) ? (int)$_GET['atividade_id'] : 1;

// Ajuste: total de questões (ideal puxar do BD; aqui fixo)
$total_questoes = 10;

$total_etapas = 2 + $total_questoes + 1; // video + texto + questoes + fim
$pct = (int) round((1 / $total_etapas) * 100);

// URLs
$minhas = "../../aluno/minhas_atividades.php";
$proximo = "atividade_texto.php?atividade_id=".$atividade_id;
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PITHS • Vídeo</title>
  <link rel="stylesheet" href="atividade.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <a id="backBtn" class="back-btn" href="<?php echo htmlspecialchars($minhas); ?>" aria-label="Voltar"></a>
    <div class="progress-wrap" aria-label="Progresso">
      <div class="progress-track"><div class="progress-bar" id="progressBar"></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="center">
    <div class="video-box">
      <!-- Melhor: vídeo local mp4 (mais leve que embed). Troque o src. -->
      <video id="video" controls>
        <source src="video.mp4" type="video/mp4">
        Seu navegador não suporta vídeo.
      </video>
    </div>

    <button class="btn" id="next" disabled>Próximo</button>
    <div class="small">O botão libera quando o vídeo terminar.</div>
  </div>
</div>

<script src="atividade.js"></script>
<script>
  PITHS.setProgress(<?php echo (int)$pct; ?>);

  (function(){
    var v = document.getElementById("video");
    var btn = document.getElementById("next");
    btn.onclick = function(){ window.location.href = <?php echo json_encode($proximo); ?>; };

    // libera quando terminar
    v.addEventListener("ended", function(){
      btn.disabled = false;
    });

    // (opcional) também libera se o aluno arrastar até o fim
    v.addEventListener("timeupdate", function(){
      try {
        if (v.duration && v.currentTime >= (v.duration - 0.25)) btn.disabled = false;
      } catch(e){}
    });
  })();
</script>

</body>
</html>