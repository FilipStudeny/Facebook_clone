const changeFormHeight = () => {

    let textArea = document.getElementById("NewPostTextArea");
    const numOfRows = textArea.value.split('\n').length;        

    if(textArea.value == ""){
        textArea.style.height = "auto";
    }

    switch (true) {
        case (numOfRows >= 7):
            textArea.style.height = "200px";
            break;
        case (numOfRows >= 3):
            textArea.style.height = "150px";
            break;
        default:
            textArea.style.height = "auto";
            break;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let textArea = document.getElementById("NewPostTextArea");
    textArea.addEventListener('input', changeFormHeight);
});





