<?php
// Aqui você pode puxar aluno_id da sessão
session_start();
$aluno_id = isset($_SESSION['aluno_id']) ? (int)$_SESSION['aluno_id'] : 1;

$atividade_id = isset($_GET['atividade_id']) ? (int)$_GET['atividade_id'] : 1;

// DADOS DEMO (o certo é vir do BD/JSON do módulo)
$questions = [
  ["value"=>"1",  "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Natural", "hint"=>"Natural é inteiro não negativo."],
  ["value"=>"0",  "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Natural", "hint"=>"Depende do material, aqui o 0 é natural."],
  ["value"=>"-3", "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Inteiro", "hint"=>"Inteiros incluem negativos."],
  ["value"=>"1/2","options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Racional", "hint"=>"Pode ser escrito como fração."],
  ["value"=>"√2", "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Irracional", "hint"=>"Decimais infinitos não periódicos."],
  ["value"=>"5",  "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Natural", "hint"=>"5 é inteiro positivo."],
  ["value"=>"-1", "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Inteiro", "hint"=>"Inteiro pode ser negativo."],
  ["value"=>"0,25","options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Racional", "hint"=>"0,25 = 25/100."],
  ["value"=>"π",  "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Irracional", "hint"=>"π é irracional."],
  ["value"=>"2",  "options"=>["Natural","Irracional","Inteiro","Racional"], "answer"=>"Natural", "hint"=>"Inteiro não negativo."],
];

$total_questoes = count($questions);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PITHS • Questões</title>
  <link rel="stylesheet" href="atividade.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <!-- seta ao lado da progressbar volta para seleção -->
    <a id="backBtn" class="back-btn" href="../../aluno/minhas_atividades.php" aria-label="Voltar"></a>
    <div class="progress-wrap">
      <div class="progress-track"><div class="progress-bar" id="progressBar"></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="center">

    <div class="card-number">
      <div class="big" id="qNumber">1</div>
    </div>

    <div class="answers">
      <div class="answer-grid">
        <div class="answer-row">
          <button class="answer-btn" id="btnA" type="button">Natural</button>
          <button class="answer-btn" id="btnB" type="button">Irracional</button>
        </div>
        <div class="answer-row">
          <button class="answer-btn" id="btnC" type="button">Inteiro</button>
          <button class="answer-btn" id="btnD" type="button">Racional</button>
        </div>
      </div>

      <div class="feedback" id="feedback"></div>

      <div class="footer-next">
        <button class="btn" id="nextBtn" type="button" disabled>Próximo</button>
      </div>

      <div class="small">
        Responda. Se acertar, a próxima questão carrega aqui mesmo.
      </div>
    </div>

  </div>
</div>

<script src="atividade.js"></script>
<script>
  PITHS.initQuestoes({
    atividadeId: <?php echo (int)$atividade_id; ?>,
    alunoId: <?php echo (int)$aluno_id; ?>,
    totalPrefixSteps: 2, // vídeo + texto
    backUrl: "../../aluno/minhas_atividades.php",
    finishUrl: "atividade_fim.php",
    saveUrl: "save_progress.php",
    pointsPerHit: 10,
    questions: <?php echo json_encode($questions, JSON_UNESCAPED_UNICODE); ?>
  });
</script>

</body>
</html>