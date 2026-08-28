(function () {
  'use strict';

  if (typeof window.external_link_live_config === 'undefined') return;
  var config = window.external_link_live_config;
  var pending = new Map();

  function isExternal(href) {
    if (!href) return false;
    try {
      var url = new URL(href, window.location.href);
      var sameHost = url.hostname === config.domain && (!config.port || String(url.port || (url.protocol === 'https:' ? 443 : 80)) === String(config.port));
      return (url.protocol === 'http:' || url.protocol === 'https:') && !sameHost;
    } catch (error) {
      return false;
    }
  }

  function markScopedLinks(root) {
    if (!config.selector || !root.querySelectorAll) return;
    var containers = [];
    try {
      if (root.matches && root.matches(config.selector)) containers.push(root);
      root.querySelectorAll(config.selector).forEach(function (container) { containers.push(container); });
    } catch (error) {
      return;
    }
    containers.forEach(function (container) {
      container.querySelectorAll('a[href]').forEach(function (link) {
        if (isExternal(link.href)) link.dataset.externalLink = '1';
      });
    });
  }

  function requestRedirectUrl(href) {
    if (pending.has(href)) return pending.get(href);
    var request = fetch(config.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'external_link_convert', url: href })
    }).then(function (response) {
      if (!response.ok) throw new Error('Link conversion failed');
      return response.json();
    }).then(function (data) {
      return data.success && data.data && data.data.url ? data.data.url : href;
    }).catch(function () {
      return href;
    }).finally(function () {
      pending.delete(href);
    });
    pending.set(href, request);
    return request;
  }

  function preconvertScopedLinks(root) {
    if (!config.selector || !root.querySelectorAll) return;
    var containers = [];
    try {
      if (root.matches && root.matches(config.selector)) containers.push(root);
      root.querySelectorAll(config.selector).forEach(function (container) { containers.push(container); });
    } catch (error) {
      return;
    }
    containers.forEach(function (container) {
      container.querySelectorAll('a[href]').forEach(function (link) {
        if (!isExternal(link.href) || link.dataset.externalLinkPrefetched || link.dataset.externalLinkPrefetching) return;
        link.dataset.externalLink = '1';
        link.dataset.externalLinkOriginal = link.href;
        link.dataset.externalLinkPrefetching = '1';
        requestRedirectUrl(link.href).then(function (redirectUrl) {
          if (redirectUrl !== link.href) link.href = redirectUrl;
          link.dataset.externalLinkPrefetched = '1';
          delete link.dataset.externalLinkPrefetching;
        });
      });
    });
  }

  function handleNavigation(event) {
    var link = event.target.closest && event.target.closest('a[data-external-link="1"]');
    if (!link || event.altKey) return;
    var originalHref = link.dataset.externalLinkOriginal || link.href;
    if (!isExternal(originalHref) && !link.dataset.externalLinkPrefetched) return;
    if (event.type === 'click' && event.button !== 0) return;
    if (event.type === 'auxclick' && event.button !== 1) return;

    event.preventDefault();
    // 摘除 target，避免浏览器默认导航（target="_blank"）与下方 window.open 各自
    // 打开一个新窗口。via 等浏览器在 document 冒泡阶段对 preventDefault 拦截
    // target 链接并不可靠，此处额外移除 target 作为兜底，保证只开一个窗口。
    link.removeAttribute('target');
    var openedWindow = window.open('about:blank', '_blank');
    link.setAttribute('aria-busy', 'true');

    var redirectRequest = link.dataset.externalLinkPrefetched
      ? Promise.resolve(link.href)
      : requestRedirectUrl(originalHref);
    redirectRequest.then(function (redirectUrl) {
      link.removeAttribute('aria-busy');
      if (openedWindow && !openedWindow.closed) {
        openedWindow.opener = null;
        openedWindow.location.replace(redirectUrl);
      } else {
        window.location.assign(redirectUrl);
      }
    });
  }

  markScopedLinks(document);
  preconvertScopedLinks(document);
  // 捕获阶段绑定：更早取消默认导航，防止 target="_blank" 链接在部分浏览器
  // （如 via）中既触发默认新窗口、又被下方 window.open 再开一个窗口。
  document.addEventListener('click', handleNavigation, true);
  document.addEventListener('auxclick', handleNavigation, true);

  if ('MutationObserver' in window && document.body) {
    new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            markScopedLinks(node);
            preconvertScopedLinks(node);
          }
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }
})();
