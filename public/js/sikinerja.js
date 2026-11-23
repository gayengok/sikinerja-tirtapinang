document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const brandToggle = document.getElementById("sikinerjaBrandToggle");
    const toggleDesktop = document.getElementById("sidebarToggleDesktop");
    const toggleMobile = document.getElementById("sidebarToggleMobile");
    const closeMobile = document.getElementById("sidebarCloseBtn");
    const navbar = document.querySelector(".sikinerja-capsule-navbar");
    const mobileQuery = window.matchMedia("(max-width: 767.98px)");
    const STORAGE_PREFIX = "sikinerja-sidebar-state";
    const isMobile = () => mobileQuery.matches;

    function getStorageKey() {
        const role = body.dataset.role || "guest";
        return `${STORAGE_PREFIX}:${role}`;
    }

    function saveDesktopState() {
        if (isMobile()) return;

        try {
            const collapsed = body.classList.contains("sidebar-collapsed");

            localStorage.setItem(
                getStorageKey(),
                collapsed ? "collapsed" : "expanded",
            );
        } catch (error) {
            console.warn("Tidak dapat menyimpan status sidebar:", error);
        }
    }

    function restoreDesktopState() {
        if (isMobile()) return;

        try {
            const state = localStorage.getItem(getStorageKey());

            body.classList.toggle("sidebar-collapsed", state === "collapsed");

            body.classList.toggle(
                "sikinerja-nav-collapsed",
                state === "collapsed",
            );
        } catch (error) {
            console.warn("Tidak dapat membaca status sidebar:", error);
        }
    }

    function desktopExpanded() {
        return !body.classList.contains("sidebar-collapsed");
    }

    function mobileOpen() {
        return sidebar?.classList.contains("show") ?? false;
    }

    function syncNavbar() {
        const expanded = isMobile() ? mobileOpen() : desktopExpanded();

        body.classList.toggle(
            "sikinerja-nav-collapsed",
            !isMobile() && !expanded,
        );

        const label = expanded ? "Tutup sidebar" : "Buka sidebar";

        [brandToggle, toggleDesktop].forEach(function (button) {
            if (!button) return;

            button.setAttribute("aria-expanded", String(expanded));
            button.setAttribute("aria-label", label);
            button.setAttribute("title", label);
        });

        if (toggleMobile) {
            toggleMobile.setAttribute("aria-expanded", String(mobileOpen()));
        }
    }

    function setDesktopSidebar(expanded) {
        body.classList.toggle("sidebar-collapsed", !expanded);

        saveDesktopState();
        syncNavbar();
    }

    function setMobileSidebar(open) {
        if (!sidebar || !overlay) return;

        sidebar.classList.toggle("show", open);
        overlay.classList.toggle("show", open);

        if (open) {
            navbar?.classList.remove("navbar-scroll-hidden");
        }

        syncNavbar();
    }

    brandToggle?.addEventListener("click", function (event) {
        event.preventDefault();

        if (isMobile()) {
            setMobileSidebar(!mobileOpen());
        } else {
            setDesktopSidebar(!desktopExpanded());
        }
    });

    toggleDesktop?.addEventListener("click", function (event) {
        event.preventDefault();

        setDesktopSidebar(!desktopExpanded());
    });

    toggleMobile?.addEventListener("click", function (event) {
        event.preventDefault();

        setMobileSidebar(!mobileOpen());
    });

    overlay?.addEventListener("click", function () {
        setMobileSidebar(false);
    });

    closeMobile?.addEventListener("click", function () {
        setMobileSidebar(false);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") return;

        if (isMobile() && mobileOpen()) {
            setMobileSidebar(false);
            brandToggle?.focus();
        }
    });

    let navbarSpacer = null;
    let lastScrollY = window.scrollY;
    let scrollTicking = false;

    if (navbar) {
        navbarSpacer = document.createElement("div");
        navbarSpacer.className = "sikinerja-navbar-spacer";
        navbarSpacer.style.display = "none";
        navbarSpacer.style.flexShrink = "0";

        navbar.insertAdjacentElement("afterend", navbarSpacer);
    }

    function syncNavbarSpacing() {
        if (!navbar || !navbarSpacer) return;

        if (!isMobile()) {
            navbar.classList.remove("navbar-scroll-hidden");
            navbarSpacer.style.display = "none";
            return;
        }

        navbarSpacer.style.display = "block";

        navbarSpacer.style.height = `${
            navbar.offsetHeight +
            24 +
            parseFloat(
                getComputedStyle(document.documentElement).getPropertyValue(
                    "--sat",
                ) || 0,
            )
        }px`;
    }

    function updateNavbarScroll() {
        const currentScrollY = Math.max(0, window.scrollY);
        const delta = currentScrollY - lastScrollY;

        if (navbar && isMobile()) {
            const dropdownOpen = Boolean(
                navbar.querySelector(".dropdown-menu.show"),
            );

            if (currentScrollY <= 30 || dropdownOpen || mobileOpen()) {
                navbar.classList.remove("navbar-scroll-hidden");
            } else if (delta > 5 && currentScrollY > 100) {
                navbar.classList.add("navbar-scroll-hidden");
            } else if (delta < -5) {
                navbar.classList.remove("navbar-scroll-hidden");
            }
        }

        lastScrollY = currentScrollY;
        scrollTicking = false;
    }

    window.addEventListener(
        "scroll",
        function () {
            if (scrollTicking) return;

            scrollTicking = true;

            requestAnimationFrame(updateNavbarScroll);
        },
        { passive: true },
    );

    navbar?.addEventListener("show.bs.dropdown", function () {
        navbar.classList.remove("navbar-scroll-hidden");
    });

    function handleViewportChange() {
        if (isMobile()) {
            setMobileSidebar(false);
        } else {
            sidebar?.classList.remove("show");
            overlay?.classList.remove("show");

            restoreDesktopState();
            syncNavbar();
        }

        syncNavbarSpacing();
        lastScrollY = window.scrollY;
    }

    if (mobileQuery.addEventListener) {
        mobileQuery.addEventListener("change", handleViewportChange);
    } else {
        mobileQuery.addListener(handleViewportChange);
    }

    window.addEventListener("resize", syncNavbarSpacing);

    if (sidebar) {
        new MutationObserver(syncNavbar).observe(sidebar, {
            attributes: true,
            attributeFilter: ["class"],
        });
    }

    new MutationObserver(syncNavbar).observe(body, {
        attributes: true,
        attributeFilter: ["class"],
    });

    syncNavbar();
    syncNavbarSpacing();

    document.querySelectorAll(".alert").forEach(function (alertEl) {
        setTimeout(function () {
            if (!alertEl.isConnected) return;

            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(alertEl).close();
            } else {
                alertEl.classList.remove("show");
            }
        }, 6000);
    });

    document
        .querySelectorAll(".btn-confirm-delete, form.form-delete")
        .forEach(function (element) {
            element.addEventListener("submit", function (event) {
                const message =
                    element.getAttribute("data-confirm-message") ||
                    "Apakah Anda yakin ingin menghapus data ini?";

                if (!confirm(message)) {
                    event.preventDefault();
                }
            });

            if (element.tagName === "BUTTON" || element.tagName === "A") {
                element.addEventListener("click", function (event) {
                    const message =
                        element.getAttribute("data-confirm-message") ||
                        "Apakah Anda yakin ingin menghapus data ini?";

                    if (!confirm(message)) {
                        event.preventDefault();
                    }
                });
            }
        });
});
