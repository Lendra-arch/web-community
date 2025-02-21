document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById("news-popup")
    const popupTitle = document.getElementById("popup-title")
    const popupContent = document.getElementById("popup-content")
    const closePopup = document.querySelector(".close-popup")
    const readMoreLinks = document.querySelectorAll(".read-more")
  
    readMoreLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault()
        const newsId = this.getAttribute("data-id")
        fetchNewsDetails(newsId)
      })
    })
  
    closePopup.addEventListener("click", () => {
      popup.style.display = "none"
    })
  
    window.addEventListener("click", (e) => {
      if (e.target === popup) {
        popup.style.display = "none"
      }
    })
  
    function fetchNewsDetails(id) {
      fetch(`get-news-details.php?id=${id}`)
        .then((response) => response.json())
        .then((data) => {
          popupTitle.textContent = data.title
          popupContent.textContent = data.content
          popup.style.display = "flex"
        })
        .catch((error) => console.error("Error:", error))
    }
  })
  
  