<?php
/**
 * Terms & Conditions — Legal Page
 */
$title       = 'Terms & Conditions';
$badge       = '📜';
$subtitle    = 'The rules governing your use of the SAIP platform, services, and data.';
$lastUpdated = 'October 4, 2025';
$viewSlug    = 'terms';
$readTime    = '6 min';
$version     = 'v2.1';

$versionHistory = [
    ['v' => '2.1', 'date' => 'Oct 4, 2025',  'note' => 'Added explanatory notes & clarified retention', 'current' => true],
    ['v' => '2.0', 'date' => 'Aug 12, 2025', 'note' => 'Added Rwandan Data Protection Law alignment'],
    ['v' => '1.5', 'date' => 'May 3, 2025',  'note' => 'Added intellectual property section'],
    ['v' => '1.0', 'date' => 'Feb 15, 2025', 'note' => 'Initial public release'],
];

$sections = [
    ['id'=>'acceptance', 'icon'=>'📜', 'title'=>'Acceptance of Terms'],
    ['id'=>'eligibility','icon'=>'👤', 'title'=>'Eligibility'],
    ['id'=>'account',    'icon'=>'🔑', 'title'=>'Account Responsibilities'],
    ['id'=>'services',   'icon'=>'🌾', 'title'=>'Platform Services'],
    ['id'=>'data',       'icon'=>'📊', 'title'=>'Data Use & Ownership'],
    ['id'=>'ip',         'icon'=>'©️',  'title'=>'Intellectual Property'],
    ['id'=>'prohibited', 'icon'=>'🚫', 'title'=>'Prohibited Conduct'],
    ['id'=>'thirdparty', 'icon'=>'🔗', 'title'=>'Third-Party Services'],
    ['id'=>'liability',  'icon'=>'⚖️', 'title'=>'Limitation of Liability'],
    ['id'=>'termination','icon'=>'🔚', 'title'=>'Termination'],
    ['id'=>'changes',    'icon'=>'📝', 'title'=>'Changes to Terms'],
];

require __DIR__ . '/_partials/head.php';
?>

<!-- ═══ Section 01 ═══ -->
<section id="acceptance" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">📜</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 01</span>
            <h3>Acceptance of Terms</h3>
        </div>
    </header>
    <p>By accessing or using the SAIP platform — including the website, mobile application, APIs, and any related services — you agree to be bound by these Terms &amp; Conditions. If you do not accept these terms in full, you must discontinue use of the platform immediately.</p>
    <p>These terms constitute a legally binding agreement between you ("User", "you") and SAIP ("we", "us", "our"), developed for the University of Rwanda BIT Capstone and NISR Big Data Hackathon 2026.</p>
    <div class="callout info">
        <span class="co-icon">📌</span>
        <div>
            <strong>Important</strong>
            <p>These terms apply to all users — farmers, cooperative leaders, agricultural officers, government officers, exporters, researchers, and administrators.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>By using SAIP, you agree to these rules. If any part is unclear, contact us before continuing to use the platform.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 02 ═══ -->
<section id="eligibility" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">👤</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 02</span>
            <h3>Eligibility</h3>
        </div>
    </header>
    <p>To use SAIP, you must meet the following requirements:</p>
    <ul class="legal-list">
        <li>Be at least <strong>18 years of age</strong> or have legal guardian consent</li>
        <li>Provide accurate, current, and complete registration information</li>
        <li>Be a resident of Rwanda, or a partner organization operating within Rwanda</li>
        <li>Have the legal capacity to enter into binding agreements</li>
        <li>Not be barred from using digital agricultural services under Rwandan law</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>You must be 18+ and a Rwandan resident or partner organization. Accurate signup information is required.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 03 ═══ -->
<section id="account" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🔑</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 03</span>
            <h3>Account Responsibilities</h3>
        </div>
    </header>
    <p>You are responsible for maintaining the confidentiality of your credentials and for all activity that occurs under your account. You agree to:</p>
    <ul class="legal-list">
        <li>Choose a strong password (minimum 8 characters) and keep it confidential</li>
        <li>Notify us immediately of any unauthorized access or security breach</li>
        <li>Provide truthful information in your profile, farm records, and listings</li>
        <li>Log out from shared devices after each session</li>
        <li>Not share your account with other individuals</li>
        <li>Keep your contact information up to date</li>
    </ul>
    <div class="callout warn">
        <span class="co-icon">⚠️</span>
        <div>
            <strong>Security Notice</strong>
            <p>SAIP is not liable for losses resulting from your failure to safeguard your credentials. Enable two-factor authentication for added protection.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Your account is your responsibility. Keep your password safe — never share it. Enable 2FA for extra protection.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 04 ═══ -->
<section id="services" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🌾</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 04</span>
            <h3>Platform Services</h3>
        </div>
    </header>
    <p>SAIP provides the following integrated services to Rwandan farmers and agricultural stakeholders:</p>
    <div class="card-grid">
        <div class="grid-card"><span class="gc-icon">🤖</span><strong>AI AgriBot</strong><small>Multilingual farming advice chatbot</small></div>
        <div class="grid-card"><span class="gc-icon">🔬</span><strong>Disease Detection</strong><small>Photo-based crop diagnostics</small></div>
        <div class="grid-card"><span class="gc-icon">🌤️</span><strong>Weather Intelligence</strong><small>District-level forecasts</small></div>
        <div class="grid-card"><span class="gc-icon">💰</span><strong>Market Prices</strong><small>Real-time agricultural pricing</small></div>
        <div class="grid-card"><span class="gc-icon">📊</span><strong>NISR Datasets</strong><small>Open government data access</small></div>
        <div class="grid-card"><span class="gc-icon">🚚</span><strong>Logistics</strong><small>Transport coordination</small></div>
        <div class="grid-card"><span class="gc-icon">🏭</span><strong>Storage Management</strong><small>Warehouse &amp; bin tracking</small></div>
        <div class="grid-card"><span class="gc-icon">💬</span><strong>Farmer Connect</strong><small>Community chat &amp; forums</small></div>
    </div>
    <p>Services are provided "as is" for informational and decision-support purposes. Field decisions remain the responsibility of the farmer.</p>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>SAIP provides AI guidance, but final farming decisions are always yours. Treat suggestions as advice, not commands.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 05 ═══ -->
<section id="data" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">📊</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 05</span>
            <h3>Data Use &amp; Ownership</h3>
        </div>
    </header>
    <p><strong>Your data belongs to you.</strong> You retain full ownership of all farm data, photos, records, and content you upload to SAIP.</p>
    <ul class="legal-list">
        <li>We use your data only to provide and improve SAIP services</li>
        <li>Anonymized aggregate data may be shared with NISR and MINAGRI for national statistics</li>
        <li>We never sell personal data to third parties</li>
        <li>You may export or delete your data at any time from account settings</li>
        <li>You can opt out of non-essential data processing</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Your farm data is yours. We use it to run the platform — never to sell it. You can export or delete it anytime.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 06 ═══ -->
<section id="ip" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">©️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 06</span>
            <h3>Intellectual Property</h3>
        </div>
    </header>
    <p>The SAIP platform — including its source code, AI models, design system, documentation, branding, and trademarks — is the intellectual property of SAIP and its development team.</p>
    <p>Open-source components are used under their respective licenses. You may not:</p>
    <ul class="legal-list danger">
        <li>Copy or redistribute any part of the platform</li>
        <li>Reverse-engineer the AI models or algorithms</li>
        <li>Remove or alter copyright notices</li>
        <li>Use SAIP branding without written permission</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>SAIP's code, models, and design belong to us. You may use them freely as intended, but not copy or resell them.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 07 ═══ -->
<section id="prohibited" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🚫</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 07</span>
            <h3>Prohibited Conduct</h3>
        </div>
    </header>
    <p>You agree <strong>not</strong> to engage in any of the following:</p>
    <ul class="legal-list danger">
        <li>Upload false, misleading, or fraudulent information</li>
        <li>Attempt to hack, scrape, or overload the platform</li>
        <li>Use SAIP for illegal activities including fraud or money laundering</li>
        <li>Impersonate other users, cooperatives, or government officials</li>
        <li>Share harmful code, malware, or spam</li>
        <li>Violate Rwandan law or international agricultural trade regulations</li>
        <li>Harvest or misuse other users' personal data</li>
        <li>Submit fake disease reports or market prices</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Be honest. Fake data, hacks, and fraud harm every farmer on the platform — and are grounds for permanent removal.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 08 ═══ -->
<section id="thirdparty" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🔗</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 08</span>
            <h3>Third-Party Services</h3>
        </div>
    </header>
    <p>SAIP integrates with external services to enhance functionality:</p>
    <div class="card-grid">
        <div class="grid-card hz"><span class="gc-icon">🗺️</span><div><strong>OpenStreetMap</strong><small>Map tiles &amp; geolocation</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">🌦️</span><div><strong>Meteo Rwanda</strong><small>Weather data</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">📊</span><div><strong>NISR</strong><small>National statistics</small></div></div>
        <div class="grid-card hz"><span class="gc-icon">💳</span><div><strong>Payment Gateways</strong><small>Marketplace transactions</small></div></div>
    </div>
    <p>These services are governed by their own terms. SAIP is not responsible for third-party outages, inaccuracies, or policy changes.</p>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Some data (weather, maps, market prices) comes from outside SAIP. We can't guarantee its accuracy but always aim for reliability.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 09 ═══ -->
<section id="liability" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">⚖️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 09</span>
            <h3>Limitation of Liability</h3>
        </div>
    </header>
    <p>SAIP is provided for informational and decision-support purposes only. We are not liable for:</p>
    <ul class="legal-list">
        <li>Crop losses arising from AI advice or market forecasts</li>
        <li>Service interruptions due to internet, power, or hosting issues</li>
        <li>Actions taken based on third-party data (weather, market prices)</li>
        <li>Indirect, incidental, or consequential damages</li>
        <li>Data loss caused by user error or device failure</li>
        <li>Marketplace transaction disputes between buyers and sellers</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>SAIP doesn't replace your own judgment. We're not liable for losses from following AI advice — always consider local conditions.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 10 ═══ -->
<section id="termination" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🔚</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 10</span>
            <h3>Termination</h3>
        </div>
    </header>
    <p>You may delete your SAIP account at any time. Upon deletion:</p>
    <ul class="legal-list">
        <li>Your personal data will be removed within 30 days</li>
        <li>Anonymized statistics may be retained for research purposes</li>
        <li>Active marketplace listings will be cancelled</li>
        <li>Pending logistics requests will be archived</li>
    </ul>
    <p>We reserve the right to suspend or terminate accounts that violate these terms, with or without notice.</p>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>You can leave anytime — delete your account and your data goes with you. We only remove accounts that break the rules.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 11 ═══ -->
<section id="changes" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">📝</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 11</span>
            <h3>Changes to Terms</h3>
        </div>
    </header>
    <p>We may update these Terms &amp; Conditions periodically. Material changes will be notified via:</p>
    <ul class="legal-list">
        <li>Email notification to your registered address</li>
        <li>Dashboard banner on your next login</li>
        <li>Updated "Last updated" date at the top of this page</li>
    </ul>
    <p>Continued use of SAIP after changes constitutes acceptance of the revised terms.</p>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Terms may change as SAIP grows. We notify you in advance — check your dashboard for updates when you log in.</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/_partials/foot.php'; ?>