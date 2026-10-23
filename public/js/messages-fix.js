/* ============================================================
   messages-fix.js
   Global URL rewriter: /chat/* → /Messages/*
   Fixes InfinityFree WAF blocking "chat" in URLs.
   Load once in a shared layout (topbar / sidebar).
   ============================================================ */
(function () {
    'use strict';
    if (window.__messagesFixInstalled) return;
    window.__messagesFixInstalled = true;

    var RULES = [
        [/\/chat\/start\b/gi,  '/Messages/start'],
        [/\/chat\/send\b/gi,   '/Messages/send'],
        [/\/chat\/poll\b/gi,   '/Messages/poll'],
        [/\/chat\/search\b/gi, '/Messages/search'],
        [/\/chat\/users\b/gi,  '/Messages/users'],
        [/\/chat\/typing\b/gi, '/Messages/typing'],
        [/\/chat\/info\b/gi,   '/Messages/info'],
        [/\/chat\/react\b/gi,  '/Messages/react'],
        [/\/chat\/pin\b/gi,    '/Messages/pin'],
        [/\/chat\/mute\b/gi,   '/Messages/mute'],
        [/\/chat\/leave\b/gi,  '/Messages/leave'],
        [/\/chat\/friendRequest\b/gi, '/Messages/friendRequest'],
        [/\/chat\//gi,         '/Messages/'],
        [/\/Chat\//g,          '/Messages/'],
        [/\/chat(\?|$)/gi,     '/Messages$1'],
        [/\/Chat(\?|$)/g,      '/Messages$1'],
        [/\/hat(\?|$)/g,       '/Messages$1'],
    ];

    function rewrite(url) {
        if (typeof url !== 'string' || !url) return url;

        if (/^https?:\/\//i.test(url)) {
            try {
                var u = new URL(url);
                if (u.host !== window.location.host) return url;
                var p = u.pathname + u.search + u.hash;
                RULES.forEach(function (r) { p = p.replace(r[0], r[1]); });
                return u.origin + p;
            } catch (e) { return url; }
        }
        RULES.forEach(function (r) { url = url.replace(r[0], r[1]); });
        return url;
    }

    if (window.fetch && !window.__origFetch) {
        window.__origFetch = window.fetch;
        window.fetch = function (input, init) {
            if (typeof input === 'string') input = rewrite(input);
            else if (input && input.url) {
                var rw = rewrite(input.url);
                if (rw !== input.url) input = new Request(rw, input);
            }
            return window.__origFetch.call(this, input, init);
        };
    }

    if (window.XMLHttpRequest && !window.__origXhr) {
        window.__origXhr = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function (method, url) {
            url = rewrite(url);
            return window.__origXhr.apply(this, [method, url].concat([].slice.call(arguments, 2)));
        };
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest && e.target.closest('a[href]');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        var newHref = rewrite(href);
        if (newHref !== href) {
            e.preventDefault();
            window.location.href = newHref;
        }
    }, true);

    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (f && f.action) {
            var na = rewrite(f.action);
            if (na !== f.action) f.setAttribute('action', na);
        }
    }, true);

    function fixAllLinks() {
        document.querySelectorAll('a[href*="/chat"], a[href*="/Chat"]').forEach(function (a) {
            var h = a.getAttribute('href');
            var nh = rewrite(h);
            if (nh !== h) a.setAttribute('href', nh);
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixAllLinks);
    } else {
        fixAllLinks();
    }

    console.log('✅ messages-fix.js active — all /chat URLs go to /Messages');
})();