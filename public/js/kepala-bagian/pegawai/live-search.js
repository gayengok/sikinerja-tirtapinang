document.addEventListener("DOMContentLoaded", () => {
    const searchForm = document.querySelector(".team-search");
    const searchInput = document.getElementById("team-search-input");
    const teamList = document.querySelector(".kepala-bagian-team-list");
    const pagination = document.querySelector(".team-pagination");

    if (!searchForm || !searchInput || !teamList) {
        return;
    }

    let searchTimer = null;
    let activeController = null;
    let requestId = 0;

    const setLoading = (loading) => {
        teamList.classList.toggle("is-searching", loading);
        searchInput.setAttribute("aria-busy", String(loading));
    };

    const buildUrl = () => {
        const url = new URL(searchForm.action, window.location.origin);
        const currentParams = new URLSearchParams(window.location.search);
        const formData = new FormData(searchForm);

        currentParams.delete("page");

        for (const [key, value] of formData.entries()) {
            if (String(value).trim() === "") {
                currentParams.delete(key);
            } else {
                currentParams.set(key, String(value));
            }
        }

        url.search = currentParams.toString();

        return url;
    };

    const loadResults = async (url, updateHistory = true) => {
        const currentRequest = ++requestId;

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();
        const controller = activeController;

        setLoading(true);

        try {
            const response = await fetch(url.toString(), {
                method: "GET",
                headers: {
                    Accept: "text/html",
                    "X-Requested-With": "XMLHttpRequest",
                },
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error("Gagal memuat data pegawai.");
            }

            const html = await response.text();

            if (currentRequest !== requestId) {
                return;
            }

            const documentResult = new DOMParser().parseFromString(
                html,
                "text/html",
            );

            const newTeamList = documentResult.querySelector(
                ".kepala-bagian-team-list",
            );

            const newPagination =
                documentResult.querySelector(".team-pagination");

            if (!newTeamList) {
                throw new Error("Daftar pegawai tidak ditemukan.");
            }

            teamList.innerHTML = newTeamList.innerHTML;

            if (pagination) {
                pagination.innerHTML = newPagination
                    ? newPagination.innerHTML
                    : "";
            }

            if (updateHistory) {
                window.history.replaceState({}, "", url.pathname + url.search);
            }
        } catch (error) {
            if (error.name === "AbortError") {
                return;
            }

            console.error("Live search pegawai:", error);
        } finally {
            if (currentRequest === requestId) {
                setLoading(false);
                activeController = null;
            }
        }
    };

    searchInput.addEventListener("input", () => {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {
            loadResults(buildUrl());
        }, 350);
    });

    searchForm.addEventListener("submit", (event) => {
        event.preventDefault();

        clearTimeout(searchTimer);
        loadResults(buildUrl());
    });

    document.addEventListener("click", (event) => {
        const paginationLink = event.target.closest(".team-pagination a[href]");

        if (!paginationLink) {
            return;
        }

        event.preventDefault();

        clearTimeout(searchTimer);

        const url = new URL(paginationLink.href, window.location.origin);

        loadResults(url);
    });

    window.addEventListener("popstate", () => {
        const params = new URLSearchParams(window.location.search);

        searchInput.value = params.get("search") || "";

        loadResults(new URL(window.location.href), false);
    });
});
