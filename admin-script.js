document.addEventListener("DOMContentLoaded", () => {
    const adminContent = document.getElementById("admin-content");
    const navLinks = document.querySelectorAll(".admin-nav a[data-page]");

    function loadContent(page) {
        fetch(`admin-${page}.php`)
            .then((response) => response.text())
            .then((html) => {
                adminContent.innerHTML = html;
                setupFormListeners();
            })
            .catch((error) => {
                console.error("Error loading content:", error);
                adminContent.innerHTML = "<p>Error loading content. Please try again.</p>";
            });
    }

    function setupFormListeners() {
        const forms = adminContent.querySelectorAll("form");
        forms.forEach((form) => {
            form.addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                    method: "POST",
                    body: formData,
                })
                    .then((response) => response.text())
                    .then((html) => {
                        adminContent.innerHTML = html;
                        setupFormListeners(); // Setup ulang listener setelah pembaruan
                    })
                    .catch((error) => {
                        console.error("Error submitting form:", error);
                    });
            });
        });
    }

    navLinks.forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const page = this.getAttribute("data-page");
            loadContent(page);
        });
    });

    // Load default content (e.g., 'content' page)
    loadContent("content");
});
