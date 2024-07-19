// preloader  

const preloader = document.querySelector('[data-preloader]')

window.addEventListener('load', () => {
    preloader.classList.add('active');
})

/* cursor */

const cursorDot = document.querySelector('[data-cursor-dot]');
const cursorOutline = document.querySelector('[data-cursor-outline]');
const anchors = document.querySelectorAll('a');

window.addEventListener('mousemove', (e) => {
    const posX = e.clientX;
    const posY = e.clientY;

    cursorOutline.animate({
        left: `${posX}px`,
        top: `${posY}px`
    }, { duration: 500, fill: "forwards" })
})

document.body.addEventListener('mouseleave', () => {
    actionOnCursor('add');

})

document.body.addEventListener('mouseenter', () => {
    actionOnCursor('remove');

})

const actionOnCursor = (action) => {
    cursorOutline.classList[`${action}`]('hide');
}


anchors.forEach(anch => {
    anch.onmouseover = () => cursorOutline.classList.add('active');
    anch.onmouseleave = () => cursorOutline.classList.remove('active');
})

/* nav toggle */

const navBar = document.querySelector('[data-nav-bar]');
const togglers = document.querySelectorAll('[data-toggler]');
const overlay = document.querySelector('[data-overlay]');
const btnToggler = document.querySelector('[data-btn-toggler]');

const toggleNavBar = () => {
    navBar.classList.toggle('active');
    overlay.classList.toggle('active');
    document.body.classList.toggle('nav-active');
    if (navBar.classList.contains('active')) {
        btnToggler.innerHTML = '<i class="fa-solid fa-door-open"></i>';
    } else {
        btnToggler.innerHTML = '<i class="fa-solid fa-door-closed"></i>';
    }
}

togglers.forEach(toggler => {
    toggler.addEventListener('click', () => {
        toggleNavBar();
    })
})

/* header active */

const header = document.querySelector('[data-header]');

window.onscroll = () => {
    header.classList[scrollY > 100 ? 'add' : 'remove']('active');
}


/* btn scroll */

const counter = document.querySelector('[data-counter]');

window.addEventListener('scroll', () => {
    if (scrollY > 350 && scrollY < document.body.scrollHeight - 800) {
        counter.classList.add('active');
        setScrollValue();
    }
    else {
        counter.classList.remove('active');
    }
})

function setScrollValue() {
    let totalHeight = document.body.scrollHeight

    let scrolledHeight = (scrollY * 100) / (totalHeight - window.innerHeight);
    counter.innerText = `${Math.ceil(scrolledHeight)}%`;

}


/**
 * SCROLL REVEAL
 */

const revealElements = document.querySelectorAll("[data-reveal]");
const revealDelayElements = document.querySelectorAll("[data-reveal-delay]");

const reveal = function () {
    for (let i = 0, len = revealElements.length; i < len; i++) {
        if (revealElements[i].getBoundingClientRect().top < window.innerHeight / 1.2) {
            revealElements[i].classList.add("revealed");
        }
    }
}

for (let i = 0, len = revealDelayElements.length; i < len; i++) {
    revealDelayElements[i].style.transitionDelay = revealDelayElements[i].dataset.revealDelay;
}

window.addEventListener("scroll", reveal);
window.addEventListener("load", reveal);

