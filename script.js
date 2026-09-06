document.addEventListener("DOMContentLoaded", () => {

    const navbar = document.querySelector(".navbar");
    const serviceButton = document.querySelector(".services-btn");
    const aboutButton = document.querySelector(".about-btn");
    const searchForm = document.querySelector("#center-search-form");
    const searchInput = document.querySelector("#center-search-input");
    const searchStatus = document.querySelector("#search-status");
    const centerCards = [...document.querySelectorAll(".center-card")];
    const locationButton = document.querySelector("#use-location-button");
    const filterChips = document.querySelectorAll(".filter-chip");
    const allHomeSidebarLinks = [...document.querySelectorAll(".home-sidebar-link")];
    const homeSidebarLinks = allHomeSidebarLinks.filter((link) => link.dataset.section);

    const setActiveSidebarLink = (targetId) => {
        allHomeSidebarLinks.forEach((link) => {
            const isActive = link.dataset.section === targetId
                || (!targetId && link.getAttribute("href") === "dashboard/dashboard.php");
            link.classList.toggle("is-active", isActive);
            if (isActive) {
                link.setAttribute("aria-current", "location");
            } else {
                link.removeAttribute("aria-current");
            }
        });
    };

    allHomeSidebarLinks.forEach((link) => {
        link.addEventListener("click", () => {
            allHomeSidebarLinks.forEach((item) => item.classList.remove("is-active"));
            link.classList.add("is-active");
        });
    });

    homeSidebarLinks.forEach((link) => {
        link.addEventListener("click", (event) => {
            const target = document.querySelector(`#${link.dataset.section}`);
            if (!target) return;

            event.preventDefault();
            window.history.replaceState(null, "", `#${target.id}`);
            setActiveSidebarLink(target.id);
            target.classList.add("is-highlighted");
            target.scrollIntoView({ behavior: "smooth", block: "start" });

            window.setTimeout(() => target.classList.remove("is-highlighted"), 1400);
        });
    });

    const initialSection = window.location.hash.slice(1);
    if (homeSidebarLinks.some((link) => link.dataset.section === initialSection)) {
        setActiveSidebarLink(initialSection);
    }

    window.addEventListener("scroll", () => {
        navbar?.classList.toggle("is-sticky", window.scrollY > 80);
    }, { passive: true });

    serviceButton?.addEventListener("click", (event) => {
        event.preventDefault();
        document.querySelector("#services")?.scrollIntoView({
            behavior: "smooth"
        });
    });

    aboutButton?.addEventListener("click", (event) => {
        event.preventDefault();
        document.querySelector("#about")?.scrollIntoView({
            behavior: "smooth"
        });
    });

    searchForm?.addEventListener("submit", (event) => {
        event.preventDefault();
        const query = searchInput?.value.trim().toLowerCase() ?? "";
        let matchCount = 0;

        centerCards.forEach((card) => {
            const cardText = card.textContent?.toLowerCase() ?? "";
            const isMatch = !query || cardText.includes(query);
            card.hidden = !isMatch;
            if (isMatch) matchCount += 1;
        });

        if (searchStatus) {
            searchStatus.textContent = query
                ? `${matchCount} center${matchCount === 1 ? "" : "s"} found for "${query}".`
                : "Showing all available centers.";
        }

        document.querySelector("#centers")?.scrollIntoView({ behavior: "smooth" });
    });

    locationButton?.addEventListener("click", () => {
        if (!navigator.geolocation) {
            if (searchStatus) searchStatus.textContent = "Location is not available in this browser.";
            return;
        }

        locationButton.textContent = "Locating...";
        navigator.geolocation.getCurrentPosition(
            () => {
                locationButton.textContent = "Location selected";
                if (searchStatus) searchStatus.textContent = "Showing centers near your current location.";
            },
            () => {
                locationButton.textContent = "Use my location";
                if (searchStatus) searchStatus.textContent = "We could not access your location. Search by city instead.";
            }
        );
    });

    filterChips.forEach((chip) => {
        chip.addEventListener("click", () => {
            chip.classList.toggle("is-active");
        });
    });

});