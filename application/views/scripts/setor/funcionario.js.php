$(document).ready(function() {
    $("#bt_jan_procurar").click(function() {
        $("#msg_erro_cpf").hide();
        if (!validarCPF($("#jan_cpf").val())) {
            $("#msg_erro_cpf .mensagem_erro").text("CPF INVÁLIDO!");
            $("#msg_erro_cpf").show();
            return false;
        }
        return true;
    });
    $("#janela_cpf").css( { "width": "500px", "margin-left": "-250px" } ).modal("hide");
    $("#janela_cpf").on("show", function() {
        $("#msg_erro_cpf").hide();
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "setor", "action" => "addfuncionario")); ?>");
    });
    $("#janela_cpf").on("hide", function() {
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "setor", "action" => "funcionario")); ?>");
    });
    $("#janela_cpf").on("shown", function() {
        $("#jan_cpf").val("").focus().select();
    });
    $("#janela_cpf").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $("#bt_jan_procurar").click();
        }
    });
    $(".link_alterar").click(function(event) {
        event.preventDefault();
        $("#jan_cpf").val($(this).attr("id"));
        $("#formulario").attr("action", $(this).attr("href"))
        $("#formulario").submit();        
    });
});

function add_usuario() {
    $("#janela_cpf").modal("show");
}