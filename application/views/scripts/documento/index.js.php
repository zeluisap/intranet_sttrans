var ajax_obj = null;
$(document).ready(
    function() {
        $("#filtro_avancado").change(
            function() {
                if ($(this).attr("checked")) {
                    $(".linha_avancado").show();
                } else {
                    limpaFiltroAvancado();
                    $(".linha_avancado").hide();
                }
            }
        );
		var filtros = $(".filtro_avancado");
		if (filtros.length) {
			filtros.each(function() {
				if ($(this).val() != "") {
                    $("#filtro_avancado").attr("checked", true);
					$(".linha_avancado").show();
					return true;
				}
			});
		}
		$("#bt_janela_confirmar").click(
			function() {
				$("#formulario").submit();
			}
		);
		$(".link_vincular").click(
			function(event) {
				event.preventDefault();
				$("#operacao").val("vincular");
				$("#jan_id_documento").val($(this).attr("id"));
                $("#janela_vinculo").modal("show");
			}
		);
		$(".bt_destino_cancelar").click(
			function() {
			    $("#destino_resposta").hide();
				$("#mask1, #destino_resposta tr").remove();
				$("#janela_vinculo").hide();
				$("#jan_id_documento").val("");
				$("#info_documento").text("");
				$("#operacao").val("");
			}
		);
		$("#bt_destino_limpar").click(
			function() {
				$("#jan_id_documento_tipo, #jan_numero, #jan_ano, #jan_pagina").val("");
				$("#formulario").submit();
			}
		);
		$("#formulario").submit(
			function(event) {
				if ($("#operacao").val() == "vincular") {
					event.preventDefault();
					procurarVinculo();
					return false;
				}
				return true;
			}
		);
		$("#janela_processo").css({ "width": "600px", "margin-left": "-300px" }).modal("hide");
        $("#janela_processo").on("shown", function() {
            $("#janela_processo").find("input:text").val("");
            $("#janela_processo").find("input").first().focus();
        });
        $("#janela_processo, #janela_arquivar").on("hide", function() {
            $("#formulario").attr("action", "<?php echo $this->view->url(array("action" => "index")); ?>");
        });
        $("#janela_arquivar").modal("hide");
        $(".link_receber").click(function(event) {
            event.preventDefault();
            $("#formulario").attr("action", $(this).attr("href"));
            $("#janela_arquivar").modal("show");
        });
        $("#bt_arquivar_confirmar").click(function() {
            $("#formulario").submit();
        });
        $(".link_processo").click(function(event) {
            event.preventDefault();
            $("#formulario").attr("action", $(this).attr("href"));
            $("#janela_processo").modal("show");
        });
        $("#janela_vinculo").css( { "width": "800px", "margin-left": "-400px" } ).modal("hide");
        $("#janela_vinculo").on("show", function() {
            procurarVinculo();        
        });
        $("#janela_vinculo").on("shown", function() {
            $("#janela_vinculo .filtro").first().focus();
        });
        $("#janela_vinculo").on("hide", function() {
            $("#operacao").val("");
        });
        $("#janela_vinculo").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $("#formulario").submit();
            }
        });
    }
);

function limpaFiltroAvancado() {
    $(".filtro_avancado").val("");
}

function setPage(pagina) {
    $("#jan_pagina").val(pagina);
    $("#formulario").submit();
}

function procurarVinculo() {
    $("#destino_resposta tr, .paginacao_vinculo").remove();
	var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/documento/info/format/json/",
        "type" : "POST",
        "data" : { "id_documento": $("#jan_id_documento").val() },
        "success" : function(result) {
        	if (result) {
        		$("#info_documento").text(result.doc);
			    $("#destino_resposta").hide();
			    var ajax_obj1 = $.ajax({
			        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/documento/listarvinculoporpagina/format/json/",
			        "type" : "POST",
			        "data" : { "filtro_id_documento_tipo": $("#jan_id_documento_tipo").val(),
			                   "pagina_atual": $("#jan_pagina").val(),
			                   "filtro_numero": $("#jan_numero").val(),
			                   "filtro_ano": $("#jan_ano").val(),
			                   "qtd_por_pagina": 20,
			                   "filtro_id_documento": $("#jan_id_documento").val() },
			        "success" : function(result) {
			            destinos = [];
			            $('<tr><th width="50px">ID</th><th>Tipo</th><th width="160px">Número</th><th>Resumo</th></tr>').appendTo($(".head_destino"));
			            if (result.items && result.items.length) {
			                for (var i = 0; i < result.items.length; i++) {
			                    var item = result.items[i];
			                    destinos[i] = item;
			                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#destino_resposta"));
			                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/documento/vincular/id_documento/' + $("#jan_id_documento").val() + '/id_documento_anexo/' + item.id + '">' + item.id + '</a></td>').appendTo(tr);
			                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/documento/vincular/id_documento/' + $("#jan_id_documento").val() + '/id_documento_anexo/' + item.id + '">' + item.tipo + '</a></td>').appendTo(tr);
			                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/documento/vincular/id_documento/' + $("#jan_id_documento").val() + '/id_documento_anexo/' + item.id + '">' + item.numero + '</a></td>').appendTo(tr);
			                    $('<td><a href="<?php echo Escola_Util::getBaseUrl(); ?>/documento/vincular/id_documento/' + $("#jan_id_documento").val() + '/id_documento_anexo/' + item.id + '">' + item.resumo + '</a></td>').appendTo(tr);
			                    tr.appendTo($(".corpo_destino"));
			                }
			                var paginacao = new Paginacao();
			                paginacao.total_pagina = result.total_pagina;
			                paginacao.pagina_atual = result.pagina_atual;
			                paginacao.primeira = result.primeira;
			                paginacao.ultima = result.ultima;
			                var html = paginacao.render();
			                $('<div class="paginacao_vinculo">' + html + '</div>').appendTo($("#janela_vinculo .modal-body"));
			            } else {
			                $("<tr class='linha_resultado'><td colspan='4' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#destino_resposta"));
			            }
                        $("#destino_resposta").show();
			        }
			    });

        	}
        }
    });
}