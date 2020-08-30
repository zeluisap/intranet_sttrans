<?php
$tb = new TbPrevisaoTipo();
$pt = $tb->getPorChave("BO");
?>
var ajax_obj_lote_item = ajax_obj_bolsa_tipo = false;
$(document).ready(
    function() {
        $("#id_previsao_tipo").focus();
        $("#id_previsao_tipo").change(function() {
        
            $(".linha_id_bolsa_tipo").hide();
            if ($(this).val() != "") {
<?php if ($pt) { ?>
                if ($(this).val() != "<?php echo $pt->getId(); ?>") {
<?php } ?>
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
                                var controle_bolsa_tipo = $("#id_bolsa_tipo");
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
                                $(".linha_id_bolsa_tipo").show();
                            }
                        }
                    });    
<?php if ($pt) { ?>
                }
<?php } ?>
            }
            
            $(".linha_tipo_vinculo").html("");
            if (ajax_obj_lote_item) {
                ajax_obj_lote_item.abort();
            }
            if ($(this).val()) {
                ajax_obj_lote_item = $.ajax({
                    "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/vinculo/vinculoloteitemform/format/json/",
                    "type" : "POST",
                    "data" : { "id_previsao_tipo": $("#id_previsao_tipo").val(),
                               "id_vinculo_lote": "<?php echo $this->view->registro->getId(); ?>" },
                    "success" : function(result) {
                        if (result.toform) {
                            $(".linha_tipo_vinculo").html(result.toform);
                            $(".moeda").priceFormat({
                                prefix: '',
                                centsSeparator: ',', 
                                thousandsSeparator: '.',
                                limit: false,
                                centsLimit: 2
                            });
                        }
                    }
                });    
            }
        }).change();
    }
);