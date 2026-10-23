<?php
/**
 * Accessibility Statement — Legal Page
 */
$title       = 'Accessibility Statement';
$badge       = '♿';
$subtitle    = "Our commitment to making SAIP usable for every Rwandan farmer, regardless of ability.";
$lastUpdated = 'October 4, 2025';
$viewSlug    = 'accessibility';
$readTime    = '5 min';
$version     = 'v2.0';

$versionHistory = [
    ['v' => '2.0', 'date' => 'Oct 4, 2025',  'note' => 'Added explanatory notes & known limitations', 'current' => true],
    ['v' => '1.5', 'date' => 'Jun 20, 2025', 'note' => 'Expanded WCAG 2.1 AA compliance details'],
    ['v' => '1.0', 'date' => 'Feb 15, 2025', 'note' => 'Initial accessibility statement'],
];

$sections = [
    ['id'=>'commitment', 'icon'=>'💚', 'title'=>'Our Commitment'],
    ['id'=>'standards',  'icon'=>'📐', 'title'=>'Standards We Follow'],
    ['id'=>'features',   'icon'=>'✨', 'title'=>'Accessibility Features'],
    ['id'=>'keyboard',   'icon'=>'⌨️', 'title'=>'Keyboard Navigation'],
    ['id'=>'assistive',  'icon'=>'🔊', 'title'=>'Assistive Technology'],
    ['id'=>'limitations','icon'=>'⚠️', 'title'=>'Known Limitations'],
    ['id'=>'feedback',   'icon'=>'💬', 'title'=>'Feedback & Support'],
];

require __DIR__ . '/_partials/head.php';
?>

<!-- ═══ Section 01 ═══ -->
<section id="commitment" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">💚</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 01</span>
            <h3>Our Commitment</h3>
        </div>
    </header>
    <p>SAIP is built for Rwanda's <strong>1.4+ million smallholder farmers</strong>, many of whom may have limited vision, hearing, mobility, or device capabilities. We are committed to making our platform accessible to everyone.</p>
    <div class="callout info">
        <span class="co-icon">🎯</span>
        <div>
            <strong>Our Goal</strong>
            <p>Achieve and maintain <strong>WCAG 2.1 Level AA</strong> compliance across all SAIP pages.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>SAIP is built for everyone. If you face any barrier using it, we want to hear from you and fix it.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 02 ═══ -->
<section id="standards" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">📐</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 02</span>
            <h3>Standards We Follow</h3>
        </div>
    </header>
    <p>SAIP adheres to internationally recognized accessibility standards:</p>
    <ul class="legal-list">
        <li><strong>WCAG 2.1 Level AA</strong> — Web Content Accessibility Guidelines</li>
        <li><strong>Rwanda's Law on the Rights of Persons with Disabilities</strong></li>
        <li><strong>UN Convention on the Rights of Persons with Disabilities (CRPD)</strong></li>
        <li><strong>Section 508</strong> — US public sector baseline practices</li>
        <li><strong>ARIA 1.2</strong> — Accessible Rich Internet Applications</li>
    </ul>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Our design follows WCAG 2.1 AA and Rwandan disability law — the same standards used by major government websites.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 03 ═══ -->
<section id="features" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">✨</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 03</span>
            <h3>Accessibility Features</h3>
        </div>
    </header>
    <p>SAIP includes the following accessibility features:</p>
    <div class="card-grid">
        <div class="grid-card"><span class="gc-icon">🎨</span><strong>High Contrast</strong><small>WCAG AA color ratios on all text</small></div>
        <div class="grid-card"><span class="gc-icon">🔠</span><strong>Scalable Text</strong><small>Up to 200% zoom without loss</small></div>
        <div class="grid-card"><span class="gc-icon">⌨️</span><strong>Keyboard Navigation</strong><small>Full keyboard access to every feature</small></div>
        <div class="grid-card"><span class="gc-icon">🔊</span><strong>Screen Reader Support</strong><small>ARIA labels &amp; semantic landmarks</small></div>
        <div class="grid-card"><span class="gc-icon">🌍</span><strong>Multilingual</strong><small>EN · RW · FR · SW</small></div>
        <div class="grid-card"><span class="gc-icon">📱</span><strong>Responsive</strong><small>Works on any screen size</small></div>
        <div class="grid-card"><span class="gc-icon">🎬</span><strong>Reduced Motion</strong><small>Respects OS preference</small></div>
        <div class="grid-card"><span class="gc-icon">👆</span><strong>Touch Targets</strong><small>Min 44×44px buttons</small></div>
        <div class="grid-card"><span class="gc-icon">🎯</span><strong>Focus Indicators</strong><small>Visible focus outlines</small></div>
        <div class="grid-card"><span class="gc-icon">📝</span><strong>Form Labels</strong><small>Every input has a label</small></div>
        <div class="grid-card"><span class="gc-icon">🎤</span><strong>Voice Input</strong><small>Speech-to-text on supported devices</small></div>
        <div class="grid-card"><span class="gc-icon">🔍</span><strong>Zoom Friendly</strong><small>Layout adapts to text scaling</small></div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Whether you use a screen reader, keyboard, or high-contrast mode — SAIP adapts to your needs.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 04 ═══ -->
<section id="keyboard" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">⌨️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 04</span>
            <h3>Keyboard Navigation</h3>
        </div>
    </header>
    <p>Every feature in SAIP can be accessed with a keyboard alone:</p>
    <div class="kbd-grid">
        <div class="kbd-item"><kbd>Tab</kbd><span>Move to next element</span></div>
        <div class="kbd-item"><kbd>Shift</kbd> + <kbd>Tab</kbd><span>Move back</span></div>
        <div class="kbd-item"><kbd>Enter</kbd><span>Activate button / link</span></div>
        <div class="kbd-item"><kbd>Space</kbd><span>Toggle checkbox</span></div>
        <div class="kbd-item"><kbd>Esc</kbd><span>Close modal / dropdown</span></div>
        <div class="kbd-item"><kbd>←</kbd> <kbd>→</kbd><span>Navigate tabs</span></div>
        <div class="kbd-item"><kbd>↑</kbd> <kbd>↓</kbd><span>Navigate lists</span></div>
        <div class="kbd-item"><kbd>Home</kbd><span>Jump to top</span></div>
        <div class="kbd-item"><kbd>End</kbd><span>Jump to bottom</span></div>
        <div class="kbd-item"><kbd>/</kbd><span>Focus search</span></div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Every feature works without a mouse. Press Tab, arrows, Enter, and Esc to navigate the entire platform.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 05 ═══ -->
<section id="assistive" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">🔊</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 05</span>
            <h3>Assistive Technology</h3>
        </div>
    </header>
    <p>SAIP is tested with the following assistive technologies:</p>
    <ul class="legal-list">
        <li><strong>NVDA</strong> — Free screen reader (Windows)</li>
        <li><strong>JAWS</strong> — Commercial screen reader</li>
        <li><strong>VoiceOver</strong> — Built-in (iOS, macOS)</li>
        <li><strong>TalkBack</strong> — Built-in (Android)</li>
        <li><strong>Dragon NaturallySpeaking</strong> — Voice control software</li>
        <li><strong>Windows Magnifier</strong> — Screen zoom tool</li>
        <li><strong>High Contrast Mode</strong> — Windows accessibility</li>
    </ul>
    <div class="callout success">
        <span class="co-icon">✅</span>
        <div>
            <strong>Cross-Browser Support</strong>
            <p>SAIP is tested on Chrome, Firefox, Edge, and Safari — desktop and mobile.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>We test SAIP with the most common assistive tools. If yours isn't listed, let us know — we'll test it too.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 06 ═══ -->
<section id="limitations" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">⚠️</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 06</span>
            <h3>Known Limitations</h3>
        </div>
    </header>
    <p>We are transparent about current accessibility limitations and are actively working to fix them:</p>
    <ul class="legal-list">
        <li>AI disease detection results are currently image-only (audio descriptions planned)</li>
        <li>Some marketplace charts may not fully support screen readers</li>
        <li>Voice-only commands on mobile have limited support (expanding in v3.0)</li>
        <li>Rwandan Sign Language (RSL) video content is not yet available</li>
    </ul>
    <div class="callout info">
        <span class="co-icon">📌</span>
        <div>
            <strong>Roadmap</strong>
            <p>Accessibility improvements are on our priority roadmap and released every quarter.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>We're honest about what's not yet accessible. Every limitation on this list is being worked on — no hidden issues.</p>
        </div>
    </div>
</section>

<!-- ═══ Section 07 ═══ -->
<section id="feedback" class="legal-section">
    <header class="section-head">
        <span class="sec-icon">💬</span>
        <div class="sec-title-block">
            <span class="sec-num">Section 07</span>
            <h3>Feedback &amp; Support</h3>
        </div>
    </header>
    <p>If you encounter any accessibility barrier, please tell us. We commit to:</p>
    <ul class="legal-list">
        <li>Responding within <strong>5 working days</strong></li>
        <li>Prioritizing fixes for issues that block core functionality</li>
        <li>Providing alternative access methods when a fix is delayed</li>
        <li>Publishing regular updates on improvements shipped</li>
    </ul>
    <div class="callout success">
        <span class="co-icon">✅</span>
        <div>
            <strong>Known Issues Log</strong>
            <p>We maintain a public log of accessibility issues and their fix timeline in our developer portal.</p>
        </div>
    </div>

    <div class="section-note">
        <div class="sn-icon">💡</div>
        <div class="sn-content">
            <strong>What this means for you</strong>
            <p>Report any accessibility issue and we respond within 5 working days. Your feedback shapes the next release.</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/_partials/foot.php'; ?>