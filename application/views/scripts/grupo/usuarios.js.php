$(document).ready(function() {
    $("#form_usuario").submit(function(event) {
        event.preventDefault();
        atualizaUsuario();
    });
    $("#bt_limpar").click(function() {
        adicionar();
    });
    $("#janela_usuario").css({ "width": "900px", "margin-left": "-450px" });
});

function adicionar() {
    $("#filtro_nome, #jan_pagina").val("");
    atualizaUsuario();
}

function atualizaUsuario() {
    $("#table_usuario tbody tr, #janela_usuario .paginacao").remove();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/usuario/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_nome" : $("#filtro_nome").val(), 
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#table_usuario tbody"));
                    $('<td>' + item.nome + '</td>').appendTo(tr);
                    $('<td>' + item.situacao + '</td>').appendTo(tr);
                    $('<td><a href="<?php echo $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "addusuario", "id" => $this->view->grupo->getId())); ?>/id_usuario/' + item.id + '" class="btn btn-primary">Selecionar</a></td>').appendTo(tr);
                }
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="paginacao">' + html + "</div>").appendTo($("#janela_usuario .modal-body"));
            } else {
                $("<tr ><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#table_usuario tbody"));
            }
            $("#janela_usuario").modal("show");
            $("#filtro_nome").focus();
        }
    });
}

function setPage(pagina) {
    $("#jan_pagina").val(pagina);
    atualizaUsuario();
}