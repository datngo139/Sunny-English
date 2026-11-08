<?php
declare(strict_types=1);

/**
 * Lọc HTML bài viết theo danh sách cho phép (allow-list).
 * Mọi thẻ/thuộc tính ngoài danh sách bị loại bỏ → chống chèn script (XSS) dù admin dán nội dung từ web khác.
 */
function sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $doc = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?><div id="sunny-root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $root = $doc->getElementById('sunny-root');
    if (!$root) {
        return '';
    }
    sanitize_children($root, $doc);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

function sanitize_children(DOMNode $parent, DOMDocument $doc): void
{
    // Duyệt trên bản sao vì sẽ sửa cây trong lúc lặp.
    $children = [];
    foreach ($parent->childNodes as $c) {
        $children[] = $c;
    }
    foreach ($children as $node) {
        if ($node instanceof DOMComment) {
            $parent->removeChild($node);
            continue;
        }
        if (!($node instanceof DOMElement)) {
            continue; // text node giữ nguyên
        }

        $tag = strtolower($node->tagName);

        // Loại bỏ hẳn cả phần ruột.
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea',
            'select', 'svg', 'math', 'link', 'meta', 'base', 'noscript', 'template', 'video', 'audio'], true)) {
            $parent->removeChild($node);
            continue;
        }

        // Đổi tên thẻ tương đương.
        $rename = ['b' => 'strong', 'i' => 'em', 'div' => 'p', 'h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4'];
        if (isset($rename[$tag])) {
            $new = $doc->createElement($rename[$tag]);
            while ($node->firstChild) {
                $new->appendChild($node->firstChild);
            }
            $parent->replaceChild($new, $node);
            $node = $new;
            $tag = $rename[$tag];
        }

        $allowed = ['p', 'br', 'h2', 'h3', 'h4', 'strong', 'em', 'u', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'hr', 'figure', 'figcaption'];
        if (!in_array($tag, $allowed, true)) {
            // Thẻ lạ (span, font, table...) → bỏ thẻ, giữ nội dung.
            sanitize_children($node, $doc);
            while ($node->firstChild) {
                $parent->insertBefore($node->firstChild, $node);
            }
            $parent->removeChild($node);
            continue;
        }

        // Dọn thuộc tính.
        $keep = [];
        if ($tag === 'a') {
            $href = sanitize_url($node->getAttribute('href'), false);
            if ($href === '') {
                // Link hỏng → giữ chữ, bỏ link.
                sanitize_children($node, $doc);
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
                continue;
            }
            $keep['href'] = $href;
            $keep['rel'] = 'noopener noreferrer';
            if (preg_match('#^https?://#i', $href)) {
                $keep['target'] = '_blank';
            }
        } elseif ($tag === 'img') {
            $src = sanitize_url($node->getAttribute('src'), true);
            if ($src === '') {
                $parent->removeChild($node);
                continue;
            }
            $keep['src'] = $src;
            $keep['alt'] = mb_substr($node->getAttribute('alt'), 0, 200);
            $keep['loading'] = 'lazy';
        }
        while ($node->attributes->length > 0) {
            $node->removeAttributeNode($node->attributes->item(0));
        }
        foreach ($keep as $k => $v) {
            $node->setAttribute($k, $v);
        }

        if (!in_array($tag, ['br', 'hr', 'img'], true)) {
            sanitize_children($node, $doc);
        }
    }
}

/** Chỉ cho phép http(s), mailto, tel và đường dẫn nội bộ. Ảnh chỉ cho phép http(s) và /uploads/. */
function sanitize_url(string $url, bool $image): string
{
    $url = trim($url);
    if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url)) {
        return '';
    }
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    if ($image) {
        // Ảnh tải lên của chính website.
        return preg_match('#^/(?:[A-Za-z0-9_\-./]*/)?uploads/[A-Za-z0-9_\-./]+$#', $url) && !str_contains($url, '..') ? $url : '';
    }
    if (preg_match('#^(mailto:|tel:)[^\s]+$#i', $url)) {
        return $url;
    }
    if (preg_match('#^/(?!/)[^\s]*$#', $url) || preg_match('/^#[\w\-]+$/', $url)) {
        return $url;
    }
    return '';
}
