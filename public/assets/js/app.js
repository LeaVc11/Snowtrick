//Animation de L'écriture

const txtAnim = document.querySelector('.text-animation');

let typewriter = new Typewriter(txtAnim,{
    loop: false,
    deleteSpeed:20
})
typewriter
    .pauseFor(1800)
    .changeDelay(50)
    .typeString('<span style="color:#487eb0;font-weight:bold"> La communauté pour découvrir</span><br><br> ')

    .pauseFor(1000)
    .typeString('<span style="color: #EA39ff; font-weight:bold "> le snowboard et ses figures</span> ')
    .pauseFor(1000)


    .start()


