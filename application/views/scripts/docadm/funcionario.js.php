$(document).ready(function() {    
    $("#formulario").submit(
        function() {
            if ($("#operacao").val() == "usuario") {
                procurar();
                return false;
            }
            return true;
        }
    );
    $("#jan_filtro_limpar").click(
        function() {
            limparFiltro();
        }
    );
    $("#janela_cpf").css( { "width": "1000px", "margin-left": "-500px" } ).modal("hide");
    $("#janela_cpf").on("show", function() {
        $("#operacao").val("usuario");
        $("#msg_erro_cpf").hide();
        $("#jan_cpf").focus();
        limparFiltro();
    });
    $("#janela_cpf").on("hide", function() {
        $("#operacao").val("");
    });
    $("#janela_cpf").on("shown", function() {
        $("#jan_cpf").focus();
    });
    $("#janela_cpf").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $("#formulario").submit();
        }
    });
});

function add_usuario() {
    $("#janela_cpf").modal("show");
}

function procurar() {
    $(".linha_resultado, .paginacao_funcionario").remove();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/funcionario/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_cpf": $("#jan_cpf").val(),
                   "filtro_nome": $("#filtro_nome").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 15 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                infos = [];
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    infos[i] = { "id": item.id, "nome":  item.nome, "matricula": item.matricula, "cargo": item.cargo, "setor": item.setor }; 
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#tabela_lista"));
                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/docadm/addfuncionario/id/<?php echo $this->_getParam("id"); ?>/id_funcionario/' + item.id + '">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/docadm/addfuncionario/id/<?php echo $this->_getParam("id"); ?>/id_funcionario/' + item.id + '">' + item.nome + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/docadm/addfuncionario/id/<?php echo $this->_getParam("id"); ?>/id_funcionario/' + item.id + '">' + item.matricula + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/docadm/addfuncionario/id/<?php echo $this->_getParam("id"); ?>/id_funcionario/' + item.id + '">' + item.cargo + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/docadm/addfuncionario/id/<?php echo $this->_getParam("id"); ?>/id_funcionario/' + item.id + '">' + item.setor + '</a></td>').appendTo(tr);
                    tr.appendTo($(".corpo_lista"));
                }
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="paginacao_funcionario">' + html + '</div>').appendTo($("#janela_cpf .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='5' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#tabela_lista"));
            }
        }
    });    
}

function setPage(page) {
    $("#jan_pagina").val(page);
    procurar();
}

function limparFiltro() {
    $("#jan_cpf, #filtro_nome, #jan_pagina").val("");
    procurar();
}