window.addEventListener("scroll", function(){

    const header = document.querySelector("header");

    if(window.scrollY > 50){

        header.style.background = "rgba(8,15,28,.9)";

    }else{

        header.style.background = "rgba(8,15,28,.65)";

    }

});
const reveals = document.querySelectorAll(".reveal");

function revealSections() {

    const windowHeight = window.innerHeight;
    const revealPoint = 120;

    reveals.forEach(section => {

        const revealTop = section.getBoundingClientRect().top;

        if (revealTop < windowHeight - revealPoint) {

            section.classList.add("active");

        } else {

            section.classList.remove("active");

        }

    });

}

window.addEventListener("scroll", revealSections);
window.addEventListener("load", revealSections);