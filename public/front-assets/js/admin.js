const aside = document.querySelector('[data-aside]');
const toggler = document.querySelector('[data-toggler]');


toggler.addEventListener('click', () => {
    aside.classList.toggle('hide');
})


let dropdowns = document.querySelectorAll('[data-dropdown]');

dropdowns.forEach(ele => {
    ele.onclick = () => {
        let drp = document.querySelector(`.${ele.dataset.dropdown}`);
        drp.classList.toggle('active');
    }
})


const links = document.querySelectorAll('[data-link]');
const subMenus = document.querySelectorAll('[data-menu]')
const arrows = document.querySelectorAll('[data-arrow]')

links.forEach(link => {
    link.addEventListener('click', () => {
        let ele = document.querySelector(`#${link.dataset.link}`);

        arrows.forEach(arrow => {
            arrow.classList.remove('active');
        });

        if (ele.classList.contains('active')) {
            subMenus.forEach(menu => {
                menu.classList.remove('active');
            });

        } else {
            subMenus.forEach(menu => {
                menu.classList.remove('active');
            });
            link.querySelector('.arrow').classList.add('active');
            ele.classList.add('active')
        }

    })
})

const message = document.querySelector('[data-message]');

if (message) {

    setTimeout(() => {
        message.classList.add('hide');
    }, 3500);
}
