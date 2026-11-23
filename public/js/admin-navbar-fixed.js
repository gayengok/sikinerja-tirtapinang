(function () {
    function init() {
        const body = document.body;

        if (!body || !body.classList.contains("role-master")) return;

        const navbar = document.querySelector(
            "nav.navbar-sikinerja.sikinerja-capsule-navbar",
        );
        const shell = document.querySelector(".mobile-app-shell");

        if (!navbar || !shell) return;

        const desktop = window.matchMedia("(min-width: 768px)");
        const marker = document.createComment("navbar-original-position");

        if (navbar.parentElement === shell) {
            shell.insertBefore(marker, navbar);
        }

        function updateNavbar() {
            const isDesktop = desktop.matches;
            const collapsed = body.classList.contains("sidebar-collapsed");

            if (isDesktop) {
                if (navbar.parentElement !== body) {
                    body.insertBefore(navbar, shell);
                }

                navbar.classList.remove("navbar-scroll-hidden");

                const styles = {
                    position: "fixed",
                    top: "16px",
                    left: "16px",
                    right: collapsed ? "auto" : "16px",
                    bottom: "auto",
                    width: collapsed ? "72px" : "calc(100% - 32px)",
                    minWidth: collapsed ? "72px" : "0",
                    maxWidth: collapsed ? "72px" : "calc(100% - 32px)",
                    height: collapsed ? "72px" : "76px",
                    minHeight: collapsed ? "72px" : "76px",
                    margin: "0",
                    padding: collapsed ? "9px" : "10px 14px",
                    borderRadius: collapsed ? "50%" : "999px",
                    justifyContent: collapsed ? "center" : "space-between",
                    overflow: collapsed ? "hidden" : "visible",
                    transform: "none",
                    zIndex: "1050",
                };

                Object.entries(styles).forEach(function ([property, value]) {
                    navbar.style.setProperty(
                        property.replace(
                            /[A-Z]/g,
                            (match) => "-" + match.toLowerCase(),
                        ),
                        value,
                        "important",
                    );
                });
            } else {
                if (marker.parentNode === shell) {
                    shell.insertBefore(navbar, marker.nextSibling);
                } else if (navbar.parentElement !== shell) {
                    shell.insertBefore(navbar, shell.firstChild);
                }

                [
                    "position",
                    "top",
                    "left",
                    "right",
                    "bottom",
                    "width",
                    "min-width",
                    "max-width",
                    "height",
                    "min-height",
                    "margin",
                    "padding",
                    "border-radius",
                    "justify-content",
                    "overflow",
                    "transform",
                    "z-index",
                ].forEach(function (property) {
                    navbar.style.removeProperty(property);
                });
            }
        }

        updateNavbar();

        const observer = new MutationObserver(updateNavbar);

        observer.observe(body, {
            attributes: true,
            attributeFilter: ["class"],
        });

        if (desktop.addEventListener) {
            desktop.addEventListener("change", updateNavbar);
        } else {
            desktop.addListener(updateNavbar);
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
