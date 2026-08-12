/**
 * External Link - 实时换链脚本（方案A）
 *
 * 不再在 HTML 中写死跳转链接，而是点击外链时通过 AJAX 实时换取最新跳转 URL。
 * 彻底解决 CDN / 页面缓存导致跳转 Token 过期失效的问题。
 */
(function () {
  'use strict';

  if (typeof window.external_link_live_config === 'undefined') return;
  var config = window.external_link_live_config;
  var API = config.ajax_url;
  var DOMAIN = config.domain;

  /** 判断是否为站外 http(s) 链接 */
  function isExternal(href) {
    if (!href || (href.indexOf('http://') !== 0 && href.indexOf('https://') !== 0)) return false;
    try {
      return new URL(href).host !== DOMAIN;
    } catch (e) {
      return false;
    }
  }

  /**
   * 实时换取跳转链接并跳转
   * @param {HTMLAnchorElement} a
   */
  function convertAndGo(a) {
    var href = a.getAttribute('href');
    // 已经是跳转链接或站内链接，直接放行
    if (!isExternal(href)) {
      window.location.href = href;
      return;
    }

    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'external_link_convert',
        url: href
      })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.data && data.data.url) {
          window.location.href = data.data.url;
        } else {
          // 换链失败，直接打开原链接兜底
          window.location.href = href;
        }
      })
      .catch(function () {
        // 网络错误，直接打开原链接兜底
        window.location.href = href;
      });
  }

  /**
   * 为元素绑定点击事件（带 data-external-link 标记的外链）
   * @param {Element} root
   */
  function bind(root) {
    var links = root.querySelectorAll('a[data-external-link="1"]');
    Array.prototype.forEach.call(links, function (a) {
      if (a.dataset.externalLinkLiveBound) return;
      a.dataset.externalLinkLiveBound = '1';
      a.addEventListener('click', function (ev) {
        ev.preventDefault(); // 阻止直接跳转，改为实时换链
        convertAndGo(a);
      });
    });
  }

  // 初次绑定已渲染内容
  bind(document);

  // 监听动态加载内容
  if ('MutationObserver' in window) {
    var ob = new MutationObserver(function (list) {
      list.forEach(function (m) {
        m.addedNodes.forEach(function (n) {
          if (n.nodeType !== 1) return;
          if (n.matches && n.matches('a[data-external-link="1"]')) {
            if (n.dataset.externalLinkLiveBound) return;
            n.dataset.externalLinkLiveBound = '1';
            n.addEventListener('click', function (ev) {
              ev.preventDefault();
              convertAndGo(n);
            });
          }
          if (n.querySelectorAll) bind(n);
        });
      });
    });
    ob.observe(document.body, { childList: true, subtree: true });
  }
})();
