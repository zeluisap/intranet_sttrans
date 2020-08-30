$(document).ready(function() {
    $(".link_ativar, .link_desativar").click(function(event) {
        event.preventDefault();
        $("#formulario").attr("action", $(this).attr("href"));
        $("#janela_confirma #titulo").text("Atenção - Confirmação");
    });
    $(".link_desativar").click(function() {
        $("#janela_confirma #mensagem").text("Confirmar Desativação do Bolsista?");
        $("#janela_confirma #btn_confirmar").removeClass("btn-primary").addClass("btn-danger");
        $("#janela_confirma").modal("show");
    });
    $(".link_ativar").click(function(event) {
        $("#janela_confirma #mensagem").text("Confirmar Ativação do Bolsista?");
        $("#janela_confirma #btn_confirmar").removeClass("btn-danger").addClass("btn-primary");
        $("#janela_confirma").modal("show");
    });
    $("#janela_confirma #btn_confirmar").click(function(event) {
        event.preventDefault();
        $("#formulario").submit();
    });
    $("#janela_confirma").modal("hide");
    $("#janela_confirma").on("hide", function() {
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "bolsista")); ?>");
    });
});