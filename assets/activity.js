/**
 * PITHS activity.js (v2)
 * - Mantém compatibilidade com o formato antigo (quiz tradicional):
 *   { prompt, options[], answer: Number, feedback, wrong, points }
 *
 * - Adiciona novos tipos de questão via qtype (opcional):
 *   1) qtype: "single" (padrão)  -> answer: Number
 *   2) qtype: "multi"            -> answer: Number[]  (múltipla escolha)
 *   3) qtype: "order"            -> answer: Number[]  (ordem correta pelos índices das options)
 *
 * Observações de UI:
 * - Continua usando os mesmos containers do seu HTML: #quiz-question, #quiz-options, #quiz-feedback
 * - Para "multi" e "order", aparece um botão "Confirmar" e opções ficam selecionáveis.
 * - Tudo em JS puro, sem libs, compatível com navegadores antigos (evita APIs modernas onde possível).
 */
(function(){
  function getBaseUrl(){
    var path = window.location.pathname;
    var idx = path.indexOf('/atividades/');
    if (idx === -1) return '';
    return window.location.origin + path.slice(0, idx);
  }

  function getActivityBase(){
    return window.location.href.replace(/[^/]+$/, '');
  }

  function getStepUrl(step){
    var base = getActivityBase();
    var params = new URLSearchParams(window.location.search || '');
    if (params.has('step')) params.set('step', step);
    var qs = params.toString();
    return base + step + '.html' + (qs ? '?' + qs : '');
  }

  function getProgressKey(){
    return 'piths_progress_' + window.location.pathname.replace(/[^/]+$/, '');
  }

  function loadProgress(){
    try { return JSON.parse(localStorage.getItem(getProgressKey()) || '{}'); }
    catch (e){ return {}; }
  }

  function saveProgress(data){
    localStorage.setItem(getProgressKey(), JSON.stringify(data));
  }

  function setProgress(p){
    var fill = document.querySelector('.progress .fill');
    if (fill) fill.style.width = Math.max(0, Math.min(100, p)) + '%';
  }

  function hideTopbar(){
    var progressWrap = document.querySelector('.progress-wrap');
    if (progressWrap) progressWrap.style.display = 'none';
    var back = document.querySelector('[data-back-panel]');
    if (back) back.style.display = 'none';
  }

  function bindGlobal(){
    var back = document.querySelector('[data-back-panel]');
    if (back){
      back.addEventListener('click', function(){
        var base = getBaseUrl();
        if (base) window.location.href = base + '/aluno/index.php';
        else window.history.back();
      });
    }
  }

  function enableButton(btn){
    if (!btn) return;
    btn.classList.remove('btn-disabled');
    btn.removeAttribute('disabled');
  }

  function disableButton(btn){
    if (!btn) return;
    btn.classList.add('btn-disabled');
    btn.setAttribute('disabled', 'disabled');
  }

  function markProgress(fields){
    var data = loadProgress();
    for (var k in fields){
      if (Object.prototype.hasOwnProperty.call(fields, k)) data[k] = fields[k];
    }
    saveProgress(data);
    return data;
  }

  // ---------- Video ----------
  function setupYouTubePlayer(nextBtn){
    var frame = document.getElementById('ytplayer');
    if (!frame) return;

    var videoId = frame.getAttribute('data-youtube-id') || '';
    if (!videoId) return;

    function onReady(){
      disableButton(nextBtn);
    }

    function onStateChange(event){
      if (!event) return;
      if (event.data === 0){
        enableButton(nextBtn);
        markProgress({ videoDone: true });
        return;
      }
      if (event.data === 2){
        // paused: allow if near the end
        try {
          var player = event.target;
          var duration = player.getDuration();
          var current = player.getCurrentTime();
          if (duration > 0 && current >= duration - 2){
            enableButton(nextBtn);
            markProgress({ videoDone: true });
          }
        } catch (e) {}
      }
    }

    if (window.YT && window.YT.Player){
      new window.YT.Player('ytplayer', { events: { 'onReady': onReady, 'onStateChange': onStateChange } });
      return;
    }

    window.onYouTubeIframeAPIReady = function(){
      new window.YT.Player('ytplayer', { events: { 'onReady': onReady, 'onStateChange': onStateChange } });
    };

    var tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(tag);
  }

  function setupVideo(){
    var nextBtn = document.querySelector('[data-next-video]');
    if (!nextBtn) return;

    setProgress(0);
    enableButton(nextBtn);

    nextBtn.addEventListener('click', function(){
      if (nextBtn.hasAttribute('disabled')) return;
      window.location.href = getStepUrl('texto');
    });

    // se houver ytplayer
    setupYouTubePlayer(nextBtn);
  }

  // ---------- Texto ----------
  function setupTexto(){
    var prevBtn = document.querySelector('[data-prev-texto]');
    var nextBtn = document.querySelector('[data-next-texto]');

    setProgress(25);

    if (prevBtn){
      prevBtn.addEventListener('click', function(){
        window.location.href = getStepUrl('video');
      });
    }
    if (nextBtn){
      nextBtn.addEventListener('click', function(){
        markProgress({ textoDone: true });
        window.location.href = getStepUrl('quiz');
      });
    }
  }

  // ---------- Utils Quiz ----------
  function normalizeQType(q){
    var t = (q && q.qtype) ? String(q.qtype).toLowerCase() : '';
    if (!t) t = 'single';
    if (t === 'quiz' || t === 'tradicional') t = 'single';
    return t;
  }

  function isArray(x){
    return Object.prototype.toString.call(x) === '[object Array]';
  }

  function uniqSortedNumbers(arr){
    var seen = {};
    var out = [];
    for (var i = 0; i < arr.length; i++){
      var n = arr[i];
      if (typeof n !== 'number') continue;
      if (seen[n]) continue;
      seen[n] = true;
      out.push(n);
    }
    out.sort(function(a,b){ return a-b; });
    return out;
  }

  function arraysEqual(a, b){
    if (!a || !b) return false;
    if (a.length !== b.length) return false;
    for (var i = 0; i < a.length; i++){
      if (a[i] !== b[i]) return false;
    }
    return true;
  }

  function shuffleIndices(n){
    // Fisher-Yates: retorna array [0..n-1] embaralhado
    var arr = [];
    for (var i = 0; i < n; i++) arr.push(i);
    for (var j = n - 1; j > 0; j--){
      var k = Math.floor(Math.random() * (j + 1));
      var tmp = arr[j]; arr[j] = arr[k]; arr[k] = tmp;
    }
    return arr;
  }

  function createEl(tag, className, text){
    var el = document.createElement(tag);
    if (className) el.className = className;
    if (typeof text === 'string') el.textContent = text;
    return el;
  }

  // ---------- Quiz ----------
  function setupQuiz(){
    var dataEl = document.getElementById('quiz-data');
    if (!dataEl) return;

    var questions = [];
    try { questions = JSON.parse(dataEl.textContent || '[]'); }
    catch(e){ questions = []; }

    // Clonar para não destruir a referência do JSON original (a gente faz splice/push)
    if (isArray(questions)) questions = questions.slice(0);
    else questions = [];

    var screenSelect = document.getElementById('quiz-select');
    var screenPlay = document.getElementById('quiz-play');
    var screenFinal = document.getElementById('quiz-final');
    var questionTitle = document.getElementById('quiz-question');
    var optionWrap = document.getElementById('quiz-options');
    var feedback = document.getElementById('quiz-feedback');
    var pointsEl = document.getElementById('quiz-points');
    var timeEl = document.getElementById('quiz-time');
    var startBtn = document.getElementById('quiz-start');
    var backBtn = document.getElementById('quiz-back');
    var exitBtn = document.getElementById('quiz-exit');

    var idx = 0;
    var points = 0;
    var startAt = 0;
    var lastTotalSec = 0;

    function show(screen){
      if (screenSelect) screenSelect.style.display = screen === 'select' ? 'block' : 'none';
      if (screenPlay) screenPlay.style.display = screen === 'play' ? 'block' : 'none';
      if (screenFinal) screenFinal.style.display = screen === 'final' ? 'block' : 'none';
    }

    function start(){
      idx = 0;
      points = 0;
      startAt = Date.now();
      show('play');
      render();
    }

    function backToSelect(){
      show('select');
      setProgress(50);
    }

    function moveWrongToEnd(){
      var wrong = questions.splice(idx, 1)[0];
      questions.push(wrong);
      if (idx >= questions.length){
        idx = Math.max(0, questions.length - 1);
      }
    }

    function okAdvance(){
      points += (questions[idx].points || 10);
      var base = 50, span = 50;
      setProgress(base + ((idx + 1) / questions.length) * span);

      setTimeout(function(){
        idx += 1;
        if (idx >= questions.length){
          finish();
        } else {
          render();
        }
      }, 650);
    }

    function showOk(msg){
      feedback.textContent = msg || 'Resposta correta!';
      feedback.className = 'feedback ok';
    }

    function showErr(msg){
      feedback.textContent = msg || 'Tente novamente.';
      feedback.className = 'feedback err';
    }

    function finish(){
      var totalMs = Date.now() - startAt;
      var totalSec = Math.max(1, Math.round(totalMs / 1000));
      lastTotalSec = totalSec;

      if (pointsEl) pointsEl.textContent = points;
      if (timeEl) timeEl.textContent = totalSec + 's';

      show('final');
      setProgress(100);
      markProgress({ quizDone: true });
      hideTopbar();
    }

    function render(){
      if (!questions.length) return;

      var q = questions[idx] || {};
      var qtype = normalizeQType(q);

      // progresso base
      var base = 50, span = 50;
      setProgress(base + ((idx) / questions.length) * span);

      // texto
      questionTitle.textContent = q.prompt || ('Questão ' + (idx + 1));
      optionWrap.innerHTML = '';
      feedback.textContent = '';
      feedback.className = 'feedback';

      // valida options
      var opts = isArray(q.options) ? q.options : [];
      if (!opts.length){
        optionWrap.appendChild(createEl('div', 'helper', 'Questão sem opções.'));
        return;
      }

      // Render por tipo
      if (qtype === 'multi'){
        renderMulti(q, opts);
        return;
      }
      if (qtype === 'order'){
        renderOrder(q, opts);
        return;
      }

      // default: single (compatível com seu JS original)
      renderSingle(q, opts);
    }

    function renderSingle(q, opts){
      var correct = (typeof q.answer === 'number') ? q.answer : 0;

      for (var i = 0; i < opts.length; i++){
        (function(i){
          var btn = document.createElement('button');
          btn.className = 'option';
          btn.textContent = String(opts[i]);

          btn.addEventListener('click', function(){
            if (i === correct){
              showOk(q.feedback || 'Resposta correta!');
              okAdvance();
            } else {
              showErr(q.wrong || 'Tente novamente.');
              setTimeout(function(){
                moveWrongToEnd();
                render();
              }, 550);
            }
          });

          optionWrap.appendChild(btn);
        })(i);
      }
    }

    function renderMulti(q, opts){
      // answer: [0,2,3]
      var ans = isArray(q.answer) ? q.answer : [];
      ans = uniqSortedNumbers(ans);

      // estado seleção
      var selected = {}; // idx->true
      var info = createEl('div', 'helper', 'Selecione todas as alternativas corretas e clique em Confirmar.');
      optionWrap.appendChild(info);

      // container de botões
      var btns = [];

      function refreshSelectedStyle(){
        for (var i = 0; i < btns.length; i++){
          var b = btns[i];
          var k = b.getAttribute('data-opt-idx');
          if (selected[k]){
            b.classList.add('selected');
            // reforço visual sem depender de CSS:
            b.style.outline = '2px solid rgba(0,0,0,0.25)';
          } else {
            b.classList.remove('selected');
            b.style.outline = '';
          }
        }
      }

      for (var i = 0; i < opts.length; i++){
        (function(i){
          var btn = document.createElement('button');
          btn.className = 'option';
          btn.textContent = String(opts[i]);
          btn.setAttribute('data-opt-idx', String(i));

          btn.addEventListener('click', function(){
            var key = String(i);
            if (selected[key]) delete selected[key];
            else selected[key] = true;
            refreshSelectedStyle();
          });

          btns.push(btn);
          optionWrap.appendChild(btn);
        })(i);
      }

      // Ações
      var actions = createEl('div', 'btn-row', '');
      actions.style.marginTop = '10px';

      var confirmBtn = createEl('button', 'btn-solid', 'Confirmar');
      var resetBtn = createEl('button', 'btn-ghost', 'Limpar');
      // se seu CSS não tiver btn-ghost, não tem problema:
      resetBtn.style.marginLeft = '8px';

      confirmBtn.addEventListener('click', function(){
        // construir array selecionado
        var picked = [];
        for (var k in selected){
          if (Object.prototype.hasOwnProperty.call(selected, k)){
            picked.push(parseInt(k, 10));
          }
        }
        picked = uniqSortedNumbers(picked);

        if (arraysEqual(picked, ans)){
          showOk(q.feedback || 'Resposta correta!');
          okAdvance();
        } else {
          showErr(q.wrong || 'Ainda não. Tente novamente.');
          setTimeout(function(){
            moveWrongToEnd();
            render();
          }, 650);
        }
      });

      resetBtn.addEventListener('click', function(){
        selected = {};
        refreshSelectedStyle();
        feedback.textContent = '';
        feedback.className = 'feedback';
      });

      actions.appendChild(confirmBtn);
      actions.appendChild(resetBtn);
      optionWrap.appendChild(actions);

      refreshSelectedStyle();
    }

    function renderOrder(q, opts){
      // answer: [2,0,1,3] -> ordem correta por índices das options originais
      var ans = isArray(q.answer) ? q.answer.slice(0) : [];
      // valida leve: se não veio, assume ordem original
      if (!ans.length){
        ans = [];
        for (var x = 0; x < opts.length; x++) ans.push(x);
      }

      // Para ficar mais interessante, embaralha a apresentação (mas a resposta continua pelos índices originais).
      var orderShown = shuffleIndices(opts.length);

      var picked = []; // sequência de índices originais escolhidos (não os embaralhados)
      var info = createEl('div', 'helper', 'Clique nas opções na ordem correta. Depois clique em Confirmar.');
      optionWrap.appendChild(info);

      // “painel” de sequência escolhida
      var seqBox = createEl('div', 'helper', 'Sua ordem: (vazia)');
      seqBox.style.marginTop = '6px';
      optionWrap.appendChild(seqBox);

      function updateSeqBox(){
        if (!picked.length){
          seqBox.textContent = 'Sua ordem: (vazia)';
          return;
        }
        var parts = [];
        for (var i = 0; i < picked.length; i++){
          var idxOrig = picked[i];
          parts.push((i + 1) + 'º ' + String(opts[idxOrig]));
        }
        seqBox.textContent = 'Sua ordem: ' + parts.join('  •  ');
      }

      // lista de opções clicáveis
      var btns = [];

      function refreshDisableIfPicked(){
        // desabilita itens já escolhidos (evita repetir)
        for (var i = 0; i < btns.length; i++){
          var btn = btns[i];
          var idxOrig = parseInt(btn.getAttribute('data-orig-idx'), 10);
          var already = false;
          for (var j = 0; j < picked.length; j++){
            if (picked[j] === idxOrig){ already = true; break; }
          }
          if (already){
            btn.setAttribute('disabled', 'disabled');
            btn.classList.add('btn-disabled');
            btn.style.opacity = '0.75';
          } else {
            btn.removeAttribute('disabled');
            btn.classList.remove('btn-disabled');
            btn.style.opacity = '';
          }
        }
      }

      for (var i = 0; i < orderShown.length; i++){
        (function(pos){
          var idxOrig = orderShown[pos];
          var btn = document.createElement('button');
          btn.className = 'option';
          btn.textContent = String(opts[idxOrig]);
          btn.setAttribute('data-orig-idx', String(idxOrig));

          btn.addEventListener('click', function(){
            // adiciona ao fim
            picked.push(idxOrig);
            updateSeqBox();
            refreshDisableIfPicked();
          });

          btns.push(btn);
          optionWrap.appendChild(btn);
        })(i);
      }

      // Ações
      var actions = createEl('div', 'btn-row', '');
      actions.style.marginTop = '10px';

      var confirmBtn = createEl('button', 'btn-solid', 'Confirmar');
      var undoBtn = createEl('button', 'btn-ghost', 'Desfazer');
      var resetBtn = createEl('button', 'btn-ghost', 'Limpar');

      undoBtn.style.marginLeft = '8px';
      resetBtn.style.marginLeft = '8px';

      function canConfirm(){
        // Só permite confirmar quando escolheu a mesma quantidade
        return picked.length === ans.length;
      }

      confirmBtn.addEventListener('click', function(){
        if (!canConfirm()){
          showErr('Escolha ' + ans.length + ' itens (você escolheu ' + picked.length + ').');
          return;
        }
        if (arraysEqual(picked, ans)){
          showOk(q.feedback || 'Ordem correta!');
          okAdvance();
        } else {
          showErr(q.wrong || 'Ordem incorreta. Tente novamente.');
          setTimeout(function(){
            moveWrongToEnd();
            render();
          }, 750);
        }
      });

      undoBtn.addEventListener('click', function(){
        if (!picked.length) return;
        picked.pop();
        updateSeqBox();
        refreshDisableIfPicked();
        feedback.textContent = '';
        feedback.className = 'feedback';
      });

      resetBtn.addEventListener('click', function(){
        picked = [];
        updateSeqBox();
        refreshDisableIfPicked();
        feedback.textContent = '';
        feedback.className = 'feedback';
      });

      actions.appendChild(confirmBtn);
      actions.appendChild(undoBtn);
      actions.appendChild(resetBtn);
      optionWrap.appendChild(actions);

      updateSeqBox();
      refreshDisableIfPicked();
    }

    // binds
    if (startBtn) startBtn.addEventListener('click', start);

    if (backBtn){
      backBtn.addEventListener('click', function(){
        window.location.href = getStepUrl('texto');
      });
    }

    if (exitBtn){
      exitBtn.addEventListener('click', function(){
        var base = getBaseUrl();
        if (!base) return;

        var body = new URLSearchParams();
        body.set('points', String(points));

        var params = new URLSearchParams(window.location.search);
        var idAtiv = params.get('id_atividade');
        if (idAtiv) body.set('id_atividade', idAtiv);
        if (lastTotalSec > 0) body.set('tempo', String(lastTotalSec));

        fetch(base + '/runner/finish.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString()
        }).then(function(res){
          return res.json().catch(function(){ return {}; });
        }).then(function(data){
          if (data && data.redirect){
            window.location.href = data.redirect;
            return;
          }
          window.location.href = base + '/aluno/index.php';
        }).catch(function(){
          window.location.href = base + '/aluno/index.php';
        });
      });
    }

    setProgress(50);
    show('select');
  }

  // ---------- Bootstrap ----------
  document.addEventListener('DOMContentLoaded', function(){
    bindGlobal();
    var page = document.body.getAttribute('data-page');
    if (page === 'video') setupVideo();
    if (page === 'texto') setupTexto();
    if (page === 'quiz') setupQuiz();
  });
})();