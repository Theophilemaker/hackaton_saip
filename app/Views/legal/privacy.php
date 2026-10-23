<?php
/**
 * Security & Privacy — Legal Page
 */
$title       = 'Security & Privacy';
$badge       = '🔒';
$subtitle    = 'How SAIP collects, protects, and uses your personal and agricultural data.';
$lastUpdated = 'October 4, 2025';
$viewSlug    = 'privacy';
$readTime    = '8 min';
$version     = 'v2.1';

$versionHistory = [
    ['v' => '2.1', 'date' => 'Oct 4, 2025',  'note' => 'Added explanatory notes to each section', 'current' => true],
    ['v' => '2.0', 'date' => 'Aug 12, 2025', 'note' => 'Expanded rights section with Rwanda DPA'],
    ['v' => '1.5', 'date' => 'May 3, 2025',  'note' => 'Added data retention periods'],
    ['v' => '1.0', 'date' => 'Feb 15, 2025', 'note' => 'Initial privacy policy'],
];

$sections = [
    ['id'=>'overview', 'icon'=>'🛡️', 'title'=>'Overview'],
    ['id'=>'collect',  'icon'=>'📥', 'title'=>'Information We Collect'],
    ['id'=>'use',      'icon'=>'🎯', 'title'=>'How We Use Your Data'],
    ['id'=>'storage',  'icon'=>'🗄️', 'title'=>'Storage & Security'],
    ['id'=>'sharing',  'icon'=>'🤝', 'title'=>'Data Sharing'],
    ['id'=>'rights',   'icon'=>'⚖️', 'title'=>'Your Rights'],
    ['id'=>'retention','icon'=>'⏳', 'title'=>'Data Retention'],
    ['id'=>'children', 'icon'=>'👶', 'title'=>"Children's Privacy"],
    ['id'=>'breach',   'icon'=>'🚨', 'title'=>'Breach Notification'],
];

require __DIR__ . '/_partials/head.php';
?>

<!-- ═══ Section 01 ═══ -->
<section id="overview" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🛡️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 01</span>
            <h3>Overview</h3>
        </div>
    </header>
    <p>SAIP is committed to protecting your privacy. This policy explains what data we collect, why we collect it, how we protect it, and your rights regarding your information.</p>
    <div class="callout info">
        <span class="co-icon">📌</span>
        <div>
            <strong>Compliance</strong>
            <p>SAIP complies with Rwanda's Law on Personal Data Protection and Electronic Messages (Law No. 058/2021).</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Your data is protected under Rwandan law. We treat your farming records with the same privacy standards as your personal identity — nothing is shared without your knowledge.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 02 ═══ -->
<section id="collect" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">📥</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 02</span>
            <h3>Information We Collect</h3>
        </div>
    </header>
    <p>We collect the following categories of information:</p>
    <div class="card-grid">
        <div class="grid-card">
            <span class="gc-icon">👤</span>
            <strong>Personal Data</strong>
            <small>Full name · Email · Phone · District · Sector</small>
        </div>
        <div class="grid-card">
            <span class="gc-icon">🌾</span>
            <strong>Farm Data</strong>
            <small>Location · Size · Crops · Photos · Detections</small>
        </div>
        <div class="grid-card">
            <span class="gc-icon">📱</span>
            <strong>Usage Data</strong>
            <small>Login times · Device · IP · Chat interactions</small>
        </div>
        <div class="grid-card">
            <span class="gc-icon">💳</span>
            <strong>Transaction Data</strong>
            <small>Marketplace orders · Delivery addresses</small>
        </div>
        <div class="grid-card">
            <span class="gc-icon">📍</span>
            <strong>Location Data</strong>
            <small>GPS coordinates · District · Farm location</small>
        </div>
        <div class="grid-card">
            <span class="gc-icon">🔊</span>
            <strong>Media Data</strong>
            <small>Voice notes · Photos · Disease scans</small>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Everything you upload — from your name to your farm photos — is collected with a clear purpose. We never collect data you didn't provide or don't need for SAIP to work.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 03 ═══ -->
<section id="use" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🎯</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 03</span>
            <h3>How We Use Your Data</h3>
        </div>
    </header>
    <p>Your data is used strictly for the following purposes:</p>
    <ul class="legal-list">
        <li>Deliver platform services (AI detection, weather, market prices)</li>
        <li>Personalize recommendations for your farm and crops</li>
        <li>Send alerts about pests, weather, or market changes</li>
        <li>Generate anonymized statistics for NISR and MINAGRI</li>
        <li>Improve AI models and platform security</li>
        <li>Process marketplace transactions and logistics</li>
        <li>Detect fraud and prevent abuse</li>
        <li>Comply with legal obligations</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Your data is used to make SAIP work better for you — not to profile you for advertisers or sell to third parties. Every use listed above serves a farming or platform purpose.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 04 ═══ -->
<section id="storage" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🗄️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 04</span>
            <h3>Storage &amp; Security</h3>
        </div>
    </header>
    <p>Your data is stored on secured servers with the following protections:</p>
    <div class="card-grid">
        <div class="grid-card hz"><span class="gc-icon">🔐</span><div><strong>Encryption</strong><small>AES-256 at rest · HTTPS in transit</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🛡️</span><div><strong>Access Control</strong><small>Role-based permissions</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🔑</span><div><strong>Hashed Passwords</strong><small>bcrypt with salt</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">📋</span><div><strong>Audit Logs</strong><small>Every action is logged</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">💾</span><div><strong>Encrypted Backups</strong><small>Daily encrypted snapshots</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🚨</span><div><strong>Intrusion Detection</strong><small>Automated monitoring</small></div></div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Your data is protected with the same techniques used by banks and government portals. Even if a server was compromised, your information stays encrypted and unreadable.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 05 ═══ -->
<section id="sharing" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🤝</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 05</span>
            <h3>Data Sharing</h3>
        </div>
    </header>
    <p><strong>We never sell your personal data.</strong> We share data only in the following cases:</p>
    <ul class="legal-list">
        <li><strong>With NISR</strong> — anonymized agricultural statistics</li>
        <li><strong>With MINAGRI</strong> — aggregated district-level insights</li>
        <li><strong>With your consent</strong> — when you explicitly authorize sharing</li>
        <li><strong>When legally required</strong> — by Rwandan law or court order</li>
        <li><strong>With service providers</strong> — hosting, SMS, payment gateways (under strict contracts)</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>When we share data with the government, it's always anonymized. Your name, phone, and farm details never leave SAIP unless you explicitly ask us to share them.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 06 ═══ -->
<section id="rights" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">⚖️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 06</span>
            <h3>Your Rights</h3>
        </div>
    </header>
    <p>Under Rwanda's Data Protection Law, you have the following rights:</p>
    <div class="card-grid">
        <div class="grid-card hz"><span class="gc-icon">👁️</span><div><strong>Right to Access</strong><small>View all data we hold about you</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">✏️</span><div><strong>Right to Correct</strong><small>Update inaccurate information</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🗑️</span><div><strong>Right to Delete</strong><small>Request account deletion anytime</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">📤</span><div><strong>Right to Export</strong><small>Download as CSV/JSON</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🚫</span><div><strong>Right to Object</strong><small>Opt out of non-essential processing</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">📋</span><div><strong>Right to Lodge Complaint</strong><small>Contact Rwanda's Data Authority</small></div></div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>You own your data — and you control it. From your Account Settings, you can view, export, or permanently delete everything SAIP knows about you at any time.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 07 ═══ -->
<section id="retention" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">⏳</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 07</span>
            <h3>Data Retention</h3>
        </div>
    </header>
    <ul class="legal-list">
        <li><strong>Active account data:</strong> kept while account is active</li>
        <li><strong>Deleted accounts:</strong> purged within 30 days</li>
        <li><strong>Anonymized statistics:</strong> retained indefinitely for research</li>
        <li><strong>Audit logs:</strong> retained for 12 months</li>
        <li><strong>Transaction records:</strong> retained for 7 years (tax law)</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>When you delete your account, your personal information is gone within 30 days. Only anonymized statistics remain — and those can't be traced back to you.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 08 ═══ -->
<section id="children" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">👶</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 08</span>
            <h3>Children's Privacy</h3>
        </div>
    </header>
    <p>SAIP is not intended for users under 18. We do not knowingly collect data from minors. If you believe a minor has registered, please contact us immediately so we can take appropriate action.</p>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>SAIP is designed for adult farmers and professionals. If you're a parent or guardian and discover a minor has registered, we'll remove their account immediately on request.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 09 ═══ -->
<section id="breach" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🚨</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 09</span>
            <h3>Breach Notification</h3>
        </div>
    </header>
    <p>In the event of a data breach that affects your rights, we will:</p>
    <ul class="legal-list">
        <li>Notify affected users within <strong>72 hours</strong></li>
        <li>Report to Rwanda's Data Protection Authority as required by law</li>
        <li>Publish a summary in the platform's notification center</li>
        <li>Provide guidance on protective actions you can take</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>If a security incident ever happens, you'll know within 3 days — not weeks. We'll tell you exactly what was affected and what actions you should take.</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/_partials/foot.php'; ?>