(function () {
  'use strict';

  if (typeof window.external_link_live_config === 'undefined') return;
  var config = window.external_link_live_config;
  var pending = new Map();
  var originalWindowOpen = window.open;

  function isExternal(href) {
    if (!href || typeof href !== 'string') return false;
    var trimmed = href.trim();
    if (/^(javascript:|mailto:|tel:|#|data:|blob:)/i.test(trimmed)) return false;
    try {
      var url = new URL(trimmed, window.location.href);
      var sameHost = url.hostname === config.domain && (!config.port || String(url.port || (url.protocol === 'https:' ? 443 : 80)) === String(config.port));
      if (sameHost || (config.slug && url.pathname.indexOf('/' + config.slug) !== -1)) {
        return false;
      }
      return (url.protocol === 'http:' || url.protocol === 'https:');
    } catch (error) {
      return false;
    }
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

  // 为节点内所有外部链接添加 data-external-link 标记（覆盖动态弹窗、AJAX加载等所有场景）
  function markLinksInNode(root) {
    if (!root || !root.querySelectorAll) return;
    try {
      if (root.matches && root.matches('a[href]')) {
        if (isExternal(root.href)) root.dataset.externalLink = '1';
      }
      root.querySelectorAll('a[href]').forEach(function (link) {
        if (isExternal(link.href)) link.dataset.externalLink = '1';
      });
    } catch (error) {}
  }

  // 针对配置了特定选择器的容器打标
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
      markLinksInNode(container);
    });
  }

  // 预转链逻辑（仅对配置了特定选择器的模块生效，避免全站预请求过多触发限流）
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

  // 核心拦截处理：监听点击事件
  function handleNavigation(event) {
    if (event.altKey) return;
    if (event.type === 'click' && event.button !== 0) return;
    if (event.type === 'auxclick' && event.button !== 1) return;

    var targetEl = event.target;
    var link = targetEl && targetEl.closest ? targetEl.closest('a[href]') : null;
    var externalUrl = null;
    var isPrefetched = false;

    if (link) {
      var rawHref = link.getAttribute('href') || '';
      if (!/^(javascript:|#|mailto:|tel:|data:|blob:)/i.test(rawHref.trim())) {
        var originalHref = link.dataset.externalLinkOriginal || link.href;
        if (link.dataset.externalLinkPrefetched) {
          externalUrl = link.href;
          isPrefetched = true;
        } else if (link.dataset.externalLink === '1' || isExternal(originalHref)) {
          externalUrl = originalHref;
        }
      }
    } else {
      // 兼容非 <a> 标签带有 data-url / data-href / data-link 的自定义按钮/卡片（常见于商业弹窗）
      var customEl = targetEl && targetEl.closest ? targetEl.closest('[data-url], [data-href], [data-link]') : null;
      if (customEl) {
        var customUrl = customEl.getAttribute('data-url') || customEl.getAttribute('data-href') || customEl.getAttribute('data-link');
        if (isExternal(customUrl)) {
          externalUrl = customUrl;
          link = customEl;
        }
      }
    }

    if (!externalUrl) return;

    event.preventDefault();
    event.stopPropagation();

    if (link && link.removeAttribute) {
      // 摘除 target，避免浏览器默认导航与下方 window.open 各自打开一个窗口
      link.removeAttribute('target');
      link.setAttribute('aria-busy', 'true');
    }

    // 同步打开新标签页，绕过现代浏览器的弹窗拦截策略
    var openedWindow = originalWindowOpen.call(window, 'about:blank', '_blank');

    var redirectRequest = isPrefetched
      ? Promise.resolve(externalUrl)
      : requestRedirectUrl(externalUrl);

    redirectRequest.then(function (redirectUrl) {
      if (link && link.removeAttribute) {
        link.removeAttribute('aria-busy');
      }
      if (openedWindow && !openedWindow.closed) {
        openedWindow.opener = null;
        openedWindow.location.replace(redirectUrl);
      } else {
        window.location.assign(redirectUrl);
      }
    });
  }

  // 劫持原生 window.open，防御性拦截通过 JS 脚本/广告组件直接发起的外部跳转
  window.open = function (url, target, features) {
    if (url && typeof url === 'string' && isExternal(url)) {
      var openedWindow = originalWindowOpen.call(window, 'about:blank', target || '_blank', features);
      requestRedirectUrl(url).then(function (redirectUrl) {
        if (openedWindow && !openedWindow.closed) {
          openedWindow.opener = null;
          openedWindow.location.replace(redirectUrl);
        } else {
          window.location.assign(redirectUrl);
        }
      });
      return openedWindow;
    }
    return originalWindowOpen.apply(window, arguments);
  };

  markScopedLinks(document);
  preconvertScopedLinks(document);

  // 捕获阶段绑定：最优先捕获点击事件
  document.addEventListener('click', handleNavigation, true);
  document.addEventListener('auxclick', handleNavigation, true);

  // 监听 DOM 树变动，自动为新增的弹窗、模态框、异步加载内容标记外链
  if ('MutationObserver' in window && document.body) {
    new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            markLinksInNode(node);
            markScopedLinks(node);
            preconvertScopedLinks(node);
          }
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }
})();
