$(document).ready(
    function() {
        $("#jan_filtro_limpar").click(
            function() {
                $("#jan_filtro_id_info_tipo, #filtro_titulo").val("");
                $("#formulario").submit();
            }
        );
        $("#formulario").submit(
            function() {
                if ($("#operacao").val() == "janela") {
                    procurar();
                    return false;
                }
                return true;
            }
        );
        $("#janela_add").css( { "width": "800px", "margin-left": "-400px" } ).modal("hide");
        $("#janela_add").on("show", function() {
            $("#filtro_titulo, #jan_filtro_id_info_tipo").val("");
            $("#operacao").val("janela");
            procurar();
        });
        $("#janela_add").on("shown", function() {
            $("#jan_filtro_id_info_tipo").focus();
        });
        $("#janela_add").on("hide", function() {
            $("#operacao").val("");
        });
        $("#janela_add").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $("#formulario").submit();
            }
        });
    }
);

function procurar() {
    $(".linha_resultado, .paginacao_ref").remove();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/info/listar/format/json/",
        "type" : "POST",
        "data" : { "filtro_id_info_tipo": $("#jan_filtro_id_info_tipo").val(),
                   "filtro_titulo": $("#filtro_titulo").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 14 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#tabela_lista"));
                    $('<td><a href="#" class="link_resultado" id="' + item.id + '">' + item.data + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_resultado" id="' + item.id + '">' + item.tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_resultado" id="' + item.id + '">' + item.titulo + '</a></td>').appendTo(tr);
                    tr.appendTo($("#tabela_lista"));
                }
                $(".link_resultado").bind("click", function() {
                    $("#jan_id_info").val($(this).attr("id"));
                    $("#operacao").val("");
                    $("#formulario").attr("action", "<?php echo Escola_Util::url(array("action" => "salvareferencia")); ?>");
                    $("#formulario").submit();
                });
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="paginacao_ref">' + html + '</div>').appendTo($("#janela_add .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#tabela_lista"));
            }
        }
    });
}

function adicionar() {
    $("#janela_add").modal("show");
}

function setPage(page) {
    $("#jan_pagina").val(page);
    procurar();
}