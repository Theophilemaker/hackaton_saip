<script>
/* Smooth scroll + active TOC highlight */
(function () {
    'use strict';

    /* Smooth scroll for TOC links */
    document.querySelectorAll('.toc-item').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', this.getAttribute('href'));
            }
        });
    });

    /* Highlight active section while scrolling */
    var sections = document.querySelectorAll('.legal-section');
    var tocItems = document.querySelectorAll('.toc-item');

    if (!sections.length || !tocItems.length) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var id = entry.target.id;
                tocItems.forEach(function (item) {
                    item.classList.toggle('active',
                        item.getAttribute('href') === '#' + id
                    );
                });
            }
        });
    }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });

    sections.forEach(function (s) { observer.observe(s); });

    /* Add .active styling */
    var style = document.createElement('style');
    style.textContent = '.toc-item.active{background:rgba(16,185,129,.12);color:#34d399;}';
    document.head.appendChild(style);
})();
</script>