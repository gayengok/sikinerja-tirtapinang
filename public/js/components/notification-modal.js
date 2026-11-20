document.addEventListener("DOMContentLoaded", function () {
    const trigger = document.getElementById("navNotifBtn");
    const modal = document.getElementById("sikinerjaNotificationModal");
    const backdrop = document.getElementById("sikinerjaNotificationBackdrop");
    const closeButton = document.getElementById("sikinerjaNotificationClose");
    const panel = modal?.querySelector(".sikinerja-notification-panel");

    if (!trigger || !modal || !panel) {
        return;
    }

    let lastFocusedElement = null;

    function openNotification() {
        if (!modal.hidden) {
            return;
        }

        lastFocusedElement = document.activeElement;

        document
            .querySelectorAll('[data-bs-toggle="dropdown"]')
            .forEach(function (toggle) {
                if (window.bootstrap?.Dropdown) {
                    bootstrap.Dropdown.getInstance(toggle)?.hide();
                }
            });

        modal.hidden = false;
        modal.setAttribute("aria-hidden", "false");

        document.body.classList.add("sikinerja-notification-open");

        requestAnimationFrame(function () {
            modal.classList.add("is-visible");
            closeButton?.focus();
        });
    }

    function closeNotification() {
        if (modal.hidden) {
            return;
        }

        if (modal.contains(document.activeElement)) {
            trigger.focus();
        }

        modal.classList.remove("is-visible");
        modal.setAttribute("aria-hidden", "true");
        modal.hidden = true;

        document.body.classList.remove("sikinerja-notification-open");

        if (
            lastFocusedElement instanceof HTMLElement &&
            lastFocusedElement !== trigger
        ) {
            lastFocusedElement.focus();
        }
    }

    trigger.addEventListener("click", openNotification);

    closeButton?.addEventListener("click", closeNotification);

    backdrop?.addEventListener("click", closeNotification);

    modal.addEventListener("keydown", function (event) {
        if (event.key !== "Tab") {
            return;
        }

        const focusable = Array.from(
            panel.querySelectorAll(
                'button:not([disabled]), a[href], input:not([disabled]), [tabindex]:not([tabindex="-1"])',
            ),
        ).filter(function (element) {
            return element.getClientRects().length > 0;
        });

        if (!focusable.length) {
            event.preventDefault();
            panel.focus();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) {
            event.preventDefault();
            closeNotification();
        }
    });
});
