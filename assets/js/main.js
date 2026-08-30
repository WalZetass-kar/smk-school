/**
 * main.js — public site behaviour & interactive UI.
 * Scroll Animations, Skeleton Loading, Top Progress Bar & Micro-interactions.
 */

// Top page loading bar progress simulation
(function () {
    const bar = document.createElement('div');
    bar.id = 'page-progress-bar';
    document.documentElement.appendChild(bar);

    let progress = 10;
    bar.style.width = progress + '%';

    const timer = setInterval(() => {
        if (progress < 85) {
            progress += Math.random() * 15;
            bar.style.width = Math.min(progress, 85) + '%';
        }
    }, 150);

    window.addEventListener('load', () => {
        clearInterval(timer);
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => bar.remove(), 400);
        }, 200);
    });

    // Simulate progress bar on link clicks
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link || link.target === '_blank' || link.getAttribute('href')?.startsWith('#') || link.getAttribute('href')?.startsWith('javascript:')) return;
        if (link.hostname === window.location.hostname && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            const newBar = document.createElement('div');
            newBar.id = 'page-progress-bar';
            newBar.style.width = '45%';
            document.documentElement.appendChild(newBar);
        }
    });
})();

document.addEventListener('DOMContentLoaded', function () {

    /* ---- 1. Scroll Reveal Animations (IntersectionObserver) ---- */
    const revealElements = document.querySelectorAll('.reveal, .reveal-up, .reveal-left, .reveal-right, .reveal-zoom, .reveal-fade');
    if (revealElements.length && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        });

        revealElements.forEach((el) => revealObserver.observe(el));
    } else {
        // Fallback if IntersectionObserver is not supported
        revealElements.forEach((el) => el.classList.add('is-revealed'));
    }

    // Auto stagger cascade for .reveal-group children
    document.querySelectorAll('.reveal-group').forEach((group) => {
        const children = group.children;
        Array.from(children).forEach((child, index) => {
            if (!child.classList.contains('reveal') && !child.classList.contains('reveal-up')) {
                child.classList.add('reveal-up');
            }
            const delayClass = `delay-${Math.min((index % 6) + 1, 6)}`;
            child.classList.add(delayClass);
            if ('IntersectionObserver' in window) {
                const obs = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });
                obs.observe(child);
            } else {
                child.classList.add('is-revealed');
            }
        });
    });

    /* ---- 2. Progressive Image Skeleton Loading ---- */
    const lazyImages = document.querySelectorAll('img');
    lazyImages.forEach((img) => {
        img.classList.add('lazy-fade');
        if (img.complete) {
            img.classList.add('is-loaded');
            const wrapper = img.closest('.img-skeleton-wrapper');
            if (wrapper) wrapper.classList.add('loaded');
        } else {
            img.addEventListener('load', function () {
                img.classList.add('is-loaded');
                const wrapper = img.closest('.img-skeleton-wrapper');
                if (wrapper) wrapper.classList.add('loaded');
            });
            img.addEventListener('error', function () {
                img.classList.add('is-loaded');
                const wrapper = img.closest('.img-skeleton-wrapper');
                if (wrapper) wrapper.classList.add('loaded');
            });
        }
    });

    /* ---- 3. Sticky navbar on scroll ---- */
    const navbar = document.querySelector('.navbar-smk');
    if (navbar) {
        const toggleSticky = () => {
            navbar.classList.toggle('is-sticky', window.scrollY > 40);
        };
        toggleSticky();
        window.addEventListener('scroll', toggleSticky, { passive: true });
    }

    /* ---- 4. Back-to-top button ---- */
    const backToTop = document.querySelector('.btn-back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('show', window.scrollY > 400);
        }, { passive: true });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---- 5. Statistik counter animation (triggered upon scroll into view) ---- */
    const statValues = document.querySelectorAll('[data-counter-target]');
    if (statValues.length) {
        const animateCounter = (el) => {
            const target = parseInt(el.dataset.counterTarget, 10) || 0;
            const duration = 1400;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target).toLocaleString('id-ID');
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target.toLocaleString('id-ID');
                }
            };
            requestAnimationFrame(step);
        };

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        statValues.forEach((el) => observer.observe(el));
    }

    /* ---- 6. Simple image preview before upload (used on admin CRUD forms) ---- */
    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        input.addEventListener('change', function () {
            const previewEl = document.querySelector(this.dataset.preview);
            if (!previewEl || !this.files || !this.files[0]) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                previewEl.src = e.target.result;
                previewEl.classList.remove('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        });
    });

    /* ---- 7. Delete confirmation (admin CRUD) ---- */
    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            const msg = form.dataset.confirmDelete || 'Yakin ingin menghapus data ini?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // Ensure pure light theme
    localStorage.removeItem('smk_theme');
    document.documentElement.removeAttribute('data-theme');

    /* ---- 8. Global Live Search (Modal + Keyboard Shortcut Ctrl+K) ---- */
    const searchModalEl = document.getElementById('globalSearchModal');
    const searchInput = document.getElementById('globalSearchInput');
    const searchResultsList = document.getElementById('searchResultsList');
    const searchSpinner = document.getElementById('searchResultsSpinner');
    const searchEmptyMessage = document.getElementById('searchEmptyMessage');
    const searchQuickLinks = document.getElementById('searchQuickLinks');

    // Global keyboard shortcut Ctrl + K
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (searchModalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getOrCreateInstance(searchModalEl);
                modal.show();
            }
        }
    });

    if (searchModalEl && searchInput) {
        searchModalEl.addEventListener('shown.bs.modal', () => {
            searchInput.focus();
        });

        let debounceTimeout = null;

        const performSearch = (query) => {
            query = query.trim();
            if (query.length < 2) {
                searchResultsList.innerHTML = '';
                if (searchQuickLinks) searchQuickLinks.classList.remove('d-none');
                if (searchEmptyMessage) searchEmptyMessage.classList.add('d-none');
                if (searchSpinner) searchSpinner.classList.add('d-none');
                return;
            }

            if (searchQuickLinks) searchQuickLinks.classList.add('d-none');
            if (searchSpinner) searchSpinner.classList.remove('d-none');
            if (searchEmptyMessage) searchEmptyMessage.classList.add('d-none');

            fetch(`${window.location.origin}/api/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (searchSpinner) searchSpinner.classList.add('d-none');
                    searchResultsList.innerHTML = '';

                    if (!data.results || data.results.length === 0) {
                        if (searchEmptyMessage) searchEmptyMessage.classList.remove('d-none');
                        return;
                    }

                    if (searchEmptyMessage) searchEmptyMessage.classList.add('d-none');

                    data.results.forEach(item => {
                        const itemEl = document.createElement('a');
                        itemEl.href = item.url;
                        itemEl.className = 'wa-contact-item hover-lift text-decoration-none';
                        itemEl.innerHTML = `
                            <div class="wa-contact-icon ${item.badge}">
                                <i class="bi ${item.icon}"></i>
                            </div>
                            <div class="wa-contact-info flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge ${item.badge} px-2 py-0" style="font-size: 0.7rem;">${item.category}</span>
                                </div>
                                <div class="fw-bold text-dark text-truncate">${item.title}</div>
                                <div class="text-muted small text-truncate">${item.subtitle}</div>
                            </div>
                            <i class="bi bi-chevron-right text-muted ms-2"></i>
                        `;
                        searchResultsList.appendChild(itemEl);
                    });
                })
                .catch(() => {
                    if (searchSpinner) searchSpinner.classList.add('d-none');
                });
        };

        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => performSearch(e.target.value), 250);
        });

        // Quick tags click handler
        document.querySelectorAll('.quick-search-tag').forEach(tag => {
            tag.addEventListener('click', () => {
                searchInput.value = tag.dataset.query;
                performSearch(tag.dataset.query);
            });
        });
    }

    /* ---- 10. Multi-CS WhatsApp Floating Widget Toggle ---- */
    const waToggleBtn = document.getElementById('waToggleBtn');
    const waPopupCard = document.getElementById('waPopupCard');
    const waCloseBtn = document.getElementById('waCloseBtn');

    if (waToggleBtn && waPopupCard) {
        waToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            waPopupCard.classList.toggle('d-none');
        });

        if (waCloseBtn) {
            waCloseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                waPopupCard.classList.add('d-none');
            });
        }

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!waPopupCard.contains(e.target) && !waToggleBtn.contains(e.target)) {
                waPopupCard.classList.add('d-none');
            }
        });
    }
});


