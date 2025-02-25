document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.querySelector(".navbar")
    const hamburgerMenu = document.querySelector(".hamburger-menu")
    const navMenu = document.querySelector(".nav-menu")
    const ctaButton = document.getElementById("cta-button")
  
    // Hamburger menu functionality
    hamburgerMenu.addEventListener("click", () => {
      hamburgerMenu.classList.toggle("active")
      navMenu.classList.toggle("active")
  
      // Prevent scrolling when menu is open
      if (navMenu.classList.contains("active")) {
        document.body.style.overflow = "hidden"
      } else {
        document.body.style.overflow = "auto"
      }
    })
  
    // Close menu when clicking outside
    document.addEventListener("click", (e) => {
      if (!navMenu.contains(e.target) && !hamburgerMenu.contains(e.target)) {
        hamburgerMenu.classList.remove("active")
        navMenu.classList.remove("active")
        document.body.style.overflow = "auto"
      }
    })
  
    // Close menu when a nav item is clicked
    document.querySelectorAll(".nav-menu li a").forEach((navItem) => {
      navItem.addEventListener("click", () => {
        hamburgerMenu.classList.remove("active")
        navMenu.classList.remove("active")
        document.body.style.overflow = "auto"
      })
    })
  
    window.addEventListener("scroll", () => {
      if (window.scrollY > 50) {
        navbar.classList.add("scrolled")
      } else {
        navbar.classList.remove("scrolled")
      }
    })
  
    /* ctaButton.addEventListener("click", () => {
      alert("Thank you for your interest! Join our community by filling out the form (form implementation coming soon).")
    }) */
  
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener("click", function (e) {
        e.preventDefault()
  
        document.querySelector(this.getAttribute("href")).scrollIntoView({
          behavior: "smooth",
        })
      })
    })

        // Dynamic event list

   /* events.forEach((event) => {
      const li = document.createElement("li")
      li.textContent = `${event.name} - ${event.date}`
      eventList.appendChild(li)
    }) */
  });

 /*       // Menampilkan loading spinner saat halaman dimuat
        window.addEventListener('load', function() {
            const loadingElement = document.getElementById('loading');

            // Menambahkan kelas hidden untuk memulai animasi memudar
            loadingElement.classList.add('hidden');

            // Menunggu animasi selesai sebelum menyembunyikan elemen
            setTimeout(function() {
                loadingElement.style.display = 'none'; // Menyembunyikan elemen loading
                document.getElementById('main').style.display = 'block'; // Menampilkan konten utama
                document.body.style.overflow = 'auto'; // Mengizinkan scroll
            }, 500); // Waktu yang sama dengan durasi transisi CSS
        }); 
