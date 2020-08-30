$(document).ready(function() {
    $("#bt_jan_procurar").click(function() {
        $("#msg_erro_cpf").hide();
        if (!validarCPF($("#jan_cpf").val())) {
            $("#msg_erro_cpf .mensagem_erro").text("CPF INVÁLIDO!");
            $("#msg_erro_cpf").show();
            return false;
        }
        $("#formulario").submit();
    });
    $("#bt_imprimir").click(
    	function() {
    		$("#janela_ponto").modal("hide");
    		$("#formulario").submit();
    		$("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "funcionario", "action" => "index")); ?>");
    	}
    );
    $("#janela_cpf, #janela_ponto").css( { "width": "500px", "margin-left": "-250px" } );
    $("#janela_cpf, #janela_ponto").modal("hide");
    $("#janela_cpf, #janela_ponto").on("hidden", function() {
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "index")); ?>");
    });
    $("#janela_cpf").on("show", function() {
        $("#msg_erro_cpf").hide();
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "addfuncionario")); ?>");
    });    
    $("#janela_ponto").on("show", function() {
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "funcionario", "action" => "imprimir")); ?>");
    });
    $("#janela_cpf").on("shown", function() {
        $("#jan_cpf").val("").focus().select();
    });
    $("#janela_cpf").keypress(function(event) {
        event.preventDefault();
        if (event.which == 13) {
            $("#formulario").submit();
        }
    });
    $(".link_ponto").click(function(event) {
        event.preventDefault();
        $("#ponto_id_funcionario").val($(this).attr("id"));
        $("#janela_ponto").modal("show");
    });
});

function add_usuario() {
    $("#janela_cpf").modal("show");
}

function alterar(id_funcionario) {
    $("#id_funcionario").val(id_funcionario);
    $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "funcionario", "action" => "addfuncionario")); ?>/id_funcionario/" + id_funcionario);
    $("#formulario").submit();
}

function importar() {
    if ($(".importar").css("display") == "none") {
        $(".importar").show();
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "funcionario", "action" => "importar")); ?>");
    } else {
        $(".importar").hide();
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "funcionario", "action" => "index")); ?>");    
    }
}

function folha_ponto() {
    $("#ponto_id_funcionario").val("");
    $("#janela_ponto").modal("show");    
}