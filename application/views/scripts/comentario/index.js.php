$(document).ready(function() {
    $(".link_confirmar").click(function(event) {
        event.preventDefault();
        $("#formulario").attr("action", $(this).attr("href"));
        $("#janela_confirmacao #myModalLabel").text("Confirmação de Comentário");
        $("#janela_confirmacao .modal-body").text("Confirmação de Comentário?");
        $("#janela_confirmacao").modal("show");
    });
    $(".link_negar").click(function(event) {
        event.preventDefault();
        $("#formulario").attr("action", $(this).attr("href"));
        $("#janela_confirmacao #myModalLabel").text("Negar Comentário");
        $("#janela_confirmacao .modal-body").text("Negar de Comentário?");
        $("#janela_confirmacao").modal("show");
    });
    $("#janela_confirmacao").modal("hide");
    $("#janela_confirmacao").on("hide", function() {
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "comentario", "action" => "index")); ?>");
    });
    $("#bt_confirmar").click(function() {
        $("#formulario").submit();
    });
});