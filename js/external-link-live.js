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
  document.addEventListener('click', handleNavigation);
  document.addEventListener('auxclick', handleNavigation);

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
