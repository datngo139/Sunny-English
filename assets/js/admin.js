/* Sunny — hành vi trang quản trị: xác nhận xoá, trình soạn bài, tải ảnh (nhiều ảnh), ảnh khoá học, ngày nghỉ */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var MAX = 6 * 1024 * 1024, TYPES = /^image\/(jpeg|png|webp|gif)$/;

  /* Xác nhận trước khi xoá */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-confirm]');
    if (b && !window.confirm(b.getAttribute('data-confirm'))) e.preventDefault();
  });
  /* Đổi trạng thái là lưu ngay */
  $$('select[data-autosubmit]').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });

  /* ---- Tải một ảnh lên máy chủ (trả Promise {path, url}) ---- */
  function makeUploader(uploadUrl, csrf) {
    return function (file) {
      return new Promise(function (resolve, reject) {
        if (!file) return reject(new Error('Chưa chọn ảnh.'));
        if (!TYPES.test(file.type)) return reject(new Error('"' + file.name + '": chỉ nhận ảnh JPG, PNG, WebP hoặc GIF.'));
        if (file.size > MAX) return reject(new Error('"' + file.name + '": ảnh quá lớn (tối đa 6MB).'));
        var fd = new FormData(); fd.append('image', file); fd.append('_csrf', csrf);
        fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' }, credentials: 'same-origin' })
          .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Máy chủ trả về lỗi (mã ' + r.status + ').' }; }); })
          .then(function (j) { j.ok ? resolve(j) : reject(new Error(j.message || 'Tải ảnh thất bại.')); })
          .catch(function () { reject(new Error('Không tải được ảnh, kiểm tra mạng rồi thử lại.')); });
      });
    };
  }

  /* ---- Chọn / đổi / bỏ một ảnh (dùng cho ảnh bìa bài viết và ảnh khoá học) ---- */
  function imagePicker(upload, ids) {
    var input = $(ids.input), file = $(ids.file), preview = $(ids.preview), clearBtn = $(ids.clear), pickBtn = $(ids.pick);
    if (!input || !file || !preview || !pickBtn) return;
    pickBtn.addEventListener('click', function () { file.click(); });
    file.addEventListener('change', function () {
      var f = file.files[0]; file.value = '';
      if (!f) return;
      preview.style.opacity = '.5';
      upload(f).then(function (j) {
        input.value = j.path;
        preview.innerHTML = '<img alt="">'; preview.firstChild.src = j.url; preview.removeAttribute('data-empty');
        if (clearBtn) clearBtn.hidden = false;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }).catch(function (err) { window.alert(err.message); })
        .then(function () { preview.style.opacity = ''; });
    });
    if (clearBtn) clearBtn.addEventListener('click', function () {
      input.value = '';
      preview.innerHTML = '<span><i class="icon icon-image" aria-hidden="true"></i>Chưa có ảnh</span>'; preview.setAttribute('data-empty', '1');
      clearBtn.hidden = true;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }

  /* ---- Form khoá học ---- */
  var cform = $('#course-form');
  if (cform) {
    imagePicker(makeUploader(cform.getAttribute('data-upload'), cform.getAttribute('data-csrf')),
      { input: '#image-input', file: '#image-file', preview: '#image-preview', clear: '#image-clear', pick: '#image-pick' });
  }

  /* ---- Trang lịch: ngày nghỉ ---- */
  var blockedInput = $('#blocked-input');
  if (blockedInput) {
    var list = $('#blocked-list'), dateEl = $('#blocked-date');
    var dates = blockedInput.value.split(/\s+/).filter(Boolean).sort();
    var fmt = function (d) { var p = d.split('-'); return p[2] + '/' + p[1] + '/' + p[0]; };
    var render = function () {
      blockedInput.value = dates.join(' ');
      list.innerHTML = '';
      if (!dates.length) { list.innerHTML = '<span class="muted small">Chưa có ngày nghỉ nào.</span>'; return; }
      dates.forEach(function (d) {
        var c = document.createElement('span'); c.className = 'rchip'; c.textContent = fmt(d) + ' ';
        var x = document.createElement('button'); x.type = 'button'; x.setAttribute('aria-label', 'Bỏ ngày ' + fmt(d)); x.textContent = '×';
        x.addEventListener('click', function () { dates = dates.filter(function (v) { return v !== d; }); render(); });
        c.appendChild(x); list.appendChild(c);
      });
    };
    $('#blocked-add').addEventListener('click', function () {
      var v = dateEl.value;
      if (/^\d{4}-\d{2}-\d{2}$/.test(v) && dates.indexOf(v) < 0) { dates.push(v); dates.sort(); render(); }
      dateEl.value = '';
    });
    render();
  }

  /* ---- Form bài viết ---- */
  var form = $('#post-form');
  if (!form) return;

  var editor = $('#editor'), bodyInput = $('#body-input');
  var upload = makeUploader(form.getAttribute('data-upload'), form.getAttribute('data-csrf'));
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

  /* Thanh công cụ */
  var saved = null;
  function saveSel() { var s = window.getSelection(); if (s.rangeCount && editor.contains(s.anchorNode)) saved = s.getRangeAt(0).cloneRange(); }
  function restoreSel() { editor.focus(); if (!saved) return; var s = window.getSelection(); s.removeAllRanges(); s.addRange(saved); }
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

  /* Gõ vào ô trống: bọc chữ đầu tiên vào <p> để định dạng khối (H2, danh sách) hoạt động đúng */
  function ensureParagraph() {
    var f = editor.firstChild;
    if (f && (f.nodeType === 3 || /^(strong|em|u|a|b|i|span|br)$/i.test(f.nodeName))) document.execCommand('formatBlock', false, 'p');
  }
  editor.addEventListener('input', function () { ensureParagraph(); dirty = true; });

  /* ---- Chèn ảnh: chọn nhiều, kéo thả, hoặc dán ---- */
  function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); }
  function insertImages(urls) {
    if (!urls.length) return;
    var html;
    if (urls.length === 1) {
      html = '<p><img src="' + esc(urls[0]) + '" alt=""></p>';
    } else {
      var cls = (urls.length === 2 || urls.length === 4) ? 'gallery gallery-2' : 'gallery gallery-3';
      html = '<figure class="' + cls + '">' + urls.map(function (u) { return '<img src="' + esc(u) + '" alt="">'; }).join('') + '</figure>';
    }
    var wrap = document.createElement('div'); wrap.innerHTML = html;
    var nodes = Array.prototype.slice.call(wrap.childNodes);
    // Luôn chèn SAU đoạn văn đang đặt con trỏ để không cắt đôi đoạn văn (ảnh là khối riêng).
    var rng = saved || (window.getSelection().rangeCount ? window.getSelection().getRangeAt(0) : null);
    var top = null;
    if (rng && editor.contains(rng.startContainer)) {
      top = rng.startContainer;
      while (top && top.parentNode !== editor) top = top.parentNode;
    }
    var ref = top ? top.nextSibling : null;
    var emptyPara = top && top.nodeType === 1 && top.tagName === 'P' && !top.textContent.trim() && !top.querySelector('img');
    if (emptyPara) { ref = top.nextSibling; editor.removeChild(top); }
    nodes.forEach(function (n) { editor.insertBefore(n, ref); });
    // Luôn có một dòng trống sau ảnh để admin gõ tiếp
    var last = nodes[nodes.length - 1], tail = last.nextSibling;
    if (!tail || tail.nodeType !== 1 || tail.tagName !== 'P') {
      var np = document.createElement('p'); np.innerHTML = '<br>'; editor.insertBefore(np, tail || null);
      tail = np;
    }
    var r = document.createRange(); r.setStart(tail, 0); r.collapse(true);
    saved = r;
    dirty = true;
  }
  function uploadMany(files) {
    files = files.filter(function (f) { return f && /^image\//.test(f.type); });
    if (!files.length) { say('Không có ảnh hợp lệ trong các tệp đã chọn.', true); return; }
    if (files.length > 12) { say('Mỗi lần chỉ tải tối đa 12 ảnh.', true); files = files.slice(0, 12); }
    editor.classList.add('uploading');
    var urls = [], errors = [], n = 0;
    var next = function () {
      if (n >= files.length) {
        insertImages(urls);
        editor.classList.remove('uploading');
        say(errors.length ? errors.join(' ') : '', errors.length > 0);
        return;
      }
      say('Đang tải ảnh ' + (n + 1) + '/' + files.length + '…');
      upload(files[n]).then(function (j) { urls.push(j.url); }, function (err) { errors.push(err.message); }).then(function () { n++; next(); });
    };
    next();
  }

  var imgInput = $('#img-input');
  imgInput.addEventListener('change', function () { var fs = Array.prototype.slice.call(imgInput.files); imgInput.value = ''; uploadMany(fs); });

  var box = $('.editor-box');
  function hasFiles(e) { return e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') >= 0; }
  editor.addEventListener('dragover', function (e) { if (hasFiles(e)) { e.preventDefault(); box.classList.add('dragging'); } });
  editor.addEventListener('dragleave', function () { box.classList.remove('dragging'); });
  editor.addEventListener('drop', function (e) {
    box.classList.remove('dragging');
    if (!hasFiles(e)) return;
    e.preventDefault();
    var fs = Array.prototype.slice.call(e.dataTransfer.files);
    var r = document.caretRangeFromPoint ? document.caretRangeFromPoint(e.clientX, e.clientY) : null;
    if (r && editor.contains(r.startContainer)) saved = r;
    uploadMany(fs);
  });

  /* Dán: ảnh thì tải lên; chữ thì chỉ lấy chữ thường để không kéo theo định dạng rác */
  editor.addEventListener('paste', function (e) {
    var cd = e.clipboardData || window.clipboardData; if (!cd) return;
    var imgs = Array.prototype.slice.call(cd.files || []).filter(function (f) { return /^image\//.test(f.type); });
    if (imgs.length) { e.preventDefault(); saveSel(); uploadMany(imgs); return; }
    e.preventDefault();
    var text = cd.getData('text/plain') || '';
    var html = text.split(/\r?\n{2,}/).map(function (p) {
      return '<p>' + p.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\r?\n/g, '<br>') + '</p>';
    }).join('');
    document.execCommand('insertHTML', false, html); dirty = true;
  });

  /* Ảnh bìa */
  imagePicker(upload, { input: '#cover-input', file: '#cover-file', preview: '#cover-preview', clear: '#cover-clear', pick: '#cover-pick' });
  $('#cover-input').addEventListener('change', function () { dirty = true; });

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
