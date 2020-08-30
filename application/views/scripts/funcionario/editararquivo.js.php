<?php
$id_documento_tipo = 0;
$doc = $this->view->documento;
if ($doc) {
    $id_documento_tipo = $doc->id_documento_tipo;
}
?>
var ajax_obj = false;
$(document).ready(
    function() {
        var tds = [];
        var tipo_docs = [];
<?php
$tb = new TbDocumentoTipoTarget();
$dtts = $tb->listar();
if ($dtts) {
    foreach ($dtts as $dtt) {
        if (!$dtt->normal()) {
            $tb = new TbDocumentoTipo();
            $dts = $tb->listar(array("filtro_id_documento_tipo_target" => $dtt->getId()));
            if ($dts) {
?>
                tds[<?php echo $dtt->getId(); ?>] = [];
<?php 
                $contador = 0;
                foreach ($dts as $dt) {
?>
                    tds[<?php echo $dtt->getId(); ?>][<?php echo $contador; ?>] = { "id": <?php echo $dt->getId(); ?>, "descricao": '<?php echo $dt->toString(); ?>', 'possui_numero': '<?php echo $dt->possui_numero; ?>' };
                    tipo_docs[<?php echo $dt->getId(); ?>] = { "id": <?php echo $dt->getId(); ?>, "descricao": '<?php echo $dt->toString(); ?>', 'possui_numero': '<?php echo $dt->possui_numero; ?>' };
<?php $contador++; }}}}} ?>
        $("#id_documento_tipo_target").change(
            function() {
                $(".linha_documento_tipo").hide();
                $("#id_documento_tipo").children().remove();
                if ($(this).val().length) {
                    $('<option value="">==> SELECIONE <== </option>').appendTo($("#id_documento_tipo"));
                    for (var i = 0; i < tds[$(this).val()].length; i++) {
                        var obj = tds[$(this).val()][i];
                        var selected = "";
                        if (obj.id == '<?php echo $id_documento_tipo; ?>') {
                            selected = " selected ";
                        }
                        $('<option value="' + obj.id + '" ' + selected + '>' + obj.descricao + '</option>').appendTo($("#id_documento_tipo"));
                    }
                    $(".linha_documento_tipo").show();
                }
<?php
$tb = new TbDocumentoTipoTarget();
$dtt = $tb->getPorChave("A");
if ($dtt) {
?>
                $(".linha_cadastro, .linha_documento_cadastro, .linha_documento").hide();
                if ($(this).val() == '<?php echo $dtt->getId(); ?>') {
                    $(".linha_cadastro").show();
                }
<?php } ?>
            }
        ).change();
        $("#id_documento_tipo").change(
            function() {
            	var obj = false;
            	if ($(this).val() && $(this).val().length) {
            		var obj = tipo_docs[$(this).val()];
            	}
                    $(".linha_documento, .linha_cadastro, .linha_documento_cadastro").hide();   
                	if (obj) {
                		if  (obj.possui_numero == "S") {
		                    if ($(this).children().length && $(this).val().length ) {
		                        $(".linha_documento").show();
		                    }
	            	    } else {
							$(".linha_cadastro").show();	                
		                }
		            }
            }
        ).change();
        $("#link_documento").click(
            function() {
                $("#jan_pagina, #id_documento").val("");
                $("#show_documento").text("");
                $("#operacao").val("documento");
                $("#janela_documento").modal("show");
                limparFiltro();
            }
        );
        $(".bt_documento_cancelar").click(
            function() {
                fecharJanela();
            }
        );
        $("#bt_documento_limpar").click(
            function() {
                limparFiltro();
            }
        );
        $("#formulario").submit(
            function() {
                if ($("#operacao").val() == "documento") {
                    procurarDocumento();
                    return false;
                }
                return true;
            }
        );
        $("#bt_documento_novo").click(
            function() {
                fecharJanela();
                $(".linha_documento").hide();
                $(".linha_cadastro, .linha_documento_cadastro").show();
                $(".linha_documento_cadastro input").first().focus();
                $(".field").val("");
            }
        );
        $("#janela_documento").css( { "width": "1000px", "margin-left": "-500px" } ); 
        $("#janela_documento").modal("hide").on("shown", function() {
            $(".filtro").first().focus();
        });
        $("#janela_documento").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                procurarDocumento();
            }
        });
    }
);

function setPage($page) {
    $("#jan_pagina").val($page);
    $("#formulario").submit();
}

function limparFiltro() {
    $(".filtro").val("");
    procurarDocumento();
}

function fecharJanela() {
    $("#operacao").val("");
    $("#janela_documento").modal("hide");
}

function procurarDocumento() {    
    if (ajax_obj) {
        ajax_obj.abort();
    }
    $(".linha_resultado, .paginacao_arquivo").remove();
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/documento/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_numero": $("#filtro_numero").val(),
                   "filtro_ano": $("#filtro_ano").val(),
                   "filtro_resumo": $("#filtro_resumo").val(),
                   "filtro_id_documento_tipo": $("#id_documento_tipo").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 15 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                infos = [];
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    infos[i] = { "id": item.id,
                                 "data_hora":  item.data_hora,
                                 "tipo": item.tipo,
                                 "numero": item.numero,
                                 "resumo": item.resumo }; 
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#documento_resposta"));
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.data_hora + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.numero+ '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.resumo + '</a></td>').appendTo(tr);
                    tr.appendTo($(".corpo_documento"));
                }
                $(".link_seleciona").bind("click",
                    function(event) {
                        event.preventDefault();
                        $("#id_documento").val($(this).attr("id"));
                        $("#operacao").val("set_documento");
                        $("#formulario").submit();
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="paginacao_arquivo">' + html + '</div>').appendTo($("#janela_documento .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='5' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#documento_resposta"));
            }
        }
    });    
}