<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('ALUNO');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/layout.php';

$matricula = (int)($_SESSION['matricula'] ?? 0);

$id_atividade = (int)($_GET['id_atividade'] ?? 0);
$step = $_GET['step'] ?? 'video';
$allowed = ['video','texto','questoes','fim'];
if (!in_array($step, $allowed, true)) $step = 'video';

if ($id_atividade <= 0) { http_response_code(400); die("Atividade inválida."); }

// Path base da atividade (ex: atividades/tipos_numeros)
$stmt = $mysqli->prepare("SELECT PATH_ATIVIDADE FROM ATIVIDADE WHERE ID_ATIVIDADE=? LIMIT 1");
$stmt->bind_param("i", $id_atividade);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { http_response_code(404); die("Atividade não encontrada."); }

$base = trim($row['PATH_ATIVIDADE'], "/");

// caminhos reais no disco (assumindo que PATH_ATIVIDADE é relativo à raiz do projeto)
$root = realpath(__DIR__ . "/..");
$activityDir = realpath($root . "/" . $base);

// Segurança básica: impede path traversal
if ($activityDir === false || strpos($activityDir, $root) !== 0) {
  http_response_code(403); die("Path da atividade inválido.");
}

// arquivos
$videoFile   = $activityDir . "/video.mp4";
$textoFile   = $activityDir . "/texto.html";
$questoesFile= $activityDir . "/questoes.json";

// URLs para o browser (relativas)
$videoUrl = "../" . $base . "/video.mp4";

$backUrl = "../aluno/index.php"; // ajuste se seu arquivo for outro

// Questões (contagem para progress)
$questions = [];
if (file_exists($questoesFile)) {
  $j = json_decode(file_get_contents($questoesFile), true);
  if (is_array($j)) $questions = $j;
}
$totalQ = max(1, count($questions));
$totalSteps = 2 + $totalQ + 1; // video + texto + questoes + fim

function pct_step($n, $totalSteps){
  return (int) round(($n / $totalSteps) * 100);
}

if ($step === 'video') {
  $pct = pct_step(1, $totalSteps);
  piths_head("Atividade - Vídeo");
  piths_navbar();
  ?>
  <div class="container py-4">
    <div class="piths-glass p-4 text-center">
      <div class="d-flex align-items-center gap-3 mb-3">
        <a class="btn btn-dark rounded-circle" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center"
           href="<?= htmlspecialchars($backUrl) ?>" aria-label="Voltar">←</a>
        <div class="progress flex-grow-1" style="height:14px;">
          <div class="progress-bar bg-success" id="pb" style="width:<?= $pct ?>%"></div>
        </div>
      </div>

      <div class="ratio ratio-16x9 bg-dark rounded overflow-hidden">
        <?php if (file_exists($videoFile)): ?>
          <video id="v" controls style="width:100%;height:100%;">
            <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
          </video>
        <?php else: ?>
          <div class="text-white p-4">Vídeo não encontrado: <code>video.mp4</code></div>
        <?php endif; ?>
      </div>

      <button id="next" class="btn btn-primary btn-fun mt-4" disabled>Próximo</button>
      <div class="small text-muted mt-2">O botão libera quando o vídeo terminar.</div>
    </div>
  </div>

  <script>
  (function(){
    var v = document.getElementById('v');
    var btn = document.getElementById('next');
    btn.onclick = function(){
      location.href = <?= json_encode("../runner/atividade.php?id_atividade=".$id_atividade."&step=texto") ?>;
    };
    if (!v) { btn.disabled = false; return; }
    v.addEventListener('ended', function(){ btn.disabled = false; });
    v.addEventListener('timeupdate', function(){
      try { if (v.duration && v.currentTime >= v.duration - 0.25) btn.disabled = false; } catch(e){}
    });
  })();
  </script>
  <?php
  piths_footer();
  exit;
}

if ($step === 'texto') {
  $pct = pct_step(2, $totalSteps);
  $textoHtml = file_exists($textoFile) ? file_get_contents($textoFile) : "<p><b>texto.html</b> não encontrado.</p>";

  piths_head("Atividade - Texto");
  piths_navbar();
  ?>
  <div class="container py-4">
    <div class="piths-glass p-4">
      <div class="d-flex align-items-center gap-3 mb-3">
        <a class="btn btn-dark rounded-circle" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center"
           href="<?= htmlspecialchars($backUrl) ?>" aria-label="Voltar">←</a>
        <div class="progress flex-grow-1" style="height:14px;">
          <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
        </div>
      </div>

      <div class="mb-4">
        <?= $textoHtml ?>
      </div>

      <div class="d-flex gap-3 justify-content-center">
        <a class="btn btn-outline-dark" href="<?= htmlspecialchars("../runner/atividade.php?id_atividade=".$id_atividade."&step=video") ?>">Anterior</a>
        <a class="btn btn-primary btn-fun" href="<?= htmlspecialchars("../runner/atividade.php?id_atividade=".$id_atividade."&step=questoes") ?>">Próximo</a>
      </div>
    </div>
  </div>
  <?php
  piths_footer();
  exit;
}

if ($step === 'questoes') {
  $pctBase = pct_step(2, $totalSteps); // começa após texto
  piths_head("Atividade - Questões");
  piths_navbar();
  ?>
  <style>
    .q-card{width:520px;max-width:92%;height:280px;background:#fff;border-radius:14px;box-shadow:0 10px 18px rgba(0,0,0,.15);
      display:flex;align-items:center;justify-content:center;margin:18px auto;}
    .q-val{font-size:170px;font-weight:800;}
    .q-opt{width:360px;max-width:44vw;margin:12px 14px;padding:14px 12px;border:2px solid #222;border-radius:14px;background:#fff;font-size:26px;cursor:pointer;}
    .q-opt.sel{outline:4px solid rgba(0,0,0,.12);}
    .q-fb{display:none;max-width:860px;margin:12px auto 0;padding:12px;border-radius:14px;border:2px solid rgba(0,0,0,.10);font-size:18px;}
    .q-fb.ok{display:block;background:rgba(127,227,95,.18);}
    .q-fb.bad{display:block;background:rgba(255,100,100,.14);}
  </style>

  <div class="container py-4">
    <div class="piths-glass p-4 text-center">

      <div class="d-flex align-items-center gap-3 mb-3">
        <a class="btn btn-dark rounded-circle" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center"
           href="<?= htmlspecialchars($backUrl) ?>" aria-label="Voltar">←</a>

        <div class="progress flex-grow-1" style="height:14px;">
          <div class="progress-bar bg-success" id="pb" style="width:<?= (int)$pctBase ?>%"></div>
        </div>
      </div>

      <div class="q-card"><div id="qVal" class="q-val">1</div></div>

      <div class="d-flex flex-wrap justify-content-center" style="max-width:860px;margin:0 auto;">
        <button class="q-opt" id="a"></button>
        <button class="q-opt" id="b"></button>
        <button class="q-opt" id="c"></button>
        <button class="q-opt" id="d"></button>
      </div>

      <div id="fb" class="q-fb"></div>

      <button id="next" class="btn btn-primary btn-fun mt-3" disabled>Próximo</button>
      <div class="small text-muted mt-2">Se acertar, a próxima questão carrega aqui mesmo.</div>

    </div>
  </div>

  <script>
  (function(){
    var questions = <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>;
    var totalQ = questions.length;
    var totalSteps = <?= (int)$totalSteps ?>;
    var prefix = 2; // video+texto
    var idx = 0, selected = null, startedAt = null, pontos = 0;

    var pb = document.getElementById('pb');
    var qVal = document.getElementById('qVal');
    var a = document.getElementById('a');
    var b = document.getElementById('b');
    var c = document.getElementById('c');
    var d = document.getElementById('d');
    var fb = document.getElementById('fb');
    var next = document.getElementById('next');

    function setProgress(doneQ){
      var doneSteps = prefix + doneQ;
      var pct = Math.round((doneSteps / totalSteps) * 100);
      pb.style.width = pct + '%';
    }

    function pick(btn){
      selected = btn.textContent || btn.innerText;
      next.disabled = false;
      fb.style.display = 'none';
      a.className='q-opt'; b.className='q-opt'; c.className='q-opt'; d.className='q-opt';
      btn.className='q-opt sel';
    }

    a.onclick=function(){pick(a);};
    b.onclick=function(){pick(b);};
    c.onclick=function(){pick(c);};
    d.onclick=function(){pick(d);};

    function post(url, data, cb){
      try{
        var x = new XMLHttpRequest();
        x.open('POST', url, true);
        x.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
        x.onreadystatechange=function(){ if(x.readyState===4){ cb && cb(x.status,x.responseText);} };
        var s=[];
        for(var k in data){ if(data.hasOwnProperty(k)) s.push(encodeURIComponent(k)+'='+encodeURIComponent(String(data[k]))); }
        x.send(s.join('&'));
      }catch(e){ cb && cb(0,''); }
    }

    function norm(s){ return String(s).toLowerCase().replace(/\s+/g,' ').trim(); }

    function render(){
      if(startedAt===null) startedAt = (new Date()).getTime();
      if(idx >= totalQ){ finish(); return; }

      var q = questions[idx];
      qVal.textContent = q.value;

      a.textContent = q.options[0];
      b.textContent = q.options[1];
      c.textContent = q.options[2];
      d.textContent = q.options[3];

      selected = null;
      next.disabled = true;
      fb.style.display = 'none';
      a.className='q-opt'; b.className='q-opt'; c.className='q-opt'; d.className='q-opt';
      setProgress(idx);
    }

    next.onclick=function(){
      if(selected===null) return;
      var q = questions[idx];
      var ok = (norm(selected) === norm(q.answer));

      if(ok){
        pontos += 10;
        fb.className='q-fb ok';
        fb.textContent='✅ Certo! Próxima…';
        fb.style.display='block';

        // salva andamento
        post('../runner/save_progress.php', {
          id_atividade: <?= (int)$id_atividade ?>,
          matricula: <?= (int)$matricula ?>,
          status: 'INCOMPLETO'
        });

        setTimeout(function(){ idx++; render(); }, 350);
      } else {
        fb.className='q-fb bad';
        fb.textContent='❌ Ainda não. Dica: ' + (q.hint || 'tente de novo.');
        fb.style.display='block';

        // incrementa tentativas
        post('../runner/save_progress.php', {
          id_atividade: <?= (int)$id_atividade ?>,
          matricula: <?= (int)$matricula ?>,
          status: 'INCOMPLETO',
          tentativas_inc: 1
        });
      }
    };

    function finish(){
      var totalMs = (new Date()).getTime() - startedAt;
      var totalSec = Math.round(totalMs/1000);

      post('../runner/save_progress.php', {
        id_atividade: <?= (int)$id_atividade ?>,
        matricula: <?= (int)$matricula ?>,
        status: 'COMPLETO',
        tempo_segundos: totalSec,
        pontos: pontos
      }, function(){
        location.href = <?= json_encode("../runner/atividade.php?id_atividade=".$id_atividade."&step=fim") ?> +
          "&pontos=" + encodeURIComponent(pontos) + "&tempo=" + encodeURIComponent(totalSec);
      });
    }

    render();
  })();
  </script>
  <?php
  piths_footer();
  exit;
}

if ($step === 'fim') {
  $pontos = (int)($_GET['pontos'] ?? 0);
  $tempo  = (int)($_GET['tempo'] ?? 0);
  $pct = pct_step($totalSteps, $totalSteps);

  $min = floor($tempo/60);
  $sec = $tempo % 60;
  $tempoFmt = $min > 0 ? ($min."m ".$sec."s") : ($sec."s");

  piths_head("Atividade - Concluída");
  piths_navbar();
  ?>
  <div class="container py-4">
    <div class="piths-glass p-5 text-center">
      <div class="progress mb-4" style="height:14px;">
        <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
      </div>

      <h1 class="display-4 fw-bold">Parabéns! 🎉</h1>
      <p class="fs-2 mb-1">Você ganhou <b><?= (int)$pontos ?></b> pontos!</p>
      <p class="text-muted">⏱️ Tempo total: <?= htmlspecialchars($tempoFmt) ?></p>

      <div class="mt-4">
        <a class="btn btn-primary btn-fun" href="<?= htmlspecialchars($backUrl) ?>">Voltar para atividades</a>
      </div>
      <div class="small text-muted mt-3">Você será redirecionado automaticamente.</div>
    </div>
  </div>

  <script>
    setTimeout(function(){ location.href = <?= json_encode($backUrl) ?>; }, 2500);
  </script>
  <?php
  piths_footer();
  exit;
}