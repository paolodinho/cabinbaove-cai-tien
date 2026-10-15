(function () {
  var d = document, b = d.body;
  // Menu di động
  var burger = d.getElementById('burger');
  if (burger) burger.addEventListener('click', function () {
    var open = b.classList.toggle('nav-open');
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  d.addEventListener('click', function (e) {
    if (b.classList.contains('nav-open') && !e.target.closest('.nav') && !e.target.closest('#burger')) b.classList.remove('nav-open');
    if (e.target.closest('.nav__close')) b.classList.remove('nav-open');
    var t = e.target.closest('.nav__toggle');
    if (t) { var li = t.parentNode; li.classList.toggle('is-open'); t.setAttribute('aria-expanded', li.classList.contains('is-open') ? 'true' : 'false'); }
  });
  d.addEventListener('keydown', function (e) { if (e.key === 'Escape') b.classList.remove('nav-open'); });

  // Header bóng khi cuộn + nút lên đầu
  var hd = d.getElementById('header'), top = d.getElementById('totop');
  function onScroll() { var y = window.scrollY; if (hd) hd.classList.toggle('is-stuck', y > 40); if (top) top.classList.toggle('is-on', y > 600); }
  window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  if (top) top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  // Slider trang chủ
  d.querySelectorAll('[data-slider]').forEach(function (s) {
    var tr = s.querySelector('.slider__track'), n = tr.children.length, i = 0, timer;
    if (n < 2) return;
    var dots = s.querySelectorAll('.slider__dots button');
    function go(k) { i = (k + n) % n; tr.style.transform = 'translateX(' + (-100 * i) + '%)'; dots.forEach(function (x, j) { x.classList.toggle('is-on', j === i); }); }
    function play() { clearInterval(timer); timer = setInterval(function () { go(i + 1); }, 6000); }
    s.querySelector('.slider__nav--prev').addEventListener('click', function () { go(i - 1); play(); });
    s.querySelector('.slider__nav--next').addEventListener('click', function () { go(i + 1); play(); });
    dots.forEach(function (x, j) { x.addEventListener('click', function () { go(j); play(); }); });
    var sx = null;
    tr.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    tr.addEventListener('touchend', function (e) { if (sx === null) return; var dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 40) go(i + (dx < 0 ? 1 : -1)); sx = null; play(); });
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) play();
  });

  // Ảnh sản phẩm: bấm ảnh nhỏ đổi ảnh lớn
  d.querySelectorAll('[data-gallery]').forEach(function (g) {
    var main = g.querySelector('[data-main]');
    g.querySelectorAll('.prd__thumbs button').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!main) return;
        main.removeAttribute('srcset'); main.src = btn.dataset.src;
        g.querySelectorAll('.prd__thumbs button').forEach(function (x) { x.classList.toggle('is-on', x === btn); });
      });
    });
  });

  // Chống chữ mồ côi: nối 2 từ cuối bằng NBSP
  var SEL = 'h1,h2,h3,h4,p,li,figcaption,.btn,[data-nowrap-tail]';
  d.querySelectorAll(SEL).forEach(function (el) {
    var skip = { acceptNode: function (t) { return t.parentNode.closest('script,style,noscript,template') ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT; } };
    var w = d.createTreeWalker(el, NodeFilter.SHOW_TEXT, skip), last = null, x;
    while ((x = w.nextNode())) if (x.nodeValue.trim()) last = x;
    if (!last) return;
    // Mã sản phẩm (GAT-1625, BOT-08...) không tách dòng ở dấu gạch
    var tw = d.createTreeWalker(el, NodeFilter.SHOW_TEXT, skip), y;
    while ((y = tw.nextNode())) if (/[A-Za-z]-[\w.]*\d/.test(y.nodeValue)) y.nodeValue = y.nodeValue.replace(/([A-Za-z])-(?=[\w.]*\d)/g, '$1\u2011');
    var t = last.nodeValue.replace(/\s+$/, ''), m = t.match(/^([\s\S]*\S)\s+(\S+)$/);
    if (m) last.nodeValue = m[1] + ' ' + m[2] + last.nodeValue.slice(t.length);
  });
})();

/* Chống chữ mồ côi: nối 2 từ cuối bằng NBSP */
/* Chống chữ mồ côi (1 từ trên 1 dòng) - dán vào JS của mọi site.
   Nối bằng NBSP từ cuối cùng với từ liền trước trong mọi tiêu đề, đoạn, mục danh sách, nút, chú thích.
   Đi kèm CSS: h1..h6,.title{text-wrap:balance} p,li,figcaption{text-wrap:pretty} */
(function () {
  var SEL = 'h1,h2,h3,h4,h5,h6,p,li,figcaption,blockquote,dd,dt,.btn,.kicker,[data-nowrap-tail]';
  function glue(root) {
    (root || document).querySelectorAll(SEL).forEach(function (el) {
      if (el.dataset.orphanDone) return;
      /* bỏ qua chữ trong script/style: JSON-LD lọt trong <p> bị chèn NBSP sẽ hỏng JSON (lỗi GSC LOA 12/10/2026) */
      var w = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, { acceptNode: function (t) { return t.parentNode.closest('script,style,noscript,template') ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT; } }), last = null, n;
      while ((n = w.nextNode())) if (n.nodeValue.trim()) last = n;
      if (!last) return;
      var t = last.nodeValue.replace(/\s+$/, ''), m = t.match(/^([\s\S]*\S)\s+(\S+)$/);
      if (m && /\S\s+\S/.test(t)) last.nodeValue = m[1] + ' ' + m[2] + last.nodeValue.slice(t.length);
      else if (last.previousSibling === null && last.parentNode !== el) return; /* từ cuối nằm riêng trong thẻ con: bỏ qua */
      el.dataset.orphanDone = '1';
    });
  }
  if (document.readyState !== 'loading') glue(); else document.addEventListener('DOMContentLoaded', function () { glue(); });
  window.icdGlueOrphans = glue;
})();
