/**
 * Queen Al-Falah front-end interactions.
 *
 * No external dependency is required. All controls are progressively enhanced,
 * keyboard operable, and respect the user's reduced-motion preference.
 */

(function () {
  'use strict';

  document.documentElement.classList.add('js-enabled');

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const desktopNavigation = window.matchMedia('(min-width: 48rem)');
  const strings = Object.assign(
    {
      expand: 'Buka submenu',
      collapse: 'Tutup submenu',
      menuOpen: 'Buka menu',
      menuClose: 'Tutup menu',
      searchOpen: 'Buka pencarian',
      searchClose: 'Tutup pencarian',
    },
    window.queenTheme || {}
  );

  const ready = (callback) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
      return;
    }

    callback();
  };

  const uniqueId = (prefix) => {
    let id = '';

    do {
      id = `${prefix}-${Math.random().toString(36).slice(2, 9)}`;
    } while (document.getElementById(id));

    return id;
  };

  const directChild = (parent, selector) => {
    return Array.from(parent.children).find((child) => child.matches(selector)) || null;
  };

  const addMediaListener = (mediaQuery, callback) => {
    if (typeof mediaQuery.addEventListener === 'function') {
      mediaQuery.addEventListener('change', callback);
    } else if (typeof mediaQuery.addListener === 'function') {
      mediaQuery.addListener(callback);
    }
  };

  function initNavigation() {
    const toggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.site-nav');

    if (!navigation) {
      return;
    }

    if (!navigation.id) {
      navigation.id = uniqueId('site-navigation');
    }

    const updateSubmenuButton = (button, isOpen) => {
      const label = button.dataset.submenuLabel || 'navigasi';
      button.setAttribute('aria-expanded', String(isOpen));
      button.setAttribute(
        'aria-label',
        `${isOpen ? strings.collapse : strings.expand}: ${label}`
      );
    };

    const closeAllSubmenus = (exceptItem = null) => {
      navigation.querySelectorAll('.menu-item-has-children.is-submenu-open').forEach((item) => {
        if (item === exceptItem || (exceptItem && item.contains(exceptItem))) {
          return;
        }

        const button = directChild(item, '.submenu-toggle, .dropdown-toggle');
        const submenu = directChild(item, '.sub-menu');
        item.classList.remove('is-submenu-open');

        if (button) {
          updateSubmenuButton(button, false);
        }

        if (submenu) {
          submenu.classList.remove('is-open');
        }
      });
    };

    navigation.querySelectorAll('.menu-item-has-children').forEach((item) => {
      const submenu = directChild(item, '.sub-menu');
      const link = directChild(item, 'a');

      if (!submenu) {
        return;
      }

      if (!submenu.id) {
        submenu.id = uniqueId('submenu');
      }

      let submenuToggle = directChild(item, '.submenu-toggle, .dropdown-toggle');

      if (!submenuToggle) {
        submenuToggle = document.createElement('button');
        submenuToggle.type = 'button';
        submenuToggle.className = 'submenu-toggle';
        if (link) {
          link.insertAdjacentElement('afterend', submenuToggle);
        } else {
          item.insertBefore(submenuToggle, submenu);
        }
      }

      submenuToggle.dataset.submenuLabel = link ? link.textContent.trim() : 'navigasi';
      submenuToggle.setAttribute('aria-controls', submenu.id);
      updateSubmenuButton(submenuToggle, false);

      submenuToggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        const willOpen = submenuToggle.getAttribute('aria-expanded') !== 'true';

        if (willOpen) {
          const siblings = item.parentElement
            ? Array.from(item.parentElement.children).filter((sibling) => sibling !== item)
            : [];

          siblings.forEach((sibling) => {
            const siblingButton = directChild(sibling, '.submenu-toggle, .dropdown-toggle');
            const siblingMenu = directChild(sibling, '.sub-menu');
            sibling.classList.remove('is-submenu-open');

            if (siblingButton) {
              updateSubmenuButton(siblingButton, false);
            }

            if (siblingMenu) {
              siblingMenu.classList.remove('is-open');
            }
          });
        }

        updateSubmenuButton(submenuToggle, willOpen);
        item.classList.toggle('is-submenu-open', willOpen);
        submenu.classList.toggle('is-open', willOpen);
      });
    });

    const setMenuState = (isOpen, restoreFocus = false) => {
      if (!toggle) {
        return;
      }

      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? strings.menuClose : strings.menuOpen);
      navigation.classList.toggle('is-open', isOpen);
      document.body.classList.toggle('nav-open', isOpen && !desktopNavigation.matches);

      if (!desktopNavigation.matches) {
        navigation.setAttribute('aria-hidden', String(!isOpen));
      } else {
        navigation.removeAttribute('aria-hidden');
      }

      if (!isOpen) {
        closeAllSubmenus();
      }

      if (restoreFocus) {
        toggle.focus();
      }
    };

    if (toggle) {
      toggle.setAttribute('aria-controls', navigation.id);
      toggle.setAttribute('aria-expanded', 'false');
      setMenuState(false);

      toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        setMenuState(!isOpen);

        if (!isOpen) {
          const firstLink = navigation.querySelector('a, button');
          if (firstLink) {
            window.setTimeout(() => firstLink.focus(), 30);
          }
        }
      });
    }

    navigation.addEventListener('click', (event) => {
      const link = event.target.closest('a');

      if (!link || desktopNavigation.matches) {
        return;
      }

      const parent = link.parentElement;
      const hasSubmenu = parent && directChild(parent, '.sub-menu');

      if (!hasSubmenu) {
        setMenuState(false);
      }
    });

    document.addEventListener('click', (event) => {
      if (!desktopNavigation.matches) {
        const clickedToggle = toggle && toggle.contains(event.target);

        if (
          toggle &&
          toggle.getAttribute('aria-expanded') === 'true' &&
          !navigation.contains(event.target) &&
          !clickedToggle
        ) {
          setMenuState(false);
        }

        return;
      }

      if (navigation.contains(event.target)) {
        return;
      }

      closeAllSubmenus();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape') {
        return;
      }

      const openSubmenuButton = Array.from(
        navigation.querySelectorAll('.submenu-toggle[aria-expanded="true"], .dropdown-toggle[aria-expanded="true"]')
      ).pop();

      if (openSubmenuButton) {
        const item = openSubmenuButton.closest('.menu-item-has-children');
        const submenu = item ? directChild(item, '.sub-menu') : null;
        updateSubmenuButton(openSubmenuButton, false);

        if (item) {
          item.classList.remove('is-submenu-open');
        }

        if (submenu) {
          submenu.classList.remove('is-open');
        }

        openSubmenuButton.focus();
        return;
      }

      if (toggle && toggle.getAttribute('aria-expanded') === 'true') {
        setMenuState(false, true);
      }
    });

    addMediaListener(desktopNavigation, (event) => {
      if (event.matches) {
        document.body.classList.remove('nav-open');
        navigation.classList.remove('is-open');
        navigation.removeAttribute('aria-hidden');

        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
          toggle.setAttribute('aria-label', strings.menuOpen);
        }
      } else {
        setMenuState(false);
      }

      closeAllSubmenus();
    });
  }

  function initHeaderSearch() {
    const toggle = document.querySelector('.search-toggle');
    const panel = document.getElementById('header-search');

    if (!toggle || !panel) {
      return;
    }

    const field = panel.querySelector('input[type="search"]');
    const setState = (isOpen, restoreFocus = false) => {
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? strings.searchClose : strings.searchOpen);
      panel.hidden = !isOpen;

      if (isOpen && field) {
        window.setTimeout(() => field.focus(), 30);
      } else if (restoreFocus) {
        toggle.focus();
      }
    };

    setState(false);
    toggle.addEventListener('click', () => {
      setState(toggle.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setState(false, true);
      }
    });
  }

  function initStickyHeader() {
    const header = document.querySelector('.site-header');

    if (!header) {
      return;
    }

    let ticking = false;

    const update = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 16);
      ticking = false;
    };

    const requestUpdate = () => {
      if (!ticking) {
        window.requestAnimationFrame(update);
        ticking = true;
      }
    };

    update();
    window.addEventListener('scroll', requestUpdate, { passive: true });
  }

  function initAnnouncements() {
    document.querySelectorAll('.announcement-strip').forEach((announcement, index) => {
      const closeButton = announcement.querySelector(
        '[data-dismiss-announcement], .announcement-strip__close'
      );

      if (!closeButton) {
        return;
      }

      const announcementId =
        announcement.dataset.announcementId || announcement.id || `announcement-${index + 1}`;
      const storageKey = `queen-alfalah:${announcementId}:dismissed`;
      let wasDismissed = false;

      try {
        wasDismissed = window.sessionStorage.getItem(storageKey) === '1';
      } catch (error) {
        wasDismissed = false;
      }

      if (wasDismissed) {
        announcement.hidden = true;
        return;
      }

      closeButton.addEventListener('click', () => {
        announcement.classList.add('is-dismissed');
        closeButton.setAttribute('aria-expanded', 'false');

        try {
          window.sessionStorage.setItem(storageKey, '1');
        } catch (error) {
          // Storage can be unavailable in strict privacy modes; dismissal still works.
        }

        window.setTimeout(
          () => {
            announcement.hidden = true;
          },
          prefersReducedMotion.matches ? 0 : 320
        );
      });
    });
  }

  function initTabs() {
    document.querySelectorAll('[data-tabs], .tabs').forEach((tabs) => {
      const tabList = tabs.querySelector('[role="tablist"], .tabs__list');

      if (!tabList) {
        return;
      }

      tabList.setAttribute('role', 'tablist');

      const tabButtons = Array.from(
        tabList.querySelectorAll('[role="tab"], .tabs__tab')
      );
      const panels = Array.from(
        tabs.querySelectorAll('[role="tabpanel"], .tabs__panel')
      );

      if (!tabButtons.length || !panels.length) {
        return;
      }

      const activateTab = (activeTab, moveFocus = true) => {
        tabButtons.forEach((tab, tabIndex) => {
          const isActive = tab === activeTab;
          const controlledId = tab.getAttribute('aria-controls');
          const panel = controlledId
            ? document.getElementById(controlledId)
            : panels[tabIndex] || null;

          tab.setAttribute('aria-selected', String(isActive));
          tab.tabIndex = isActive ? 0 : -1;

          if (panel) {
            panel.hidden = !isActive;
            panel.classList.toggle('is-active', isActive);
          }
        });

        if (moveFocus) {
          activeTab.focus();
        }
      };

      tabButtons.forEach((tab, index) => {
        const panel = panels[index] || null;

        tab.setAttribute('role', 'tab');

        if (!tab.id) {
          tab.id = uniqueId('tab');
        }

        if (panel) {
          panel.setAttribute('role', 'tabpanel');

          if (!panel.id) {
            panel.id = uniqueId('tabpanel');
          }

          tab.setAttribute('aria-controls', panel.id);
          panel.setAttribute('aria-labelledby', tab.id);
        }

        tab.addEventListener('click', (event) => {
          event.preventDefault();
          activateTab(tab);
        });

        tab.addEventListener('keydown', (event) => {
          const currentIndex = tabButtons.indexOf(tab);
          let nextIndex = null;

          if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
            nextIndex = (currentIndex + 1) % tabButtons.length;
          } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
            nextIndex = (currentIndex - 1 + tabButtons.length) % tabButtons.length;
          } else if (event.key === 'Home') {
            nextIndex = 0;
          } else if (event.key === 'End') {
            nextIndex = tabButtons.length - 1;
          }

          if (nextIndex !== null) {
            event.preventDefault();
            activateTab(tabButtons[nextIndex]);
          }
        });
      });

      const initiallySelected =
        tabButtons.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabButtons[0];
      activateTab(initiallySelected, false);
    });
  }

  function initAccordions() {
    document.querySelectorAll('[data-accordion], .accordion').forEach((accordion) => {
      const triggers = Array.from(
        accordion.querySelectorAll('.accordion__button, .accordion__trigger')
      );
      const allowsMultiple = accordion.dataset.accordionMultiple !== 'false';

      triggers.forEach((trigger) => {
        const item = trigger.closest('.accordion__item') || trigger.parentElement;
        let panel = null;
        const controlledId = trigger.getAttribute('aria-controls');

        if (controlledId) {
          panel = document.getElementById(controlledId);
        }

        if (!panel && item) {
          panel = item.querySelector('.accordion__panel');
        }

        if (!panel) {
          return;
        }

        if (!panel.id) {
          panel.id = uniqueId('accordion-panel');
        }

        trigger.setAttribute('aria-controls', panel.id);

        if (trigger.tagName !== 'BUTTON') {
          trigger.setAttribute('role', 'button');
          trigger.tabIndex = 0;
        }

        const setExpanded = (isExpanded) => {
          trigger.setAttribute('aria-expanded', String(isExpanded));
          panel.hidden = !isExpanded;

          if (item) {
            item.classList.toggle('is-open', isExpanded);
          }
        };

        setExpanded(trigger.getAttribute('aria-expanded') === 'true');

        const togglePanel = () => {
          const willExpand = trigger.getAttribute('aria-expanded') !== 'true';

          if (willExpand && !allowsMultiple) {
            triggers.forEach((otherTrigger) => {
              if (otherTrigger === trigger) {
                return;
              }

              const otherId = otherTrigger.getAttribute('aria-controls');
              const otherPanel = otherId ? document.getElementById(otherId) : null;
              otherTrigger.setAttribute('aria-expanded', 'false');

              if (otherPanel) {
                otherPanel.hidden = true;
              }

              const otherItem = otherTrigger.closest('.accordion__item');
              if (otherItem) {
                otherItem.classList.remove('is-open');
              }
            });
          }

          setExpanded(willExpand);
        };

        trigger.addEventListener('click', togglePanel);

        if (trigger.tagName !== 'BUTTON') {
          trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              event.preventDefault();
              togglePanel();
            }
          });
        }
      });
    });
  }

  function initCounters() {
    const counters = Array.from(document.querySelectorAll('[data-counter]'));

    if (!counters.length) {
      return;
    }

    const animateCounter = (counter) => {
      if (counter.dataset.counterAnimated === 'true') {
        return;
      }

      counter.dataset.counterAnimated = 'true';

      const rawTarget = counter.dataset.counter || counter.textContent || '0';
      const target = Number(String(rawTarget).replace(/[^0-9.-]/g, ''));

      if (!Number.isFinite(target)) {
        return;
      }

      const prefix = counter.dataset.counterPrefix || '';
      const suffix =
        counter.dataset.counterSuffix || String(rawTarget).replace(/[0-9.,\s-]/g, '');
      const decimals = Number.isInteger(target)
        ? 0
        : Math.min(2, (String(target).split('.')[1] || '').length);
      const formatter = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      });

      const render = (value) => {
        counter.textContent = `${prefix}${formatter.format(value)}${suffix}`;
      };

      if (prefersReducedMotion.matches) {
        render(target);
        return;
      }

      const duration = Math.max(300, Number(counter.dataset.counterDuration) || 1400);
      const startTime = performance.now();

      const tick = (currentTime) => {
        const elapsed = Math.min((currentTime - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - elapsed, 3);
        const value = target * eased;
        render(decimals ? value : Math.round(value));

        if (elapsed < 1) {
          window.requestAnimationFrame(tick);
        } else {
          render(target);
        }
      };

      window.requestAnimationFrame(tick);
    };

    if (!('IntersectionObserver' in window) || prefersReducedMotion.matches) {
      counters.forEach(animateCounter);
      return;
    }

    const observer = new IntersectionObserver(
      (entries, counterObserver) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            counterObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.35 }
    );

    counters.forEach((counter) => observer.observe(counter));
  }

  function initRevealAnimations() {
    const items = Array.from(document.querySelectorAll('[data-reveal], .reveal'));

    if (!items.length) {
      return;
    }

    items.forEach((item) => {
      const delay = Number(item.dataset.revealDelay);
      if (Number.isFinite(delay) && delay > 0 && !prefersReducedMotion.matches) {
        item.style.transitionDelay = `${Math.min(delay, 800)}ms`;
      }
    });

    if (!('IntersectionObserver' in window) || prefersReducedMotion.matches) {
      items.forEach((item) => item.classList.add('is-visible'));
      return;
    }

    const observer = new IntersectionObserver(
      (entries, revealObserver) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
          }
        });
      },
      {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.08,
      }
    );

    items.forEach((item) => observer.observe(item));
  }

  function initBackToTop() {
    let button = document.querySelector('[data-back-to-top], .back-to-top');

    if (!button) {
      button = document.createElement('button');
      button.type = 'button';
      button.className = 'back-to-top';
      button.setAttribute('data-back-to-top', '');
      button.setAttribute('aria-label', 'Kembali ke bagian atas halaman');

      const icon = document.createElement('span');
      icon.setAttribute('aria-hidden', 'true');
      icon.textContent = '\u2191';
      button.appendChild(icon);
      document.body.appendChild(button);
    }

    button.hidden = false;

    let ticking = false;

    const updateVisibility = () => {
      const isVisible = window.scrollY > Math.max(420, window.innerHeight * 0.65);
      button.classList.toggle('is-visible', isVisible);
      ticking = false;
    };

    const requestUpdate = () => {
      if (!ticking) {
        window.requestAnimationFrame(updateVisibility);
        ticking = true;
      }
    };

    button.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
      });
    });

    updateVisibility();
    window.addEventListener('scroll', requestUpdate, { passive: true });
  }

  function initSamePageLinks() {
    document.addEventListener('click', (event) => {
      const link = event.target.closest('a[href^="#"]');

      if (!link || link.getAttribute('href') === '#') {
        return;
      }

      let target = null;

      try {
        target = document.querySelector(link.getAttribute('href'));
      } catch (error) {
        return;
      }

      if (!target) {
        return;
      }

      event.preventDefault();
      target.scrollIntoView({
        behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
        block: 'start',
      });

      if (!target.hasAttribute('tabindex')) {
        target.setAttribute('tabindex', '-1');
        target.addEventListener(
          'blur',
          () => {
            target.removeAttribute('tabindex');
          },
          { once: true }
        );
      }

      target.focus({ preventScroll: true });

      if (window.history && typeof window.history.pushState === 'function') {
        window.history.pushState(null, '', link.getAttribute('href'));
      }
    });
  }

  ready(() => {
    initNavigation();
    initHeaderSearch();
    initStickyHeader();
    initAnnouncements();
    initTabs();
    initAccordions();
    initCounters();
    initRevealAnimations();
    initBackToTop();
    initSamePageLinks();

    document.documentElement.classList.add('theme-ready');
  });
})();
