/**
 * NepalSchoolHub — Professional Loading Indicator System
 *
 * Three-layer system:
 *  1. Top progress bar   — thin indigo bar that crawls then completes (GitHub/YouTube style)
 *  2. Page overlay       — subtle full-screen frost that covers between navigations
 *  3. Button spinners    — replace button text with spinner on form submit
 *  4. AJAX interceptors  — auto-show bar on fetch + XHR calls
 */

(function () {
  'use strict';

  /* ─── 1. INJECT STYLES ──────────────────────────────────────────── */
  var style = document.createElement('style');
  style.id  = 'erp-loader-styles';
  style.textContent = `

    /* ── Top Progress Bar ── */
    #erp-progress-bar {
      position: fixed;
      top: 0; left: 0;
      width: 0%;
      height: 3px;
      z-index: 99999;
      border-radius: 0 2px 2px 0;
      background: linear-gradient(90deg, #6366f1 0%, #818cf8 50%, #a5b4fc 100%);
      box-shadow: 0 0 10px rgba(99, 102, 241, 0.7), 0 0 4px rgba(99, 102, 241, 0.4);
      transition: width 0.3s ease, opacity 0.4s ease;
      opacity: 0;
      pointer-events: none;
    }
    #erp-progress-bar.active {
      opacity: 1;
    }
    #erp-progress-bar .glow {
      position: absolute;
      right: 0; top: -2px;
      width: 100px; height: 7px;
      border-radius: 50%;
      background: radial-gradient(ellipse at right, rgba(165, 180, 252, 0.9) 0%, transparent 70%);
    }

    /* ── Page Transition Overlay ── */
    #erp-page-overlay {
      position: fixed;
      inset: 0;
      z-index: 99998;
      background: rgba(248, 250, 252, 0.72);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease;
    }
    #erp-page-overlay.visible {
      opacity: 1;
      pointer-events: all;
    }
    .erp-overlay-logo {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
      animation: erp-logo-pulse 1.5s ease-in-out infinite;
    }
    .erp-overlay-logo img {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(99, 102, 241, 0.25);
    }
    .erp-overlay-logo span {
      font-size: 0.75rem;
      font-weight: 600;
      color: #6366f1;
      letter-spacing: 0.05em;
    }
    @keyframes erp-logo-pulse {
      0%, 100% { transform: scale(1);   opacity: 1;    }
      50%       { transform: scale(1.05); opacity: 0.75; }
    }

    /* ── Dot Train Spinner (below logo) ── */
    .erp-dot-train {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .erp-dot-train span {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: #6366f1;
      animation: erp-dot-bounce 1.2s ease-in-out infinite;
    }
    .erp-dot-train span:nth-child(1) { animation-delay: 0s;    background: #6366f1; }
    .erp-dot-train span:nth-child(2) { animation-delay: 0.15s; background: #818cf8; }
    .erp-dot-train span:nth-child(3) { animation-delay: 0.30s; background: #a5b4fc; }
    .erp-dot-train span:nth-child(4) { animation-delay: 0.45s; background: #c7d2fe; }
    @keyframes erp-dot-bounce {
      0%, 80%, 100% { transform: translateY(0);    opacity: 0.4; }
      40%            { transform: translateY(-8px); opacity: 1;   }
    }

    /* ── Button Loading State ── */
    .erp-btn-loading {
      position: relative;
      pointer-events: none !important;
      opacity: 0.8 !important;
    }
    .erp-btn-loading .erp-btn-spinner {
      display: inline-block;
      width: 14px; height: 14px;
      border: 2px solid rgba(255,255,255,0.4);
      border-top-color: #fff;
      border-radius: 50%;
      animation: erp-spin 0.6s linear infinite;
      vertical-align: middle;
      margin-right: 6px;
    }
    @keyframes erp-spin {
      to { transform: rotate(360deg); }
    }

    /* ── Dark mode compatibility ── */
    [data-theme="dark"] #erp-page-overlay {
      background: rgba(15, 23, 42, 0.72);
    }
    [data-theme="dark"] .erp-overlay-logo span {
      color: #a5b4fc;
    }
    [data-theme="dark"] .erp-dot-train span:nth-child(1) { background: #a5b4fc; }
    [data-theme="dark"] .erp-dot-train span:nth-child(2) { background: #818cf8; }
    [data-theme="dark"] .erp-dot-train span:nth-child(3) { background: #6366f1; }
    [data-theme="dark"] .erp-dot-train span:nth-child(4) { background: #4f46e5; }
  `;
  document.head.appendChild(style);

  /* ─── 2. INJECT HTML ────────────────────────────────────────────── */
  // Progress bar
  var bar = document.createElement('div');
  bar.id  = 'erp-progress-bar';
  bar.innerHTML = '<div class="glow"></div>';
  document.body.appendChild(bar);

  // Page overlay
  var overlay = document.createElement('div');
  overlay.id  = 'erp-page-overlay';
  overlay.innerHTML = `
    <div class="erp-overlay-logo">
      <img src="/imgs/logo_icon.png" alt="NepalSchoolHub" onerror="this.style.display='none'">
      <div class="erp-dot-train">
        <span></span><span></span><span></span><span></span>
      </div>
      <span>Loading…</span>
    </div>
  `;
  document.body.appendChild(overlay);

  /* ─── 3. PROGRESS BAR ENGINE ────────────────────────────────────── */
  var _progress  = 0;
  var _timer     = null;
  var _ajaxCount = 0;

  var Progress = {
    start: function () {
      if (_timer) return; // already running
      _progress = 0;
      bar.classList.add('active');
      bar.style.width = '0%';
      // Crawl quickly to 30%, then slowly to 85%
      this._tick();
    },

    _tick: function () {
      _timer = setInterval(function () {
        if (_progress < 30)       _progress += 8;
        else if (_progress < 60)  _progress += 3;
        else if (_progress < 80)  _progress += 1;
        else if (_progress < 85)  _progress += 0.3;
        // stall at 85% waiting for done()
        if (_progress >= 85) _progress = 85;
        bar.style.width = _progress + '%';
      }, 100);
    },

    done: function () {
      clearInterval(_timer);
      _timer = null;
      _progress = 100;
      bar.style.width = '100%';
      setTimeout(function () {
        bar.classList.remove('active');
        bar.style.transition = 'opacity 0.4s ease';
        setTimeout(function () {
          bar.style.width      = '0%';
          bar.style.transition = 'width 0.3s ease, opacity 0.4s ease';
        }, 400);
      }, 300);
    },

    error: function () {
      clearInterval(_timer);
      _timer    = null;
      bar.style.background = 'linear-gradient(90deg,#ef4444,#f87171)';
      bar.style.width      = '100%';
      setTimeout(function () {
        bar.classList.remove('active');
        setTimeout(function () {
          bar.style.width      = '0%';
          bar.style.background = 'linear-gradient(90deg,#6366f1 0%,#818cf8 50%,#a5b4fc 100%)';
        }, 400);
      }, 600);
    }
  };

  /* ─── 4. PAGE NAVIGATION TRIGGERS ──────────────────────────────── */
  var _overlayTimer = null;

  function showOverlay () {
    clearTimeout(_overlayTimer);
    _overlayTimer = setTimeout(function () {
      overlay.classList.add('visible');
    }, 150); // only show if navigation takes > 150ms
  }

  function hideOverlay () {
    clearTimeout(_overlayTimer);
    overlay.classList.remove('visible');
  }

  // Intercept ALL link clicks (except anchors, external, download, target=_blank)
  document.addEventListener('click', function (e) {
    var link = e.target.closest('a[href]');
    if (!link) return;

    var href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') ||
        href.startsWith('tel:') || href.startsWith('javascript:') ||
        link.target === '_blank' || link.hasAttribute('download') ||
        link.getAttribute('data-bs-toggle') || // Bootstrap modal/tab triggers
        link.getAttribute('data-no-loader') !== null) return;

    // External link
    try {
      var url = new URL(href, window.location.href);
      if (url.origin !== window.location.origin) return;
    } catch (err) { return; }

    Progress.start();
    showOverlay();
  });

  // Form submissions (except GET forms with data-no-loader)
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.getAttribute('data-no-loader') !== null) return;
    if (form.method && form.method.toLowerCase() === 'get') return; // search/filter forms
    Progress.start();
    showOverlay();
  });

  // Hide overlay & finish bar when page finishes loading
  window.addEventListener('pageshow', function (e) {
    hideOverlay();
    if (e.persisted) { Progress.done(); } // back-forward cache
  });

  // Also hide on DOMContentLoaded so hash-tab pages don't stay covered
  document.addEventListener('DOMContentLoaded', function () {
    hideOverlay();
  });

  window.addEventListener('load', function () {
    hideOverlay();
    Progress.done();
  });

  // popstate (back/forward button) — only trigger for real page navigations, not hash changes
  window.addEventListener('popstate', function (e) {
    // If only the hash changed (e.g. tab switching via #achievements), skip the loader
    if (location.pathname === _lastPathname) return;
    Progress.start();
    showOverlay();
  });

  var _lastPathname = location.pathname;

  /* ─── 5. AJAX / FETCH INTERCEPTORS ─────────────────────────────── */
  // Show bar on any network call, hide when all done

  // --- fetch ---
  var _originalFetch = window.fetch;
  window.fetch = function () {
    _ajaxCount++;
    if (_ajaxCount === 1) Progress.start();

    return _originalFetch.apply(this, arguments).then(
      function (res) {
        _ajaxCount = Math.max(0, _ajaxCount - 1);
        if (_ajaxCount === 0) Progress.done();
        return res;
      },
      function (err) {
        _ajaxCount = Math.max(0, _ajaxCount - 1);
        if (_ajaxCount === 0) Progress.error();
        throw err;
      }
    );
  };

  // --- XMLHttpRequest ---
  var _origXHROpen = XMLHttpRequest.prototype.open;
  var _origXHRSend = XMLHttpRequest.prototype.send;

  XMLHttpRequest.prototype.open = function (method, url) {
    this._erpUrl = url;
    return _origXHROpen.apply(this, arguments);
  };

  XMLHttpRequest.prototype.send = function () {
    var xhr = this;
    _ajaxCount++;
    if (_ajaxCount === 1) Progress.start();

    xhr.addEventListener('loadend', function () {
      _ajaxCount = Math.max(0, _ajaxCount - 1);
      if (_ajaxCount === 0) {
        if (xhr.status >= 400) Progress.error();
        else Progress.done();
      }
    });

    return _origXHRSend.apply(this, arguments);
  };

  /* ─── 6. BUTTON LOADING STATES ─────────────────────────────────── */
  // When a form is submitted, replace submit button text with spinner
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.getAttribute('data-no-loader') !== null) return;
    if (form.method && form.method.toLowerCase() === 'get') return;

    var btn = form.querySelector('[type="submit"]');
    if (!btn || btn.getAttribute('data-no-spinner') !== null) return;

    // Store original HTML & add spinner
    btn._erpOrigHTML = btn.innerHTML;
    var label = btn.getAttribute('data-loading-text') || 'Processing…';
    btn.innerHTML    = '<span class="erp-btn-spinner"></span>' + label;
    btn.classList.add('erp-btn-loading');
    btn.disabled     = true;

    // Restore if page doesn't navigate (e.g. validation error)
    setTimeout(function () {
      if (btn.classList.contains('erp-btn-loading')) {
        btn.innerHTML = btn._erpOrigHTML || btn.innerHTML;
        btn.classList.remove('erp-btn-loading');
        btn.disabled  = false;
      }
    }, 12000); // 12 sec safety timeout
  });

  /* ─── 7. EXPOSE GLOBAL API ──────────────────────────────────────── */
  window.ErpLoader = {
    start  : function () { Progress.start(); },
    done   : function () { Progress.done();  hideOverlay(); },
    error  : function () { Progress.error(); hideOverlay(); },
    overlay: { show: showOverlay, hide: hideOverlay }
  };

})();
