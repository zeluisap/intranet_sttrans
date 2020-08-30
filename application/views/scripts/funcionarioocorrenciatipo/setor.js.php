<?php
$tb = new TbSetor();
$pms = $tb->pegaInstituicao();
?>
$(document).ready(
    function() {
        $("#formulario").submit(
            function(event) {
                if ($("#operacao").val() == "setor") {
                    event.preventDefault();
                    procurarSetor();
                    return false;
                }
                return true;
            }
        );
        $(".bt_setor_cancelar").click(
            function() {
                fecharSetor();
            }
        );
        $("#bt_setor_limpar").click(
            function() {
                $("#jan_pagina, #jan_setor_criterio").val("");
                procurarSetor();
            }
        );
        $("#janela_setor").css( { "top": "0px", "z-index": "3", "width": "800px", "margin-left": "-400px", "position": "absolute" } );
    }
);


function setPage(pagina) {
    $("#jan_pagina").val(pagina);
    $("#formulario").submit();
}

function setor() {
    maskara(2);
    procurarSetor();
    $("#janela_setor").show();
    $("#jan_setor_criterio").focus();
    $("#operacao").val("setor");
}

function fecharSetor() {
    $("#mask2").remove();
    $("#janela_setor").hide();
    $("#operacao, #jan_setor_criterio").val("");
}

function procurarSetor() {
    $("#lista_setor_resposta .linha_resultado").remove();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/setor/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_criterio": $("#jan_setor_criterio").val(),
                   "pagina_atual": $("#jan_pagina").val(),
<?php if ($pms) { ?>
                    "filtro_id_setor_procedencia": "<?php echo $pms->getId(); ?>",
<?php } ?>
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#lista_setor_resposta"));
                    $('<td><a href="#" id="' + item.id + '" class="link_setor">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + item.id + '" class="link_setor">' + item.sigla + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + item.id + '" class="link_setor">' + item.descricao + '</a></td>').appendTo(tr);
                    tr.appendTo($(".corpo_setor"));
                }
                $(".link_setor").click(
                    function(event) {
                        event.preventDefault();
                        $("#id_setor").val($(this).attr("id"));
                        $("#operacao").val("");
                        $("#formulario").submit();
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<tr class="linha_resultado"><td align="center" colspan="3">' + html + '</td></tr>').appendTo($("#lista_setor_resposta"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#lista_setor_resposta"));
            }
            $("#mask2").height($(document).height());
        }
    });
}