var ajax_obj_bolsa_tipo = false;
$(document).ready(
    function() {
        $("#filtro_id_previsao_tipo").change(function() {
            $("#linha_filtro_id_bolsa_tipo").hide();
            if ($(this).val() != "") {
                if (ajax_obj_bolsa_tipo) {
                    ajax_obj_bolsa_tipo.abort();
                }
                ajax_obj_bolsa_tipo = $.ajax({
                    "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/vinculo/listarbolsatipo/format/json/",
                    "type" : "POST",
                    "data" : { "id_previsao_tipo": $(this).val(),
                               "id_vinculo": "<?php echo $this->view->vinculo->getId(); ?>" },
                    "success" : function(result) {
                        if (result.bolsa_tipos) {
                            var controle_bolsa_tipo = $("#filtro_id_bolsa_tipo");
                            controle_bolsa_tipo.children("option").remove();
                            var option = $("<option>", { "value" : "",
                                                       "text" : "==> SELECIONE <==",
                                                       "checked" : true });
                            option.appendTo(controle_bolsa_tipo);
                            for (var i = 0; i < result.bolsa_tipos.length; i++) {
                                var item = result.bolsa_tipos[i];
                                var option = $("<option>", { "value" : item.id, "text" : item.to_string });
                                option.appendTo(controle_bolsa_tipo);
                            }
                            $("#linha_filtro_id_bolsa_tipo").show();
                        }
                    }
                });    
            }
        }).change();
    }
);