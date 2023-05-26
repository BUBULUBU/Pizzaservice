// Global JS File

function collapseNav() {
    "use strict";

    let navbar = document.getElementById("navigation");

    if (navbar.className === "topnav") {
        navbar.className += " responsive";
    } else {
        navbar.className = "topnav";
    }
}

function updateNavBar() {
    "use strict";

    const currentLocation = location.pathname;
    const page = currentLocation.split("/").pop();

    if (currentLocation === "") {
        return;
    }

    const menuItems = document.querySelectorAll(".topnav a");

    Array.from(menuItems).forEach(function (element) {
        if (element.getAttribute("href").indexOf(page) !== -1) {
            element.className += "active";
        }
    });
}