const button = document.getElementById('theme_button');
const icon = document.getElementById("theme_icon");
const html_el = document.querySelector("html");


document.addEventListener("DOMContentLoaded", (event) => {
    const current_theme = localStorage.getItem("theme");
    if(current_theme){
        if(current_theme == "dark")
        {
        icon.classList.remove("bi-sun-fill");
        icon.classList.add("bi-moon-fill");
        } else {
        icon.classList.remove("bi-moon-fill");
        icon.classList.add("bi-sun-fill");
        }
        html_el.setAttribute("data-bs-theme",current_theme);
    }
});

button.addEventListener('click', function() {

    
    let set_theme = html_el.getAttribute("data-bs-theme") == "dark" ? "light" : "dark";

    if(set_theme == "dark")
    {
      icon.classList.remove("bi-sun-fill");
      icon.classList.add("bi-moon-fill");
    } else {
      icon.classList.remove("bi-moon-fill");
      icon.classList.add("bi-sun-fill");
    }

    html_el.setAttribute("data-bs-theme",set_theme);
    localStorage.setItem("theme",set_theme);

    // Save in user preferences for next session
    fetch("/api/preferences", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({theme:set_theme})
    });
});