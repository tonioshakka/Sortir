
export function createBurger() {
    /***********
     JS MENU BURGER
     *************/
    const menuBtn = document.querySelector('#menu-btn');
    const menu = document.querySelector('.nav');
    const menuItem = document.querySelectorAll('.nav-link');

    menuBtn.addEventListener('click', function() {

        menuBtn.classList.toggle('active');
        menu.classList.toggle('active');
    })

    menuItem.forEach(function(menuItem){
        menuItem.addEventListener('click', function(){
            menuBtn.classList.remove('active');
            menu.classList.remove('active');
        })
    })

}