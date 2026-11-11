const mapContainerSelector = '#adminRouteMap, #adminRouteCreateMap, #adminRouteEditMap, #publicServicesMap';
let mapModulePromise = null;
let portalScrollObserver = null;

const loadMapLibreWhenNeeded = () => {
    if (!document.querySelector(mapContainerSelector) || window.MadiunMapLibre) {
        return;
    }

    mapModulePromise ??= import('./maps/maplibre');
};

const initPortalScrollFade = () => {
    const targets = Array.from(document.querySelectorAll('[data-scroll-fade]'));
    if (targets.length === 0) {
        return;
    }

    if (portalScrollObserver) {
        portalScrollObserver.disconnect();
        portalScrollObserver = null;
    }

    const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        document.documentElement.classList.remove('portal-scroll-ready');
        targets.forEach((el) => el.setAttribute('data-visible', 'true'));
        return;
    }

    targets.forEach((el) => {
        const rect = el.getBoundingClientRect();
        const isInInitialViewport = rect.top < window.innerHeight * 0.92 && rect.bottom > 0;
        el.setAttribute('data-visible', isInInitialViewport ? 'true' : 'false');
    });

    document.documentElement.classList.add('portal-scroll-ready');

    portalScrollObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.setAttribute('data-visible', 'true');
                } else {
                    entry.target.setAttribute('data-visible', 'false');
                }
            });
        },
        {
            threshold: 0.06,
            rootMargin: '0px 0px -24px 0px',
        },
    );

    targets.forEach((el) => portalScrollObserver.observe(el));
};

window.MadiunBootScrollFade = initPortalScrollFade;

const AJAX_LINK_SELECTOR = 'a[data-ajax-link]';
const AJAX_PAGINATION_SELECTOR = '[data-ajax-zone] nav[role="navigation"] a[href], [data-ajax-zone] nav[aria-label*="Pagination" i] a[href], [data-ajax-zone] .pagination a[href]';

const initUniversalAsyncNavigation = () => {
    if (window.__madiunAsyncInitialized) {
        return;
    }

    window.__madiunAsyncInitialized = true;

    let activeController = null;
    let lastAsyncPathname = window.location.pathname;

    document.addEventListener('livewire:navigated', () => {
        lastAsyncPathname = window.location.pathname;
    });

    const getExplicitTargetsSelector = (sourceEl) => {
        if (!sourceEl) {
            return null;
        }

        return (
            sourceEl.getAttribute('data-ajax-targets') ||
            sourceEl.closest('[data-ajax-targets]')?.getAttribute('data-ajax-targets') ||
            null
        );
    };

    const resolveTargetDescriptors = (sourceEl) => {
        const descriptors = [];
        const explicit = getExplicitTargetsSelector(sourceEl);

        if (explicit) {
            explicit
                .split(',')
                .map((selector) => selector.trim())
                .filter(Boolean)
                .forEach((selector) => {
                    const liveEl = document.querySelector(selector);
                    if (liveEl) {
                        descriptors.push({
                            liveEl,
                            findInParsed: (doc) => doc.querySelector(selector),
                        });
                    }
                });

            return descriptors;
        }

        const zoneEl = sourceEl?.closest('[data-ajax-zone]') || document.querySelector('[data-ajax-zone]');
        if (!zoneEl) {
            return descriptors;
        }

        const zoneAttr = zoneEl.getAttribute('data-ajax-zone');
        const zoneSelector = zoneAttr ? `[data-ajax-zone="${zoneAttr}"]` : '[data-ajax-zone]';
        const regions = Array.from(zoneEl.querySelectorAll('[data-ajax-region]'));

        if (regions.length > 0) {
            regions.forEach((liveRegion, index) => {
                const regionName = liveRegion.getAttribute('data-ajax-region');
                descriptors.push({
                    liveEl: liveRegion,
                    findInParsed: (doc) => {
                        const parsedZone = doc.querySelector(zoneSelector);
                        if (!parsedZone) {
                            return null;
                        }
                        if (regionName) {
                            return parsedZone.querySelector(`[data-ajax-region="${regionName}"]`);
                        }

                        return parsedZone.querySelectorAll('[data-ajax-region]')[index] || null;
                    },
                });
            });

            return descriptors;
        }

        descriptors.push({
            liveEl: zoneEl,
            findInParsed: (doc) => doc.querySelector(zoneSelector),
        });

        return descriptors;
    };

    const collectAllPageDescriptors = () => {
        const descriptors = [];
        const seen = new Set();

        const pushUnique = (item) => {
            if (item?.liveEl && !seen.has(item.liveEl)) {
                seen.add(item.liveEl);
                descriptors.push(item);
            }
        };

        document.querySelectorAll('[data-ajax-targets]').forEach((el) => {
            resolveTargetDescriptors(el).forEach(pushUnique);
        });

        document.querySelectorAll('[data-ajax-zone]').forEach((zoneEl) => {
            resolveTargetDescriptors(zoneEl).forEach(pushUnique);
        });

        return descriptors;
    };

    const syncFormControlsFromParsed = (parsedDoc, urlString) => {
        const parsedForms = Array.from(parsedDoc.querySelectorAll('form[data-ajax-form]'));
        const liveForms = Array.from(document.querySelectorAll('form[data-ajax-form]'));
        const fallbackUrl = new URL(urlString, window.location.origin);

        liveForms.forEach((liveForm, index) => {
            const formId = liveForm.getAttribute('id');
            const matchedParsedForm =
                (formId ? parsedDoc.getElementById(formId) : null) ||
                parsedForms[index] ||
                null;

            liveForm.querySelectorAll('[data-select-filter] input[type="hidden"][name]').forEach((hiddenInput) => {
                const name = hiddenInput.getAttribute('name');
                if (!name) {
                    return;
                }

                const parsedInput = matchedParsedForm?.querySelector(`input[name="${CSS.escape(name)}"]`);
                const nextValue = parsedInput
                    ? parsedInput.value
                    : (fallbackUrl.searchParams.get(name) ?? '');

                window.dispatchEvent(
                    new CustomEvent('select-filter-sync', {
                        detail: { name, value: nextValue },
                    })
                );
            });

            liveForm
                .querySelectorAll('input[name]:not([type="checkbox"]):not([type="radio"]), textarea[name]')
                .forEach((field) => {
                    if (field.closest('[data-select-filter]')) {
                        return;
                    }

                    const name = field.getAttribute('name');
                    if (!name) {
                        return;
                    }

                    const parsedField = matchedParsedForm?.querySelector(`[name="${CSS.escape(name)}"]`);
                    const nextValue = parsedField
                        ? parsedField.value
                        : (fallbackUrl.searchParams.get(name) ?? '');

                    if (field.value !== nextValue) {
                        field.value = nextValue;
                    }
                });

            liveForm.querySelectorAll('input[type="checkbox"][name], input[type="radio"][name]').forEach((box) => {
                const name = box.getAttribute('name');
                const val = box.getAttribute('value');
                if (!name) {
                    return;
                }

                const selector = val !== null
                    ? `input[name="${CSS.escape(name)}"][value="${CSS.escape(val)}"]`
                    : `input[name="${CSS.escape(name)}"]`;
                const parsedBox = matchedParsedForm?.querySelector(selector);
                if (parsedBox) {
                    box.checked = parsedBox.checked;
                }
            });
        });
    };

    const fetchAndSwap = async (targetUrl, sourceEl, options = {}) => {
        const { pushHistory = true, scrollTarget = null } = options;
        const descriptors = sourceEl
            ? resolveTargetDescriptors(sourceEl)
            : collectAllPageDescriptors();

        if (descriptors.length === 0) {
            window.location.assign(targetUrl);
            return;
        }

        if (activeController) {
            activeController.abort();
        }

        const controller = new AbortController();
        activeController = controller;

        const visualEls = descriptors
            .map((d) => d.liveEl)
            .filter((el) => el && el.tagName !== 'SCRIPT');

        visualEls.forEach((el) => el.classList.add('is-ajax-loading'));

        try {
            const response = await fetch(targetUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html, application/xhtml+xml',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            if (response.redirected && new URL(response.url).pathname !== window.location.pathname) {
                window.location.assign(response.url);
                return;
            }

            const html = await response.text();
            const parsedDoc = new DOMParser().parseFromString(html, 'text/html');

            const missingRequired = descriptors.some(({ findInParsed }) => !findInParsed(parsedDoc));
            if (missingRequired) {
                window.location.assign(targetUrl);
                return;
            }

            descriptors.forEach(({ liveEl, findInParsed }) => {
                const nextEl = findInParsed(parsedDoc);
                if (nextEl) {
                    liveEl.innerHTML = nextEl.innerHTML;
                }
            });

            const finalUrl = response.url || targetUrl;
            syncFormControlsFromParsed(parsedDoc, finalUrl);

            if (pushHistory) {
                window.history.pushState({ madiunAsync: true }, '', finalUrl);
                lastAsyncPathname = new URL(finalUrl, window.location.origin).pathname;
            }

            if (scrollTarget) {
                const rect = scrollTarget.getBoundingClientRect();
                if (rect.top < 72 || rect.top > window.innerHeight * 0.75) {
                    scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            initPortalScrollFade();

            document.dispatchEvent(
                new CustomEvent('madiun:ajax-updated', {
                    detail: { url: finalUrl, parsedDoc },
                })
            );
        } catch (error) {
            if (error?.name === 'AbortError') {
                return;
            }

            window.location.assign(targetUrl);
        } finally {
            if (activeController === controller) {
                activeController = null;
                visualEls.forEach((el) => el.classList.remove('is-ajax-loading'));
            }
        }
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-ajax-form]');
        if (!form) {
            return;
        }

        if ((form.getAttribute('method') || 'GET').toUpperCase() !== 'GET') {
            return;
        }

        event.preventDefault();

        const rawAction = form.getAttribute('action') || window.location.pathname;
        const url = new URL(rawAction, window.location.origin);
        const keepEmpty = form.hasAttribute('data-ajax-keep-empty');
        const nextParams = new URLSearchParams();
        const formData = new FormData(form);

        if (event.submitter && event.submitter.name) {
            formData.set(event.submitter.name, event.submitter.value || '');
        }

        formData.forEach((value, key) => {
            const strVal = String(value);
            if (keepEmpty || strVal.trim() !== '') {
                nextParams.append(key, strVal);
            }
        });

        url.search = nextParams.toString();

        const scrollTarget = form.hasAttribute('data-ajax-scroll-top')
            ? resolveTargetDescriptors(form)[0]?.liveEl || null
            : null;

        fetchAndSwap(url.toString(), form, { scrollTarget });
    });

    document.addEventListener('click', (event) => {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
            return;
        }

        const anchor = event.target.closest(`${AJAX_LINK_SELECTOR}, ${AJAX_PAGINATION_SELECTOR}`);
        if (!anchor || anchor.hasAttribute('data-no-ajax') || anchor.getAttribute('target') === '_blank' || anchor.hasAttribute('download')) {
            return;
        }

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
            return;
        }

        const targetUrl = new URL(href, window.location.origin);
        if (targetUrl.origin !== window.location.origin || targetUrl.pathname !== window.location.pathname) {
            return;
        }

        event.preventDefault();

        const isPagination = Boolean(anchor.closest('nav[role="navigation"], nav[aria-label*="Pagination" i], .pagination'));
        const descriptors = resolveTargetDescriptors(anchor);
        const scrollTarget = isPagination || anchor.hasAttribute('data-ajax-scroll')
            ? (descriptors.find((d) => d.liveEl.tagName !== 'SCRIPT')?.liveEl || null)
            : null;

        fetchAndSwap(targetUrl.toString(), anchor, { scrollTarget });
    });

    window.addEventListener('popstate', () => {
        if (window.location.pathname !== lastAsyncPathname) {
            return;
        }

        if (!document.querySelector('[data-ajax-form], [data-ajax-zone]')) {
            return;
        }

        fetchAndSwap(window.location.href, null, { pushHistory: false });
    });
};

const bootUi = () => {
    loadMapLibreWhenNeeded();
    initPortalScrollFade();
    initUniversalAsyncNavigation();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootUi);
} else {
    bootUi();
}

document.addEventListener('livewire:navigated', bootUi);
