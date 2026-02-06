document.addEventListener("DOMContentLoaded", () => {

    // smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener("click", function(e){
            e.preventDefault();
            document.querySelector(this.getAttribute("href"))
                .scrollIntoView({ behavior: "smooth" });
        });
    });

    // reveal on scroll
    const reveals = document.querySelectorAll(".reveal");

    const revealOnScroll = () => {
        reveals.forEach(el => {
            const top = el.getBoundingClientRect().top;
            if(top < window.innerHeight - 80){
                el.classList.add("active");
            }
        });
    };

    window.addEventListener("scroll", revealOnScroll);
    revealOnScroll();
});
