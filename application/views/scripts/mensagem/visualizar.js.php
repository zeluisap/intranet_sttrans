var editor = false;

$(document).ready(function() {
    $("#janela_mensagem").css( { "width": "800px", "margin-left": "-400px" } );
});

function responder() {
    $("#assunto").val("Re: <?php echo $this->view->registro->assunto; ?>");
    if (!editor) {
        editor = criaEditor('mensagem');
    }
    editor.setData("");
    $("#formulario").attr("action", "<?php echo $this->view->url(array("action" => "responder")); ?>");
    $("#janela_mensagem").modal("show");
    $("#janela_mensagem").on("shown", function() {
        $("#assunto").focus();
    });
}