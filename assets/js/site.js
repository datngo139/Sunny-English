/* Sunny Education — hành vi trang công khai (không phụ thuộc thư viện) */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

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

  var data = window.SUNNY || { courses: [], slots: {} };
  var form = $('#reg-form');

  function gotoRegister(courseId) {
    var sel = $('#f-course');
    if (sel && courseId) sel.value = courseId;
    var sec = $('#dang-ky');
    if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    setTimeout(function () { var f = $('#f-parent'); if (f) f.focus({ preventScroll: true }); }, 450);
  }

  /* Nút "Đăng ký / Đặt lịch" trên trang chủ: cuộn mượt + focus vào ô đầu tiên */
  $$('[data-open-register]').forEach(function (a) {
    a.addEventListener('click', function (e) { if ($('#dang-ky')) { e.preventDefault(); gotoRegister(); } });
  });

  /* Lọc khoá học theo độ tuổi */
  var tabs = $$('#age-tabs .tab');
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      tabs.forEach(function (x) { x.setAttribute('aria-selected', x === t ? 'true' : 'false'); });
      var f = t.getAttribute('data-filter');
      $$('#course-grid .course-card').forEach(function (c) {
        var id = c.getAttribute('data-course');
        c.hidden = !(f === '' || id === f || id === 'luyenthi');
      });
    });
  });

  /* Chi tiết khoá học */
  var cd = $('#course-dialog');
  function showCourse(id) {
    var c = data.courses.filter(function (x) { return x.id === id; })[0];
    if (!c || !cd) return;
    $('#cd-badge').textContent = c.title;
    $('#cd-ages').textContent = c.ages;
    $('#cd-title').textContent = c.headline;
    $('#cd-outcome').textContent = c.outcome;
    var ul = $('#cd-points'); ul.innerHTML = '';
    c.points.forEach(function (p) {
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
      var sec = $('#khoa-hoc'); if (sec) sec.scrollIntoView({ behavior: 'smooth' });
      showCourse(a.getAttribute('data-strip-course'));
    });
  });
  var cdReg = $('#cd-register');
  if (cdReg) cdReg.addEventListener('click', function () { var id = cd.getAttribute('data-course'); closeDlg(cd); gotoRegister(id); });

  /* ---------- Form đăng ký học thử ---------- */
  if (!form) return;
  var errBox = $('#reg-error'), submit = $('#reg-submit'), bar = $('#reg-progress');
  var tracked = ['parent_name', 'phone', 'child_name', 'child_age', 'course'];

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
          $('#sd-text').textContent = 'Cảm ơn ' + parent + '. Sunny sẽ gọi đến số ' + phone + ' trong vòng 24 giờ để hẹn giờ test trình độ cho ' + child + '.';
          form.reset(); progress();
          openDlg($('#success-dialog'));
        } else {
          if (res.errors) Object.keys(res.errors).forEach(function (k) { setError(k, res.errors[k]); });
          errBox.textContent = res.message || 'Ba mẹ kiểm tra lại thông tin giúp Sunny nhé.';
        }
      })
      .catch(function () { errBox.textContent = 'Không gửi được, ba mẹ kiểm tra mạng rồi thử lại hoặc gọi Sunny trực tiếp.'; })
      .then(function () { busy = false; submit.classList.remove('is-loading'); submit.disabled = false; submit.textContent = label; });
  });
})();
