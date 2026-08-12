/**
 * 实时把指定选择器内的外链换成跳转链接
 * 支持动态配置选择器，适配不同主题
 */
(function () {
  // 获取配置，如果没有配置则使用默认值
  const config = window.external_link_circle_config || {
    selector: '.topic-content',
    ajax_url: window.location.origin + '/wp-admin/admin-ajax.php'
  };

  const ROOT = window.location.origin;
  const API = config.ajax_url;
  const DOMAIN = window.location.host;
  const SELECTOR = config.selector;

  /** 判断纯外链（含 http/https ，且 host ≠ 当前域名） */
  function isExternal(href) {
    if (!href || (!href.startsWith('http://') && !href.startsWith('https://'))) return false;
    try { return new URL(href).host !== DOMAIN; } catch (e) { return false; }
  }

  function convert(a) {
    if (a.dataset.externalLinkDone) return;            // 避免重复处理
    const href = a.getAttribute('href');
    if (!isExternal(href)) return;

    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ 
        action: 'external_link_convert',
        url: href 
      })
    })
      .then(r => r.json())
      .then(data => { 
        if (data.success && data.data && data.data.url) {
          a.setAttribute('href', data.data.url);
        }
      })
      .catch(console.error);

    a.dataset.externalLinkDone = '1';
  }

  function scan(root) {
    const links = root.querySelectorAll('a[href]');
    // 使用 requestIdleCallback 节流，避免大量外链集中发起请求阻塞渲染
    links.forEach(link => {
      if ('requestIdleCallback' in window) {
        requestIdleCallback(() => convert(link));
      } else {
        convert(link);
      }
    });
  }

  // 初次扫描已经渲染好的内容
  document.querySelectorAll(SELECTOR).forEach(scan);

  // 监听后续 DOM 变化（适配动态加载内容）
  const ob = new MutationObserver(list => {
    list.forEach(m => {
      m.addedNodes.forEach(n => {
        if (n.nodeType !== 1) return;
        if (n.matches(SELECTOR)) scan(n);
        n.querySelectorAll && n.querySelectorAll(SELECTOR).forEach(scan);
      });
    });
  });
  ob.observe(document.body, { childList: true, subtree: true });
})();
