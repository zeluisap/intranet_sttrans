<?php
$tb = new TbMenuTipo();
$mt = $tb->getPorChave("N");
$tb = new TbMenuPosicao();
$mp = $tb->getPorChave("E");
?>
var infos = [];
$(document).ready(
	function() {
        $("#id_menu_tipo").change(
            function() {
                $(".url_texto, .url_info").hide();
                if ($(this).val() == '<?php echo $mt->getId(); ?>') {
                    $(".url_info").show();
                } else {
                    if (!isNaN($("#url").val())) {
                        $("#url").val("");
                    }
                    $(".url_texto").show();
                }
            }
        ).change();
<?php if ($mp) { ?>
        $("#id_menu_posicao").change(
            function() {
                $("#linha_arquivo").hide();
                if ($(this).val() == "<?php echo $mp->getId(); ?>") {
                    $("#linha_arquivo").show();
                }
            }
        ).change();
<?php } ?>
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
        $("#link_localizar").click(
            function() {
                $("#janela_add").modal("show");
            }
        );
        $("#janela_add").css( { "width": "800px", "margin-left": "-400px" } ).modal("hide");
        $("#janela_add").on("show", function() {
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
		$("#id_menu_tipo").focus().select();
	}
);


function procurar() {
    $(".linha_resultado, .paginacao_info").remove();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/info/listar/format/json/",
        "type" : "POST",
        "data" : { "filtro_id_info_tipo": $("#jan_filtro_id_info_tipo").val(),
                   "filtro_titulo": $("#filtro_titulo").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 14 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                infos = [];
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    infos[i] = { "id": item.id, "descricao":  item.tipo + ' - ' + item.titulo}; 
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#tabela_lista"));
                    $('<td><a href="#" class="link_resultado" id="' + i + '">' + item.data + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_resultado" id="' + i + '">' + item.tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_resultado" id="' + i + '">' + item.titulo + '</a></td>').appendTo(tr);
                    tr.appendTo($("#tabela_lista"));
                }
                $(".link_resultado").bind("click", function() {
                    $("#id_info").val(infos[$(this).attr("id")].id);
                    $("#url_show_info").val(infos[$(this).attr("id")].descricao);
                    $("#janela_add").modal("hide");
                });
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="paginacao_info">' + html + '</div>').appendTo($("#janela_add .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#tabela_lista"));
            }
        }
    });
}

function setPage(page) {
    $("#jan_pagina").val(page);
    procurar();
}