document.addEventListener("DOMContentLoaded", function () {
    const hamburger = document.getElementById("hamburger");
    const sidebar = document.getElementById("sidebar");
    const menuItems = document.querySelectorAll("#menu li");

    hamburger.addEventListener("click", function () {
        sidebar.classList.toggle("hide");
    });

    menuItems.forEach(item => {
        item.addEventListener("click", () => {
            menuItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
        });
    });
});
