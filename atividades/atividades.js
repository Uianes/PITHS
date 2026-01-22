(function () {
  // Config do fluxo: Video (1) + Texto (1) + Questões (N) + Fim (1)
  // A progressbar mostrará progresso total ao longo do fluxo.
  // Aqui a página de QUESTÕES calcula seu trecho com base no índice atual.

  function $(id) { return document.getElementById(id); }

  function nowMs() { return (new Date()).getTime(); }

  // IE11 friendly
  function xhrPost(url, dataObj, cb) {
    try {
      var xhr = new XMLHttpRequest();
      xhr.open("POST", url, true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) cb && cb(xhr.status, xhr.responseText);
      };
      xhr.send(toForm(dataObj));
    } catch (e) {
      cb && cb(0, "");
    }
  }

  function toForm(obj) {
    var s = [];
    for (var k in obj) if (obj.hasOwnProperty(k)) {
      s.push(encodeURIComponent(k) + "=" + encodeURIComponent(String(obj[k])));
    }
    return s.join("&");
  }

  function clamp(n, a, b) { return Math.max(a, Math.min(b, n)); }

  // ========= API pública por página =========

  window.PITHS = window.PITHS || {};

  // Usado nas páginas Video/Texto para setar a progress
  window.PITHS.setProgress = function (pct) {
    var bar = $("progressBar");
    if (bar) bar.style.width = clamp(pct, 0, 100) + "%";
  };

  // ========= Página de Questões =========

  window.PITHS.initQuestoes = function (cfg) {
    // cfg: { atividadeId, alunoId, totalPrefixSteps(=2), backUrl, finishUrl, saveUrl, pointsPerHit, questions: [] }

    var activityId = cfg.atividadeId || 0;
    var alunoId = cfg.alunoId || 0;
    var saveUrl = cfg.saveUrl || "save_progress.php";
    var finishUrl = cfg.finishUrl || "atividade_fim.php";
    var backUrl = cfg.backUrl || "../../aluno/minhas_atividades.php";

    var prefix = cfg.totalPrefixSteps || 2; // video+texto
    var questions = cfg.questions || [];
    var pointsPerHit = cfg.pointsPerHit || 10;

    var totalQuestions = questions.length;

    // estado
    var idx = 0;
    var selected = null;
    var startedAt = null; // ms
    var totalPoints = 0;

    // elementos
    var numEl = $("qNumber");
    var btnA = $("btnA"), btnB = $("btnB"), btnC = $("btnC"), btnD = $("btnD");
    var fb = $("feedback");
    var nextBtn = $("nextBtn");

    function setProgressForQuestion(i) {
      // etapas totais = video(1) + texto(1) + questoes(N) + fim(1)
      // durante questões, progresso = (prefix + i) / (prefix + N + 1)  (fim ainda não)
      var totalSteps = prefix + totalQuestions + 1;
      var doneSteps = prefix + i; // i = quantas questões já concluídas
      var pct = Math.round((doneSteps / totalSteps) * 100);
      window.PITHS.setProgress(pct);
    }

    function render() {
      fb.style.display = "none";
      nextBtn.disabled = true;
      selected = null;

      // início do tempo total (na primeira render)
      if (startedAt === null) startedAt = nowMs();

      if (idx >= totalQuestions) {
        finish();
        return;
      }

      var q = questions[idx];
      numEl.innerHTML = escapeHtml(q.value);

      // ordem fixa que você escolheu (Natural, Irracional, Inteiro, Racional)
      // mas permite que cfg já traga nesse formato.
      btnA.innerHTML = escapeHtml(q.options[0]);
      btnB.innerHTML = escapeHtml(q.options[1]);
      btnC.innerHTML = escapeHtml(q.options[2]);
      btnD.innerHTML = escapeHtml(q.options[3]);

      clearSelected();
      setProgressForQuestion(idx);
    }

    function clearSelected() {
      btnA.className = "answer-btn";
      btnB.className = "answer-btn";
      btnC.className = "answer-btn";
      btnD.className = "answer-btn";
    }

    function pick(btn, val) {
      selected = val;
      clearSelected();
      btn.className = "answer-btn selected";
      nextBtn.disabled = false;
      fb.style.display = "none";
    }

    btnA.onclick = function(){ pick(btnA, btnA.innerHTML); };
    btnB.onclick = function(){ pick(btnB, btnB.innerHTML); };
    btnC.onclick = function(){ pick(btnC, btnC.innerHTML); };
    btnD.onclick = function(){ pick(btnD, btnD.innerHTML); };

    nextBtn.onclick = function () {
      if (selected === null) return;

      var q = questions[idx];
      var ok = (normalize(selected) === normalize(q.answer));

      if (ok) {
        totalPoints += pointsPerHit;
        showFeedback(true, "✅ Certo! Vamos para a próxima.");
        // salva parcial (opcional)
        xhrPost(saveUrl, {
          aluno_id: alunoId,
          atividade_id: activityId,
          status: 1, // em andamento
          acertos: (idx + 1),
          total: totalQuestions,
          pontos_parciais: totalPoints
        }, function(){});

        // avança com pequeno delay (parece app)
        setTimeout(function(){
          idx++;
          render();
        }, 450);

      } else {
        showFeedback(false, "❌ Ainda não. Dica: " + (q.hint || "tente lembrar a definição."));
        // não avança
      }
    };

    function finish() {
      var totalTimeMs = nowMs() - startedAt;
      var totalSec = Math.round(totalTimeMs / 1000);

      // status concluído + tempo + pontos
      xhrPost(saveUrl, {
        aluno_id: alunoId,
        atividade_id: activityId,
        status: 2, // concluído
        tempo_segundos: totalSec,
        pontos: totalPoints
      }, function () {
        // vai para tela final
        // passa dados por querystring para mostrar
        var url = finishUrl +
          "?atividade_id=" + encodeURIComponent(activityId) +
          "&pontos=" + encodeURIComponent(totalPoints) +
          "&tempo=" + encodeURIComponent(totalSec) +
          "&back=" + encodeURIComponent(backUrl);
        window.location.href = url;
      });
    }

    function showFeedback(ok, msg) {
      fb.className = "feedback " + (ok ? "ok" : "bad");
      fb.innerHTML = escapeHtml(msg);
      fb.style.display = "block";
    }

    function escapeHtml(s) {
      s = String(s);
      return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");
    }

    function normalize(s) {
      s = String(s).toLowerCase();
      s = s.replace(/\s+/g, " ").replace(/^\s+|\s+$/g, "");
      return s;
    }

    // seta do topo volta para seleção
    var backBtn = $("backBtn");
    if (backBtn) backBtn.href = backUrl;

    render();
  };

})();