document.addEventListener("DOMContentLoaded", () => {
  const header = document.getElementById("siteHeader");
  const toggle = document.querySelector(".menu-toggle");
  const nav = document.querySelector(".main-nav");

  toggle?.addEventListener("click", () => {
    const open = nav.classList.toggle("open");
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
  });

  document.querySelectorAll(".main-nav a").forEach(link => {
    link.addEventListener("click", () => {
      nav.classList.remove("open");
      toggle?.setAttribute("aria-expanded", "false");
    });
  });

  window.addEventListener("scroll", () => {
    header.classList.toggle("scrolled", window.scrollY > 20);
  });

  const year = document.getElementById("year");
  if (year) year.textContent = new Date().getFullYear();

  // Subtle reveal animation for cards when they enter the viewport.
  const cards = document.querySelectorAll(".burger-card, .gallery-card");
  cards.forEach(card => card.style.opacity = "0");

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.animate(
        [
          { opacity: 0, transform: "translateY(14px)" },
          { opacity: 1, transform: "translateY(0)" }
        ],
        { duration: 450, easing: "ease-out", fill: "forwards" }
      );
      observer.unobserve(entry.target);
    });
  }, { threshold: 0.08 });

  cards.forEach(card => observer.observe(card));
});

