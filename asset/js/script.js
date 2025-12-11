document.addEventListener("DOMContentLoaded", function () {
  const toggleButton = document.getElementById("menu-toggle");
  const sidebar = document.getElementById("sidebar");

  if (!toggleButton || !sidebar) {
    console.warn("Menu toggle button or sidebar not found");
    return;
  }

  // Toggle sidebar - berfungsi di mobile dan desktop
  toggleButton.addEventListener("click", function (e) {
    e.preventDefault();
    // Gunakan inline style untuk menampilkan/menyembunyikan sidebar
    // Pada mobile (<=768px) slide sidebar masuk/keluar
    if (window.innerWidth <= 768) {
      // Cek apakah sudah dibuka (inline style) - fallback ke computed style
      const inline = sidebar.style.transform || "";
      const computed = window.getComputedStyle(sidebar).transform;
      const isOpenInline = inline.indexOf("translateX(0") !== -1;
      const isOpenComputed =
        computed &&
        computed !== "none" &&
        computed !== "matrix(1, 0, 0, 1, 0, 0)"
          ? computed.indexOf("translate") === 0 ||
            computed.indexOf("matrix") === 0
          : false;

      // Jika inline style menyatakan open, atau computed menunjukkan posisi 0, treat as open
      const isOpen =
        isOpenInline ||
        (!inline &&
        computed &&
        computed !== "none" &&
        computed !== "matrix(1, 0, 0, 1, 0, 0)"
          ? computed.indexOf("translateX(0") !== -1
          : false);

      if (isOpen) {
        sidebar.style.transform = "translateX(-100%)";
      } else {
        sidebar.style.transform = "translateX(0)";
      }
    } else {
      // Pada desktop pastikan sidebar terlihat (tidak digeser)
      sidebar.style.transform = "translateX(0)";
    }
  });

  // Tutup sidebar saat link di-klik (mobile)
  const navLinks = sidebar.querySelectorAll("a.nav-link");
  navLinks.forEach(function (link) {
    link.addEventListener("click", function () {
      if (window.innerWidth <= 768) {
        // sembunyikan sidebar pada mobile dengan inline style
        sidebar.style.transform = "translateX(-100%)";
      }
    });
  });
});
