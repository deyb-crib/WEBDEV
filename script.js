document.addEventListener("DOMContentLoaded", () => {

    const serviceButton = document.querySelector(".btn-primary");
    const aboutButton = document.querySelector(".btn-secondary");

    serviceButton.addEventListener("click", () => {
        document.querySelector("#services")?.scrollIntoView({
            behavior: "smooth"
        });
    });

    aboutButton.addEventListener("click", () => {
        document.querySelector("#about")?.scrollIntoView({
            behavior: "smooth"
        });
    });

});