/* Sunny World — tương tác cho bé. Tiến độ chỉ lưu trên máy (localStorage), không gửi về máy chủ. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var KEY = 'sunnyWorld.v1';

  /* ---------- Tiến độ của bé ---------- */
  function load() {
    try {
      var s = JSON.parse(localStorage.getItem(KEY) || '{}');
      return { stars: +s.stars || 0, known: s.known || {}, read: s.read || {} };
    } catch (e) { return { stars: 0, known: {}, read: {} }; }
  }
  var state = load();
  function save() { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { /* chế độ riêng tư: bỏ qua */ } }
  var starEl = $('#sw-stars');
  function showStars(bump) {
    if (!starEl) return;
    starEl.textContent = state.stars;
    if (bump) { var box = starEl.parentNode; box.classList.remove('bump'); void box.offsetWidth; box.classList.add('bump'); }
  }
  function addStars(n) { state.stars += n; save(); showStars(true); }
  showStars(false);

  /* ---------- Đọc to bằng giọng của trình duyệt ---------- */
  var synth = window.speechSynthesis, voice = null;
  if (!synth || typeof SpeechSynthesisUtterance === 'undefined') document.documentElement.classList.add('no-tts');
  function pickVoice() {
    if (!synth) return;
    var vs = synth.getVoices() || [];
    voice = vs.filter(function (v) { return /^en[-_]US/i.test(v.lang); })[0] || vs.filter(function (v) { return /^en/i.test(v.lang); })[0] || null;
  }
  if (synth) { pickVoice(); if ('onvoiceschanged' in synth) synth.onvoiceschanged = pickVoice; }
  function speak(text) {
    if (!synth || !text) return;
    try {
      synth.cancel();
      var u = new SpeechSynthesisUtterance(text);
      u.lang = 'en-US'; u.rate = 0.85; if (voice) u.voice = voice;
      synth.speak(u);
    } catch (e) { /* bỏ qua */ }
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-say]');
    if (b) speak(b.getAttribute('data-say'));
  });

  /* ---------- Vườn Từ vựng: tiến độ từng chủ đề ---------- */
  $$('.tp[data-cards]').forEach(function (t) {
    var ids = t.getAttribute('data-cards').split(',').filter(Boolean);
    var k = ids.filter(function (id) { return state.known[id]; }).length;
    $('.tp-known', t).textContent = k;
    $('.tp-bar i', t).style.width = (ids.length ? k / ids.length * 100 : 0) + '%';
  });

  /* ---------- Thẻ từ ---------- */
  var cards = window.SW_CARDS, fc = $('#fc-card');
  if (cards && fc) {
    var i = 0, thumbs = $$('.fc-thumb'), knownBtn = $('#fc-known'), done = $('#fc-done');
    var render = function () {
      var c = cards[i];
      fc.classList.remove('flipped');
      $('#fc-pic').innerHTML = $('.pic', thumbs[i]).innerHTML; // hình do máy chủ vẽ sẵn trong lưới bên dưới
      $('#fc-word').textContent = c.word;
      $('#fc-mean').textContent = c.meaning;
      $('#fc-ex').textContent = c.ex;
      $('#fc-exvi').textContent = c.exVi;
      $('#fc-count').textContent = (i + 1) + ' / ' + cards.length;
      thumbs.forEach(function (t, n) { t.classList.toggle('cur', n === i); t.classList.toggle('known', !!state.known[cards[n].id]); });
      var k = !!state.known[c.id];
      knownBtn.classList.toggle('is-known', k);
      $('span', knownBtn).textContent = k ? 'Đã thuộc' : 'Con thuộc rồi!';
      done.hidden = !cards.every(function (x) { return state.known[x.id]; });
    };
    var go = function (n) { i = (n + cards.length) % cards.length; render(); };
    var flip = function () { fc.classList.toggle('flipped'); };
    fc.addEventListener('click', flip);
    fc.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); flip(); } });
    $('#fc-prev').addEventListener('click', function () { go(i - 1); });
    $('#fc-next').addEventListener('click', function () { go(i + 1); });
    $('#fc-say').addEventListener('click', function () { speak(fc.classList.contains('flipped') && cards[i].ex ? cards[i].ex : cards[i].word); });
    thumbs.forEach(function (t, n) { t.addEventListener('click', function () { go(n); speak(cards[n].word); window.scrollTo({ top: 0, behavior: 'smooth' }); }); });
    knownBtn.addEventListener('click', function () {
      var id = cards[i].id;
      if (state.known[id]) { delete state.known[id]; save(); render(); return; }
      state.known[id] = 1; addStars(1); render();
      if (i < cards.length - 1) setTimeout(function () { go(i + 1); }, 450);
    });
    document.addEventListener('keydown', function (e) {
      if (e.target.closest('input,textarea')) return;
      if (e.key === 'ArrowRight') go(i + 1);
      if (e.key === 'ArrowLeft') go(i - 1);
    });
    render();
  }

  /* ---------- Kệ truyện: đánh dấu truyện đã đọc ---------- */
  $$('.bk[data-story]').forEach(function (b) { if (state.read[b.getAttribute('data-story')]) $('.bk-read', b).hidden = false; });

  /* ---------- Trang đọc truyện ---------- */
  var reader = $('#reader');
  if (reader) {
    var total = +reader.getAttribute('data-pages'), sid = reader.getAttribute('data-story');
    var pg = 0, showVi = false, pages = $$('.pg', reader);
    var prev = $('#rd-prev'), next = $('#rd-next'), sayBtn = $('#rd-say'), viBtn = $('#rd-vi'), count = $('#rd-count'), bar = $('#rd-bar');
    var tip = $('#sw-tip'), tipFor = null;
    var hideTip = function () { tip.hidden = true; if (tipFor) tipFor.classList.remove('on'); tipFor = null; };
    var show = function (n) {
      pg = Math.max(0, Math.min(total + 1, n));
      hideTip(); if (synth) synth.cancel();
      pages.forEach(function (p) { p.hidden = +p.getAttribute('data-pg') !== pg; });
      var isText = pg >= 1 && pg <= total;
      bar.hidden = pg === 0;
      prev.disabled = pg <= 1;
      next.disabled = pg > total;
      sayBtn.disabled = viBtn.disabled = !isText;
      count.textContent = isText ? pg + ' / ' + total : '';
      $$('.pg .vi', reader).forEach(function (v) { v.hidden = !showVi; });
      if (pg === total + 1) {
        var first = !state.read[sid];
        if (first) { state.read[sid] = 1; addStars(3); }
        $('#end-star').textContent = first ? 'Con được thưởng 3 ngôi sao!' : 'Con đã đọc truyện này rồi. Đọc lại để nhớ lâu hơn nhé!';
      }
      reader.scrollIntoView({ block: 'nearest' });
    };
    reader.addEventListener('click', function (e) { var g = e.target.closest('[data-go]'); if (g) show(+g.getAttribute('data-go')); });
    prev.addEventListener('click', function () { show(pg - 1); });
    next.addEventListener('click', function () { show(pg + 1); });
    sayBtn.addEventListener('click', function () { var p = $('.pg:not([hidden]) .en', reader); if (p) speak(p.textContent); });
    viBtn.addEventListener('click', function () { showVi = !showVi; viBtn.setAttribute('aria-pressed', showVi ? 'true' : 'false'); $$('.pg .vi', reader).forEach(function (v) { v.hidden = !showVi; }); });
    document.addEventListener('keydown', function (e) {
      if (pg === 0) return;
      if (e.key === 'ArrowRight' && !next.disabled) show(pg + 1);
      if (e.key === 'ArrowLeft' && !prev.disabled) show(pg - 1);
      if (e.key === 'Escape') hideTip();
    });

    /* Nghĩa của từng từ: rê chuột, chạm hoặc dùng phím Tab */
    var showTip = function (w) {
      var m = w.getAttribute('data-m');
      if (!m) { hideTip(); return; }
      if (tipFor) tipFor.classList.remove('on');
      tipFor = w; w.classList.add('on');
      tip.textContent = m; tip.hidden = false;
      var r = w.getBoundingClientRect(), half = tip.offsetWidth / 2 + 8;
      tip.style.left = Math.max(half, Math.min(window.innerWidth - half, r.left + r.width / 2)) + 'px';
      tip.style.top = (r.top - 12) + 'px';
    };
    reader.addEventListener('mouseover', function (e) { var w = e.target.closest('.w'); if (w) showTip(w); });
    reader.addEventListener('mouseout', function (e) { if (e.target.closest('.w')) hideTip(); });
    reader.addEventListener('focusin', function (e) { var w = e.target.closest('.w'); if (w) showTip(w); });
    reader.addEventListener('click', function (e) { var w = e.target.closest('.w'); if (w) { showTip(w); speak(w.textContent); } });
    reader.addEventListener('keydown', function (e) { var w = e.target.closest('.w'); if (w && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); showTip(w); speak(w.textContent); } });
    document.addEventListener('click', function (e) { if (!e.target.closest('.w')) hideTip(); });
    window.addEventListener('scroll', hideTip, { passive: true });
    show(0);
  }

  /* ---------- Góc ba mẹ ---------- */
  var totals = window.SW_TOTALS;
  if (totals && $('#pa-words')) {
    var draw = function () {
      var words = 0, box = $('#pa-topics'); box.innerHTML = '';
      totals.topics.forEach(function (t) {
        var k = t.ids.filter(function (id) { return state.known[id]; }).length; words += k;
        var row = document.createElement('div'); row.className = 'pa-row';
        var name = document.createElement('span'); name.textContent = t.title;
        var barEl = document.createElement('span'); barEl.className = 'bar';
        var fill = document.createElement('i'); fill.style.width = (t.ids.length ? k / t.ids.length * 100 : 0) + '%'; barEl.appendChild(fill);
        var n = document.createElement('span'); n.className = 'n'; n.textContent = k + '/' + t.ids.length;
        row.appendChild(name); row.appendChild(barEl); row.appendChild(n); box.appendChild(row);
      });
      var list = $('#pa-read'); list.innerHTML = '';
      var read = totals.stories.filter(function (s) { return state.read[s.id]; });
      if (!read.length) { var li0 = document.createElement('li'); li0.className = 'none'; li0.textContent = 'Con chưa đọc xong truyện nào trên máy này.'; list.appendChild(li0); }
      read.forEach(function (s) { var li = document.createElement('li'); li.textContent = s.title; list.appendChild(li); });
      $('#pa-words').textContent = words;
      $('#pa-stories').textContent = read.length;
      $('#pa-stars').textContent = state.stars;
    };
    draw();
    $('#pa-reset').addEventListener('click', function () {
      if (!window.confirm('Xoá toàn bộ tiến độ và ngôi sao của con trên máy này?')) return;
      state = { stars: 0, known: {}, read: {} }; save(); showStars(false); draw();
    });
  }
})();
