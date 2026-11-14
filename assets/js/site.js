/* Sunny Education — hành vi trang công khai (không phụ thuộc thư viện) */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Header: viền khi cuộn + menu mobile */
  var header = $('#site-header');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 4); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }
  var toggle = $('#nav-toggle'), nav = $('#site-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.addEventListener('click', function (e) { if (e.target.closest('a')) { nav.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); } });
  }

  /* Dialog helpers */
  function openDlg(d) { if (!d) return; if (d.showModal) { if (!d.open) d.showModal(); } else { d.setAttribute('open', ''); } }
  function closeDlg(d) { if (!d) return; if (d.close) d.close(); else d.removeAttribute('open'); }
  $$('dialog.dlg').forEach(function (d) {
    d.addEventListener('click', function (e) { if (e.target === d || e.target.closest('[data-close]')) closeDlg(d); });
  });

  /* Hiện dần + bắt đầu chạy hoạt hình khi cuộn tới */
  var revealEls = $$('[data-reveal]');
  if ('IntersectionObserver' in window && revealEls.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in-view'); io.unobserve(en.target); } });
    }, { threshold: 0.18 });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in-view'); });
  }

  var data = window.SUNNY || { courses: [], schedule: [] };
  var form = $('#reg-form');

  function gotoRegister(courseId) {
    var sel = $('#f-course');
    if (sel && courseId) sel.value = courseId;
    var sec = $('#dang-ky');
    if (sec) sec.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    setTimeout(function () { var f = $('#f-parent'); if (f) f.focus({ preventScroll: true }); }, 450);
  }
  $$('[data-open-register]').forEach(function (a) {
    a.addEventListener('click', function (e) { if ($('#dang-ky')) { e.preventDefault(); gotoRegister(); } });
  });

  /* ---------- Linh vật Sunny ---------- */
  var mascot = $('#mascot');
  if (mascot) {
    var svg = $('svg', mascot), pupils = $$('.m-pupil', mascot), say = $('#mSay'), sayTimer = null, jumpTimer = null;
    var phrases = ['Hello!', 'Chào con!', "Let's speak!", 'Con giỏi quá!', 'See you soon!', 'Hi there!'], pi = 0;
    var lastPt = null, raf = false;

    function lookAt(x, y) {
      var k = svg.getBoundingClientRect().width / 400 || 1;
      pupils.forEach(function (p) {
        var eye = p.parentNode.getBoundingClientRect();
        var dx = (x - (eye.left + eye.width / 2)) / k, dy = (y - (eye.top + eye.height / 2)) / k;
        var dist = Math.sqrt(dx * dx + dy * dy) || 1, m = Math.min(dist / 10, 7);
        p.style.transform = 'translate(' + (dx / dist * m).toFixed(2) + 'px,' + (dy / dist * m).toFixed(2) + 'px)';
      });
    }
    function follow(x, y) { lastPt = [x, y]; if (!raf) { raf = true; requestAnimationFrame(function () { raf = false; lookAt(lastPt[0], lastPt[1]); }); } }
    if (!reduceMotion) {
      window.addEventListener('pointermove', function (e) { follow(e.clientX, e.clientY); }, { passive: true });
      window.addEventListener('pointerdown', function (e) { follow(e.clientX, e.clientY); }, { passive: true });
    }
    function speak(text, ms) {
      say.textContent = text;
      mascot.classList.add('say');
      clearTimeout(sayTimer);
      sayTimer = setTimeout(function () { mascot.classList.remove('say'); }, ms || 2200);
    }
    mascot.addEventListener('click', function () {
      mascot.classList.remove('jump'); void mascot.offsetWidth; mascot.classList.add('jump');
      clearTimeout(jumpTimer); jumpTimer = setTimeout(function () { mascot.classList.remove('jump'); }, 900);
      speak(phrases[pi++ % phrases.length]);
    });
    // Lời chào đầu tiên khi trang vừa mở
    setTimeout(function () { speak('Hello!', 2600); }, 900);
  }

  /* ---------- Chi tiết khoá học ---------- */
  var cd = $('#course-dialog');
  function findCourse(id) { return data.courses.filter(function (x) { return x.id === id; })[0]; }
  function showCourse(id) {
    var c = findCourse(id);
    if (!c) return;
    if (!c.headline) { if (data.book) window.open(data.book, '_blank', 'noopener'); return; } // khoá không có nội dung chi tiết → mở Zalo đặt lịch
    $('#cd-badge').textContent = c.title;
    $('#cd-ages').textContent = c.ages; $('#cd-ages').hidden = !c.ages;
    $('#cd-title').textContent = c.headline;
    $('#cd-outcome').textContent = c.outcome; $('#cd-outcome').hidden = !c.outcome;
    var ul = $('#cd-points'); ul.innerHTML = '';
    (c.points || []).forEach(function (p) {
      var li = document.createElement('li');
      li.style.cssText = 'display:flex;gap:12px;align-items:flex-start;font-weight:700;color:var(--ink-900);line-height:1.45';
      var tick = document.createElement('span');
      tick.innerHTML = '<i class="icon icon-check" aria-hidden="true"></i>';
      tick.style.cssText = 'flex:none;width:28px;height:28px;border-radius:999px;background:var(--green-500);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px';
      var tx = document.createElement('span'); tx.textContent = p;
      li.appendChild(tick); li.appendChild(tx); ul.appendChild(li);
    });
    cd.setAttribute('data-course', id);
    openDlg(cd);
  }
  $$('#course-grid .course-card').forEach(function (c) {
    c.addEventListener('click', function () { showCourse(c.getAttribute('data-course')); });
  });
  $$('[data-strip-course]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var sec = $('#khoa-hoc'); if (sec) sec.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
      showCourse(a.getAttribute('data-strip-course'));
    });
  });
  var cdReg = $('#cd-register');
  if (cdReg) cdReg.addEventListener('click', function () { var id = cd.getAttribute('data-course'); closeDlg(cd); gotoRegister(id); });

  /* ---------- Băng chuyền ảnh cơ sở vật chất (tự chạy) ---------- */
  var car = $('#facility');
  if (car) {
    var track = $('#facility-track'), slides = $$('.car-slide', car), dots = $$('.car-dots button', car), idx = 0, timer = null, paused = false, visible = false;
    var width = function () { return track.clientWidth; };
    function goTo(i, smooth) {
      idx = (i + slides.length) % slides.length;
      track.scrollTo({ left: idx * width(), behavior: smooth === false || reduceMotion ? 'auto' : 'smooth' });
      mark();
    }
    function mark() { dots.forEach(function (d, i) { d.setAttribute('aria-selected', i === idx ? 'true' : 'false'); }); }
    track.addEventListener('scroll', function () {
      var i = Math.round(track.scrollLeft / (width() || 1));
      if (i !== idx && i >= 0 && i < slides.length) { idx = i; mark(); }
    }, { passive: true });
    $('.car-btn.prev', car).addEventListener('click', function () { goTo(idx - 1); restart(); });
    $('.car-btn.next', car).addEventListener('click', function () { goTo(idx + 1); restart(); });
    dots.forEach(function (d, i) { d.addEventListener('click', function () { goTo(i); restart(); }); });
    track.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); goTo(idx + 1); restart(); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); goTo(idx - 1); restart(); }
    });
    function tick() { if (!paused && visible && !document.hidden) goTo(idx + 1); }
    function restart() { clearInterval(timer); if (!reduceMotion) timer = setInterval(tick, 4500); }
    ['mouseenter', 'focusin', 'touchstart'].forEach(function (ev) { car.addEventListener(ev, function () { paused = true; }, { passive: true }); });
    ['mouseleave', 'focusout', 'touchend'].forEach(function (ev) { car.addEventListener(ev, function () { paused = false; }, { passive: true }); });
    window.addEventListener('resize', function () { goTo(idx, false); });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en) { visible = en[0].isIntersecting; }, { threshold: 0.35 }).observe(car);
    } else { visible = true; }
    restart();
  }

  /* ---------- Icon nền mục cơ sở vật chất: xê dịch nhẹ theo con trỏ để có chiều sâu ---------- */
  var fxSec = $('#co-so');
  if (fxSec && !reduceMotion && window.matchMedia('(pointer: fine)').matches) {
    var fxRaf = false, fxPt = null;
    fxSec.addEventListener('pointermove', function (e) {
      fxPt = e; if (fxRaf) return; fxRaf = true;
      requestAnimationFrame(function () {
        fxRaf = false; var r = fxSec.getBoundingClientRect();
        fxSec.style.setProperty('--px', ((fxPt.clientX - r.left) / r.width - 0.5).toFixed(3));
        fxSec.style.setProperty('--py', ((fxPt.clientY - r.top) / r.height - 0.5).toFixed(3));
      });
    }, { passive: true });
    fxSec.addEventListener('pointerleave', function () { fxSec.style.setProperty('--px', 0); fxSec.style.setProperty('--py', 0); });
  }

  /* ---------- Phóng to ảnh trong bài viết ---------- */
  var proseImgs = $$('.prose img');
  if (proseImgs.length) {
    var lb = document.createElement('dialog');
    lb.className = 'lightbox';
    lb.innerHTML = '<div class="lb-wrap"><button class="dlg-close" type="button" aria-label="Đóng">×</button><img alt=""></div>';
    document.body.appendChild(lb);
    var lbImg = $('img', lb);
    lb.addEventListener('click', function () { closeDlg(lb); });
    proseImgs.forEach(function (im) {
      im.addEventListener('click', function () { lbImg.src = im.currentSrc || im.src; lbImg.alt = im.alt || ''; openDlg(lb); });
    });
  }

  /* ---------- Form đăng ký học thử ---------- */
  if (!form) return;
  var errBox = $('#reg-error'), submit = $('#reg-submit'), bar = $('#reg-progress');
  var tracked = ['parent_name', 'phone', 'child_name', 'child_age', 'course', 'visit_time'];

  /* Chọn ngày + giờ */
  var dateBox = $('#date-chips'), timeBox = $('#time-chips'), pick = $('#sched-pick');
  var chosenDate = '', chosenTime = '', days = data.schedule || [];
  function chip(label, sub, checked, onClick) {
    var b = document.createElement('button');
    b.type = 'button'; b.className = 'chip'; b.setAttribute('role', 'radio'); b.setAttribute('aria-checked', checked ? 'true' : 'false');
    if (label) { var l = document.createElement('b'); l.textContent = label; b.appendChild(l); }
    var s = document.createElement('span'); s.textContent = sub; b.appendChild(s);
    b.addEventListener('click', onClick);
    return b;
  }
  function renderDates() {
    if (!dateBox) return;
    dateBox.innerHTML = '';
    days.forEach(function (d) {
      dateBox.appendChild(chip(d.wd, d.dm, d.date === chosenDate, function () {
        chosenDate = (chosenDate === d.date) ? '' : d.date; chosenTime = ''; sync(); renderDates(); renderTimes();
      }));
    });
  }
  function renderTimes() {
    if (!timeBox) return;
    timeBox.innerHTML = '';
    var d = days.filter(function (x) { return x.date === chosenDate; })[0];
    timeBox.hidden = !d;
    if (!d) return;
    d.slots.forEach(function (s) {
      timeBox.appendChild(chip('', s.t.replace(/^0/, ''), s.t === chosenTime, function () {
        chosenTime = (chosenTime === s.t) ? '' : s.t; sync(); renderTimes();
      }));
    });
  }
  function sync() {
    form.elements.visit_date.value = chosenDate;
    form.elements.visit_time.value = chosenTime;
    var d = days.filter(function (x) { return x.date === chosenDate; })[0];
    if (pick) pick.textContent = (d && chosenTime) ? 'Ba mẹ chọn: ' + d.full + ' lúc ' + chosenTime.replace(/^0/, '') + '. Sunny sẽ gọi xác nhận lại.' : (d ? 'Chọn thêm giờ ở bên dưới.' : '');
    progress();
  }
  renderDates();

  function fieldEl(name) { return $('[data-field="' + name + '"]', form); }
  function setError(name, msg) {
    var f = fieldEl(name); if (!f) return;
    f.classList.toggle('has-error', !!msg);
    var t = $('.error-text', f); if (t) t.textContent = msg || '';
  }
  function clearErrors() { $$('[data-field]', form).forEach(function (f) { f.classList.remove('has-error'); var t = $('.error-text', f); if (t) t.textContent = ''; }); errBox.textContent = ''; }
  function progress() {
    var n = tracked.filter(function (k) { var el = form.elements[k]; return el && String(el.value).trim() !== ''; }).length;
    bar.style.width = Math.max(4, (n / tracked.length) * 100) + '%';
  }
  form.addEventListener('input', progress); form.addEventListener('change', progress); progress();

  function validPhone(raw) {
    var p = raw.replace(/[\s.\-()]+/g, '');
    if (p.indexOf('+84') === 0) p = '0' + p.slice(3);
    else if (/^84\d{9}$/.test(p)) p = '0' + p.slice(2);
    return /^0\d{9}$/.test(p);
  }
  function validate() {
    clearErrors();
    var ok = true;
    if (form.elements.parent_name.value.trim().length < 2) { setError('parent_name', 'Vui lòng nhập họ tên ba mẹ'); ok = false; }
    if (!validPhone(form.elements.phone.value)) { setError('phone', 'Số điện thoại gồm 10 chữ số, bắt đầu bằng 0'); ok = false; }
    var age = form.elements.child_age.value;
    if (age !== '' && (isNaN(age) || age < 2 || age > 18)) { setError('child_age', 'Tuổi từ 2 đến 18'); ok = false; }
    if (!form.elements.consent.checked) { setError('consent', 'Vui lòng đồng ý để Sunny liên hệ tư vấn'); ok = false; }
    if (chosenDate && !chosenTime) { errBox.textContent = 'Ba mẹ chọn thêm giờ học thử, hoặc bấm lại ngày đã chọn để bỏ chọn nhé.'; ok = false; }
    if (!ok) { var first = $('.has-error input, .has-error select', form); if (first) first.focus(); }
    return ok;
  }

  var busy = false;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (busy || !validate()) return;
    busy = true; submit.classList.add('is-loading'); submit.disabled = true;
    var label = submit.textContent; submit.textContent = 'Đang gửi…';

    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'fetch' }, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Có lỗi xảy ra, ba mẹ thử lại giúp Sunny nhé.' }; }); })
      .then(function (res) {
        if (res.ok) {
          var parent = form.elements.parent_name.value.trim() || 'ba mẹ';
          var child = form.elements.child_name.value.trim() || 'con';
          var phone = form.elements.phone.value.trim();
          var d = days.filter(function (x) { return x.date === chosenDate; })[0];
          var when = (d && chosenTime) ? ' Ba mẹ đã chọn ' + d.full + ' lúc ' + chosenTime.replace(/^0/, '') + ', Sunny sẽ xác nhận lại khi gọi.' : '';
          $('#sd-text').textContent = 'Cảm ơn ' + parent + '. Sunny sẽ gọi đến số ' + phone + ' trong vòng 24 giờ để hẹn giờ test trình độ cho ' + child + '.' + when;
          form.reset(); chosenDate = ''; chosenTime = ''; sync(); renderDates(); renderTimes();
          openDlg($('#success-dialog'));
        } else {
          if (res.errors) Object.keys(res.errors).forEach(function (k) { setError(k, res.errors[k]); });
          errBox.textContent = res.message || 'Ba mẹ kiểm tra lại thông tin giúp Sunny nhé.';
          if (res.refresh) { days = res.schedule || []; chosenDate = ''; chosenTime = ''; sync(); renderDates(); renderTimes(); }
        }
      })
      .catch(function () { errBox.textContent = 'Không gửi được, ba mẹ kiểm tra mạng rồi thử lại hoặc gọi Sunny trực tiếp.'; })
      .then(function () { busy = false; submit.classList.remove('is-loading'); submit.disabled = false; submit.textContent = label; });
  });
})();
