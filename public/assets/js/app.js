//Animation de L'écriture

const txtAnim = document.querySelector('.text-animation');

let typewriter = new Typewriter(txtAnim,{
    loop: false,
    deleteSpeed:20
})
typewriter
    .pauseFor(1800)
    .changeDelay(50)
    .typeString('<strong style="font-size: 34px "> SnowTrick</strong> <br>')
    .pauseFor(300)
    .deleteChars(14)
    .typeString('<span style="color:#f1c40f"> La communauté pour découvrir le snowboard et ses figures</span> ')
    .pauseFor(1000)

    .start()


