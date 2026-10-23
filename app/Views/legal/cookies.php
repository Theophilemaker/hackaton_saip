<?php
/**
 * Cookie Policy — Legal Page
 */
$title       = 'Cookie Policy';
$badge       = '🍪';
$subtitle    = 'How SAIP uses cookies and similar technologies on our platform.';
$lastUpdated = 'October 4, 2025';
$viewSlug    = 'cookies';
$readTime    = '4 min';
$version     = 'v2.0';

$versionHistory = [
    ['v' => '2.0', 'date' => 'Oct 4, 2025',  'note' => 'Added explanatory notes & third-party disclosure', 'current' => true],
    ['v' => '1.5', 'date' => 'May 3, 2025',  'note' => 'Clarified analytics cookies'],
    ['v' => '1.0', 'date' => 'Feb 15, 2025', 'note' => 'Initial cookie policy'],
];

$sections = [
    ['id'=>'what',      'icon'=>'🍪', 'title'=>'What Are Cookies'],
    ['id'=>'why',       'icon'=>'🎯', 'title'=>'Why We Use Cookies'],
    ['id'=>'types',     'icon'=>'📋', 'title'=>'Types We Use'],
    ['id'=>'manage',    'icon'=>'⚙️', 'title'=>'Managing Cookies'],
    ['id'=>'thirdparty','icon'=>'🔗', 'title'=>'Third-Party Cookies'],
    ['id'=>'changes',   'icon'=>'📝', 'title'=>'Updates'],
];

require __DIR__ . '/_partials/head.php';
?>

<!-- ═══ Section 01 ═══ -->
<section id="what" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🍪</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 01</span>
            <h3>What Are Cookies</h3>
        </div>
    </header>
    <p>Cookies are small text files stored on your device when you visit SAIP. They help us remember your preferences, keep you logged in, and understand how the platform is used.</p>
    <p>We also use similar technologies such as <strong>localStorage</strong> for storing user interface state (like sidebar collapse, theme preference, and draft form content).</p>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Cookies are tiny files that remember you between visits. Without them, you'd have to log in on every page load.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 02 ═══ -->
<section id="why" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🎯</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 02</span>
            <h3>Why We Use Cookies</h3>
        </div>
    </header>
    <p>We use cookies for four key reasons:</p>
    <ul class="legal-list">
        <li><strong>Essential</strong> — Login sessions, security tokens, CSRF protection</li>
        <li><strong>Preferences</strong> — Language, theme, district, sidebar state, font size</li>
        <li><strong>Analytics</strong> — Understand which features are used most and where users struggle</li>
        <li><strong>Performance</strong> — Improve page loading, caching, and responsiveness</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Cookies make SAIP faster and personal. They keep you logged in, remember your language, and help us fix bugs.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 03 ═══ -->
<section id="types" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">📋</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 03</span>
            <h3>Types We Use</h3>
        </div>
    </header>
    <p>Here is a complete list of cookies set by SAIP:</p>
    <div class="cookie-table">
        <div class="ct-head">
            <span>Cookie</span>
            <span>Purpose</span>
            <span>Duration</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">PHPSESSID</span>
            <span>Session — keeps you logged in</span>
            <span class="ct-badge essential">Session</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">csrf_token</span>
            <span>Security — prevents form attacks</span>
            <span class="ct-badge essential">Session</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">lang</span>
            <span>Preference — your chosen language</span>
            <span class="ct-badge pref">1 year</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">district</span>
            <span>Preference — your district filter</span>
            <span class="ct-badge pref">6 months</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">theme</span>
            <span>Preference — light/dark mode</span>
            <span class="ct-badge pref">1 year</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">sidebar_state</span>
            <span>Preference — collapsed/expanded</span>
            <span class="ct-badge pref">30 days</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">_analytics</span>
            <span>Analytics — anonymous usage stats</span>
            <span class="ct-badge analytics">2 years</span>
        </div>
        <div class="ct-row">
            <span class="ct-name">_perf</span>
            <span>Performance — page load timing</span>
            <span class="ct-badge analytics">30 days</span>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Essential cookies are required. Preference and analytics cookies are optional — you can control them in your browser.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 04 ═══ -->
<section id="manage" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">⚙️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 04</span>
            <h3>Managing Cookies</h3>
        </div>
    </header>
    <p>You can control or disable cookies through your browser settings:</p>
    <div class="card-grid">
        <div class="grid-card hz"><span class="gc-icon">🌐</span><div><strong>Chrome</strong><small>Settings → Privacy and security → Cookies</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🦊</span><div><strong>Firefox</strong><small>Options → Privacy &amp; Security</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🧭</span><div><strong>Safari</strong><small>Preferences → Privacy</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🌊</span><div><strong>Edge</strong><small>Settings → Cookies &amp; site permissions</small></div></div>
    </div>
    <div class="callout warn">
        <span class="co-icon">⚠️</span>
        <div>
            <strong>Important</strong>
            <p>Disabling essential cookies may prevent you from logging in or using core SAIP features like the marketplace and farm records.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>You're in control. Disable cookies anytime in your browser settings — but note that some features may stop working.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 05 ═══ -->
<section id="thirdparty" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🔗</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 05</span>
            <h3>Third-Party Cookies</h3>
        </div>
    </header>
    <p>We use minimal third-party services. None of them set advertising cookies:</p>
    <ul class="legal-list">
        <li><strong>OpenStreetMap</strong> — for map tiles (no tracking)</li>
        <li><strong>Google Fonts</strong> — for typography (no tracking)</li>
        <li><strong>Chart.js CDN</strong> — for data visualization</li>
        <li><strong>Font Awesome CDN</strong> — for icons</li>
    </ul>
    <div class="callout success">
        <span class="co-icon">✅</span>
        <div>
            <strong>We Do Not Use</strong>
            <p>No advertising or tracking cookies from Facebook Pixel, Google Ads, TikTok, or similar third parties.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>We use only minimal third-party services. No advertisers, no trackers, no surprise data collection.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 06 ═══ -->
<section id="changes" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">📝</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 06</span>
            <h3>Updates to This Policy</h3>
        </div>
    </header>
    <p>We may update this Cookie Policy from time to time to reflect changes in technology, legislation, or our services. The "Last updated" date at the top reflects the most recent revision.</p>
    <p>Material changes will be notified via a dashboard banner on your next login.</p>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>If we add a new cookie, we'll update this page and notify you on your next login. No silent changes.</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/_partials/foot.php'; ?>