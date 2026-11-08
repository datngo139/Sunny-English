/* Sunny — hành vi trang quản trị: xác nhận xoá, đổi trạng thái, trình soạn bài, tải ảnh */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  /* Xác nhận trước khi xoá */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-confirm]');
    if (b && !window.confirm(b.getAttribute('data-confirm'))) e.preventDefault();
  });
  /* Đổi trạng thái là lưu ngay */
  $$('select[data-autosubmit]').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });

  var form = $('#post-form');
  if (!form) return;

  var editor = $('#editor'), bodyInput = $('#body-input');
  var uploadUrl = form.getAttribute('data-upload'), csrf = form.getAttribute('data-csrf');
  var dirty = false, submitting = false;
  var hint = $('#editor-hint'), hintDefault = hint ? hint.textContent : '';

  try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) { /* bỏ qua */ }
  if (!editor.innerHTML.trim()) editor.innerHTML = '';

  function say(msg, bad) {
    if (!hint) return;
    hint.textContent = msg || hintDefault;
    hint.style.color = bad ? 'var(--red-500)' : '';
    hint.style.fontWeight = bad ? '700' : '';
  }

  /* ---- Thanh công cụ ---- */
  var saved = null;
  function saveSel() { var s = window.getSelection(); if (s.rangeCount && editor.contains(s.anchorNode)) saved = s.getRangeAt(0).cloneRange(); }
  function restoreSel() { if (!saved) { editor.focus(); return; } editor.focus(); var s = window.getSelection(); s.removeAllRanges(); s.addRange(saved); }
  ['keyup', 'mouseup', 'input', 'focus'].forEach(function (ev) { editor.addEventListener(ev, saveSel); });

  function exec(cmd, val) { restoreSel(); document.execCommand(cmd, false, val || null); dirty = true; saveSel(); refreshToolbar(); }

  $$('.toolbar button').forEach(function (b) {
    b.addEventListener('mousedown', function (e) { e.preventDefault(); }); // giữ vùng chọn trong editor
    b.addEventListener('click', function () {
      var act = b.getAttribute('data-act');
      if (act === 'link') return doLink();
      if (act === 'image') { saveSel(); $('#img-input').click(); return; }
      var cmd = b.getAttribute('data-cmd'), val = b.getAttribute('data-val');
      if (cmd === 'formatBlock') {
        var cur = (document.queryCommandValue('formatBlock') || '').toLowerCase();
        if (val !== 'p' && cur === val) val = 'p'; // bấm lần nữa = bỏ định dạng
        exec('formatBlock', '<' + val + '>');
      } else exec(cmd);
    });
  });

  function refreshToolbar() {
    var block = (document.queryCommandValue('formatBlock') || '').toLowerCase();
    $$('.toolbar button').forEach(function (b) {
      var cmd = b.getAttribute('data-cmd'), val = b.getAttribute('data-val'), on = false;
      try {
        if (cmd === 'formatBlock') on = (block === val && val !== 'p');
        else if (cmd && cmd.indexOf('insert') !== 0 && cmd !== 'removeFormat') on = document.queryCommandState(cmd);
      } catch (e) { /* bỏ qua */ }
      b.classList.toggle('on', !!on);
    });
  }
  ['keyup', 'mouseup'].forEach(function (ev) { editor.addEventListener(ev, refreshToolbar); });

  function doLink() {
    restoreSel();
    var sel = window.getSelection();
    var existing = sel.anchorNode && sel.anchorNode.parentElement && sel.anchorNode.parentElement.closest('a');
    var url = window.prompt('Dán địa chỉ liên kết (ví dụ https://facebook.com/...)', existing ? existing.getAttribute('href') : 'https://');
    if (url === null) return;
    url = url.trim();
    if (!url || url === 'https://') { exec('unlink'); return; }
    if (!/^(https?:\/\/|mailto:|tel:|\/)/i.test(url)) url = 'https://' + url;
    if (sel.isCollapsed) { exec('insertHTML', '<a href="' + url.replace(/"/g, '&quot;') + '">' + url.replace(/</g, '&lt;') + '</a>'); }
    else exec('createLink', url);
  }

  /* Dán: chỉ lấy chữ thường để không kéo theo định dạng rác */
  editor.addEventListener('paste', function (e) {
    var cd = e.clipboardData || window.clipboardData; if (!cd) return;
    e.preventDefault();
    var text = cd.getData('text/plain') || '';
    var html = text.split(/\r?\n{2,}/).map(function (p) {
      return '<p>' + p.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\r?\n/g, '<br>') + '</p>';
    }).join('');
    document.execCommand('insertHTML', false, html); dirty = true;
  });
  /* Gõ vào ô trống: trình duyệt để chữ trần ở gốc → bọc vào <p> để định dạng khối (H2, danh sách) hoạt động đúng */
  function ensureParagraph() {
    var f = editor.firstChild;
    if (f && (f.nodeType === 3 || /^(strong|em|u|a|b|i|span|br)$/i.test(f.nodeName))) {
      document.execCommand('formatBlock', false, 'p');
    }
  }
  editor.addEventListener('input', function () { ensureParagraph(); dirty = true; });

  /* ---- Tải ảnh ---- */
  function upload(file) {
    return new Promise(function (resolve, reject) {
      if (!file) return reject(new Error('Chưa chọn ảnh.'));
      if (file.size > 6 * 1024 * 1024) return reject(new Error('Ảnh quá lớn (tối đa 6MB).'));
      var fd = new FormData(); fd.append('image', file); fd.append('_csrf', csrf);
      fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Máy chủ trả về lỗi.' }; }); })
        .then(function (j) { j.ok ? resolve(j) : reject(new Error(j.message || 'Tải ảnh thất bại.')); })
        .catch(function () { reject(new Error('Không tải được ảnh, kiểm tra mạng rồi thử lại.')); });
    });
  }

  var imgInput = $('#img-input');
  imgInput.addEventListener('change', function () {
    var f = imgInput.files[0]; imgInput.value = '';
    if (!f) return;
    editor.classList.add('uploading'); say('Đang tải ảnh lên…');
    upload(f).then(function (j) {
      var alt = (window.prompt('Mô tả ngắn cho ảnh (giúp Google hiểu ảnh, có thể bỏ trống):', '') || '').replace(/"/g, '&quot;').replace(/</g, '&lt;');
      restoreSel();
      document.execCommand('insertHTML', false, '<p><img src="' + j.url + '" alt="' + alt + '"></p>');
      dirty = true; say('');
    }).catch(function (err) { say(err.message, true); })
      .then(function () { editor.classList.remove('uploading'); });
  });

  /* Ảnh bìa */
  var coverInput = $('#cover-input'), coverFile = $('#cover-file'), preview = $('#cover-preview'), clearBtn = $('#cover-clear');
  $('#cover-pick').addEventListener('click', function () { coverFile.click(); });
  coverFile.addEventListener('change', function () {
    var f = coverFile.files[0]; coverFile.value = '';
    if (!f) return;
    preview.style.opacity = '.5';
    upload(f).then(function (j) {
      coverInput.value = j.path;
      preview.innerHTML = '<img alt="">'; preview.firstChild.src = j.url; preview.removeAttribute('data-empty');
      clearBtn.hidden = false; dirty = true;
    }).catch(function (err) { window.alert(err.message); })
      .then(function () { preview.style.opacity = ''; });
  });
  clearBtn.addEventListener('click', function () {
    coverInput.value = '';
    preview.innerHTML = '<span><i class="icon icon-image" aria-hidden="true"></i>Chưa có ảnh bìa</span>'; preview.setAttribute('data-empty', '1');
    clearBtn.hidden = true; dirty = true;
  });

  /* ---- Đường dẫn tự động theo tiêu đề ---- */
  var title = $('#title'), slug = $('#slug'), slugPrev = $('#slug-preview');
  var slugTouched = slug.value.trim() !== '';
  function slugify(s) {
    return s.toLowerCase().replace(/đ/g, 'd').normalize('NFD').replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80);
  }
  title.addEventListener('input', function () { if (!slugTouched) { slug.value = slugify(title.value); slugPrev.textContent = slug.value || '…'; } dirty = true; });
  slug.addEventListener('input', function () { slugTouched = slug.value.trim() !== ''; slugPrev.textContent = slugify(slug.value) || '…'; dirty = true; });
  $$('input, textarea', form).forEach(function (i) { i.addEventListener('change', function () { dirty = true; }); });

  /* ---- Lưu ---- */
  form.addEventListener('submit', function () { bodyInput.value = editor.innerHTML; submitting = true; });
  window.addEventListener('beforeunload', function (e) { if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; } });
  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') { e.preventDefault(); $('#save-btn').click(); }
  });
})();
