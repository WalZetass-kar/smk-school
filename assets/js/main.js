/**
 * main.js — public site behaviour & interactive UI.
 * Scroll Animations, Sticky Navbar, Live Global Search, WhatsApp Widget.
 */

// Top page loading bar progress simulation
(function () {
    const bar = document.createElement('div');
    bar.id = 'page-progress-bar';
    document.documentElement.appendChild(bar);

    let progress = 15;
    bar.style.width = progress + '%';

    const timer = setInterval(() => {
        if (progress < 85) {
            progress += Math.random() * 15;
            bar.style.width = Math.min(progress, 85) + '%';
        }
    }, 120);

    window.addEventListener('load', () => {
        clearInterval(timer);
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => bar.remove(), 350);
        }, 150);
    });

    // Simulate progress bar on internal link clicks
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

    /* ---- 1. Safe Scroll Reveal Animations ---- */
    if ('IntersectionObserver' in window) {
        document.documentElement.classList.add('js-anim');
        const revealElements = document.querySelectorAll('.reveal, .reveal-up, .reveal-left, .reveal-right, .reveal-zoom, .reveal-fade');
        
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -20px 0px'
        });

        revealElements.forEach((el) => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight) {
                // Immediately reveal items visible above the fold
                el.classList.add('is-revealed');
            } else {
                revealObserver.observe(el);
            }
        });

        // Stagger cascade for .reveal-group children
        document.querySelectorAll('.reveal-group').forEach((group) => {
            const children = Array.from(group.children);
            children.forEach((child, index) => {
                if (!child.classList.contains('reveal') && !child.classList.contains('reveal-up')) {
                    child.classList.add('reveal-up');
                }
                const delayClass = `delay-${Math.min((index % 4) + 1, 4)}`;
                child.classList.add(delayClass);
                
                const rect = child.getBoundingClientRect();
                if (rect.top < window.innerHeight) {
                    child.classList.add('is-revealed');
                } else {
                    revealObserver.observe(child);
                }
            });
        });
    }

    /* ---- 2. Sticky navbar on scroll ---- */
    const navbar = document.querySelector('.navbar-smk');
    if (navbar) {
        const toggleSticky = () => {
            navbar.classList.toggle('is-sticky', window.scrollY > 30);
        };
        toggleSticky();
        window.addEventListener('scroll', toggleSticky, { passive: true });
    }

    /* ---- 3. Back-to-top button ---- */
    const backToTop = document.querySelector('.btn-back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('show', window.scrollY > 350);
        }, { passive: true });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---- 4. Counter animation for statistics ---- */
    const statValues = document.querySelectorAll('[data-counter-target]');
    if (statValues.length) {
        const animateCounter = (el) => {
            const target = parseInt(el.dataset.counterTarget, 10) || 0;
            const duration = 1200;
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

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            statValues.forEach((el) => observer.observe(el));
        } else {
            statValues.forEach((el) => animateCounter(el));
        }
    }

    /* ---- 5. Admin Form Helpers ---- */
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

    /* ---- 5. Universal Confirmation Modal for Deletions & Actions ---- */
    const uniModalEl = document.getElementById('universalConfirmModal');
    const uniIconWrap = document.getElementById('universalConfirmIconWrap');
    const uniIcon = document.getElementById('universalConfirmIcon');
    const uniTitle = document.getElementById('universalConfirmTitle');
    const uniMessage = document.getElementById('universalConfirmMessage');
    const uniBtn = document.getElementById('universalConfirmBtn');
    const uniBtnText = document.getElementById('universalConfirmBtnText');

    let pendingConfirmAction = null;

    if (uniModalEl && window.bootstrap) {
        const uniModal = bootstrap.Modal.getOrCreateInstance(uniModalEl);

        if (uniBtn) {
            uniBtn.addEventListener('click', () => {
                if (typeof pendingConfirmAction === 'function') {
                    const action = pendingConfirmAction;
                    pendingConfirmAction = null;
                    uniModal.hide();
                    action();
                }
            });
        }

        const showConfirmDialog = (options, onConfirm) => {
            const isDelete = options.type === 'delete' || /hapus|delete|trash|bersihkan|kosongkan/i.test(options.message || '');
            const isPreset = options.type === 'preset' || /preset|terapkan/i.test(options.message || '');

            if (isDelete) {
                if (uniIconWrap) uniIconWrap.className = 'p-3 bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3';
                if (uniIcon) uniIcon.className = 'bi bi-trash3-fill fs-2';
                if (uniTitle) uniTitle.textContent = options.title || 'Konfirmasi Hapus Data';
                if (uniBtn) uniBtn.className = 'btn btn-danger w-50 py-2 fw-semibold rounded-3 d-inline-flex align-items-center justify-content-center gap-1';
                if (uniBtnText) uniBtnText.textContent = options.btnText || 'Ya, Hapus Data';
            } else if (isPreset) {
                if (uniIconWrap) uniIconWrap.className = 'p-3 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3';
                if (uniIcon) uniIcon.className = 'bi bi-magic fs-2';
                if (uniTitle) uniTitle.textContent = options.title || 'Konfirmasi Terapkan Preset';
                if (uniBtn) uniBtn.className = 'btn btn-primary w-50 py-2 fw-semibold rounded-3 d-inline-flex align-items-center justify-content-center gap-1';
                if (uniBtnText) uniBtnText.textContent = options.btnText || 'Ya, Terapkan';
            } else {
                if (uniIconWrap) uniIconWrap.className = 'p-3 bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3';
                if (uniIcon) uniIcon.className = 'bi bi-exclamation-triangle-fill fs-2';
                if (uniTitle) uniTitle.textContent = options.title || 'Konfirmasi Tindakan';
                if (uniBtn) uniBtn.className = 'btn btn-primary w-50 py-2 fw-semibold rounded-3 d-inline-flex align-items-center justify-content-center gap-1';
                if (uniBtnText) uniBtnText.textContent = options.btnText || 'Ya, Lanjutkan';
            }

            if (uniMessage) uniMessage.textContent = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            pendingConfirmAction = onConfirm;
            uniModal.show();
        };

        // Intercept forms with data-confirm-delete or data-confirm
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form) return;

            if (form._bypassConfirm) {
                return; // already confirmed
            }

            const confirmMsg = form.dataset.confirmDelete || form.dataset.confirm;
            if (confirmMsg) {
                e.preventDefault();
                showConfirmDialog({
                    message: confirmMsg,
                    type: form.dataset.confirmDelete ? 'delete' : 'action'
                }, () => {
                    form._bypassConfirm = true;
                    form.submit();
                });
            }
        });

        // Intercept links or buttons with data-confirm or data-confirm-delete
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-confirm], [data-confirm-delete]');
            if (!btn || btn.tagName === 'FORM') return;

            const confirmMsg = btn.dataset.confirmDelete || btn.dataset.confirm;
            if (confirmMsg) {
                e.preventDefault();
                showConfirmDialog({
                    message: confirmMsg,
                    type: btn.dataset.confirmDelete ? 'delete' : 'action'
                }, () => {
                    if (btn.tagName === 'A' && btn.href) {
                        window.location.href = btn.href;
                    } else if (btn.type === 'submit' && btn.form) {
                        btn.form._bypassConfirm = true;
                        btn.form.submit();
                    }
                });
            }
        });
    } else {
        // Fallback for pages without the bootstrap modal
        document.querySelectorAll('[data-confirm-delete], [data-confirm]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                const msg = form.dataset.confirmDelete || form.dataset.confirm || 'Yakin ingin melanjutkan?';
                if (!confirm(msg)) e.preventDefault();
            });
        });
    }

    /* ---- 6. Global Live Search (Modal + Keyboard Shortcut Ctrl+K) ---- */
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

            // Find base URL from canonical or current path
            const baseCanonical = document.querySelector('link[rel="canonical"]')?.getAttribute('href') || '/';
            const baseOrigin = window.location.origin;

            fetch(`/api/search?q=${encodeURIComponent(query)}`)
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
                        itemEl.className = 'wa-contact-item hover-lift text-decoration-none mb-2';
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

    /* ---- 7. Multi-CS WhatsApp Floating Widget Toggle ---- */
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

        document.addEventListener('click', (e) => {
            if (!waPopupCard.contains(e.target) && !waToggleBtn.contains(e.target)) {
                waPopupCard.classList.add('d-none');
            }
        });
    }

    /* ---- 8. Admin Sidebar Scroll Memory & Tab Persistence ---- */
    const adminSidebarNav = document.querySelector('.admin-sidebar .sidebar-nav-scroll');
    if (adminSidebarNav) {
        // Restore scroll position immediately
        const savedScroll = sessionStorage.getItem('admin_sidebar_scroll');
        if (savedScroll !== null) {
            adminSidebarNav.scrollTop = parseInt(savedScroll, 10);
        }

        // Save scroll position when scrolling
        let scrollTimeout = null;
        adminSidebarNav.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                sessionStorage.setItem('admin_sidebar_scroll', adminSidebarNav.scrollTop);
            }, 40);
        }, { passive: true });

        // Save scroll position on any sidebar link click
        adminSidebarNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                sessionStorage.setItem('admin_sidebar_scroll', adminSidebarNav.scrollTop);
            });
        });

        // Ensure active item is visible in viewport if it wasn't
        const activeLink = adminSidebarNav.querySelector('.nav-link.active');
        if (activeLink && savedScroll === null) {
            const rect = activeLink.getBoundingClientRect();
            const containerRect = adminSidebarNav.getBoundingClientRect();
            if (rect.top < containerRect.top || rect.bottom > containerRect.bottom) {
                activeLink.scrollIntoView({ block: 'nearest', behavior: 'instant' });
            }
        }
    }

    /* ---- 9. Fast Page Transition Top Progress Indicator ---- */
    const topProgressBar = document.getElementById('pageTopProgress');
    if (topProgressBar) {
        // Trigger on any admin internal navigation link
        document.querySelectorAll('a[href]:not([target="_blank"]):not([data-bs-toggle]):not([data-confirm]):not([data-confirm-delete]):not([href^="#"]):not([href^="javascript:"])').forEach((a) => {
            a.addEventListener('click', (e) => {
                if (e.ctrlKey || e.metaKey || e.shiftKey || a.download) return;
                try {
                    const url = new URL(a.href, window.location.href);
                    if (url.origin === window.location.origin) {
                        topProgressBar.style.opacity = '1';
                        topProgressBar.style.width = '65%';
                    }
                } catch (_) {}
            });
        });

        // Hide when page is ready
        window.addEventListener('load', () => {
            topProgressBar.style.width = '100%';
            setTimeout(() => {
                topProgressBar.style.opacity = '0';
                setTimeout(() => { topProgressBar.style.width = '0%'; }, 300);
            }, 150);
        });
    }
});
