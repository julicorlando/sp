// form-validation.js

$(document).ready(() => {
    $('.form-cadastro').on('submit', (e) => {
        const nome = $('#id_nome').val();
        
        if (!nome) {
            e.preventDefault(); // Impede o envio do formulário
            alert('Por favor, preencha o campo Nome.'); // Mensagem de alerta
        }
    });
});
