var ajax_obj = false;

$(document).ready(function() {
    $("#janela_bolsista, #janela_problema, #janela_inapto").modal("hide").css({"width": "900px", "margin-left": "-450px"});
    $("#janela_bolsista").on("show", function() {
        listar_lote_item();
    });
    $("#janela_bolsista").on("shown", function() {
        $("#janela_bolsista_cpf").focus();
    });
    $("#janela_bolsista").on("keypress", function(event) {
        if (event.which == 13) {
            event.preventDefault();
            listar_lote_item();
        }
    });
    $("#btn_limpar_filtro").click(function() {
        $("#janela_bolsista").find(".filtro").val("");
        listar_lote_item();
    });
    $("#btn_procurar").click(function() {
        listar_lote_item();
    });
    $("#janela_problema").on("show", function() {
        $("#janela_problema_descricao").val("");
        $("#formulario").attr("action", "<?php echo Escola_Util::url(array("controller" => $this->getRequest()->getControllerName(), "action" => "addproblema")); ?>");
    });
    $("#janela_problema").on("shown", function() {
        $("#janela_problema_descricao").focus();
    });
    $("#janela_problema, #janela_inapto").on("hide", function() {
        $("#formulario").attr("action", "<?php echo Escola_Util::url(array("controller" => $this->getRequest()->getControllerName(), "action" => "index")); ?>");
    });
    $("#janela_inapto").on("show", function() {
        $("#janela_inapto_descricao").val("");
        $("#formulario").attr("action", "<?php echo Escola_Util::url(array("controller" => $this->getRequest()->getControllerName(), "action" => "addinapto")); ?>");
    });
    $("#janela_inapto").on("shown", function() {
        $("#janela_inapto_descricao").focus();
    });
    $(".link_registrar_problema").click(function(event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var obj = pagamentos[id];
        $("#janela_problema_id").val(obj.id_vinculo_lote_item);
        $("#janela_problema #show_pagamento").text(obj.referencia);
        $("#janela_problema #show_pagamento_valor").text(obj.valor);
        $("#janela_problema").modal("show");
    });
    $(".link_inapto").click(function(event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var obj = pagamentos[id];
        $("#janela_inapto_id").val(obj.id_vinculo_lote_item);
        $("#janela_inapto #show_pagamento").text(obj.referencia);
        $("#janela_inapto #show_pagamento_valor").text(obj.valor);
        $("#janela_inapto").modal("show");
    });
});

function adicionar_lote_item() {
    $("#janela_bolsista").modal("show");
}

function listar_lote_item() {
    var bolsistas = [];
    $(".corpo_bolsista tr, .bolsista_paginacao").remove();
    if (ajax_obj) {
        ajax_obj.abort();
    }
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/vinculo/listarbolsista/format/json/",
        "type" : "POST",
        "data" : { "cpf": $("#janela_bolsista_cpf").val(),
                   "nome": $("#janela_bolsista_nome").val(),
                   "tipo": "<?php echo $this->getRequest()->getParam("tipo"); ?>",
                   "id_bolsa_tipo": "<?php echo $this->getRequest()->getParam("id_bolsa_tipo"); ?>",
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    bolsistas[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    tr.appendTo($(".corpo_bolsista"));
                    $('<td><a href="<?php echo $this->view->url(array("controller" => "lote", "action" => "addloteitembolsista")); ?>/id_bolsista/' + item.id + '">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo $this->view->url(array("controller" => "lote", "action" => "addloteitembolsista")); ?>/id_bolsista/' + item.id + '">' + item.bolsa_tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo $this->view->url(array("controller" => "lote", "action" => "addloteitembolsista")); ?>/id_bolsista/' + item.id + '">' + item.cpf + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo $this->view->url(array("controller" => "lote", "action" => "addloteitembolsista")); ?>/id_bolsista/' + item.id + '">' + item.nome + '</a></td>').appendTo(tr);
                    $('<td><a href="<?php echo $this->view->url(array("controller" => "lote", "action" => "addloteitembolsista")); ?>/id_bolsista/' + item.id + '">' + item.valor + '</a></td>').appendTo(tr);
                }
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="bolsista_paginacao">' + html + '</div>').appendTo($("#janela_bolsista .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='4' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($(".corpo_bolsista"));
            }
        }
    });    
}