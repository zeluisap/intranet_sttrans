var ajax_obj = false;
$(document).ready(function() {
    $("#ano_referencia").change(function() {
        atualiza_datas();
<?php if ($this->view->interdicao->getId()) { ?>
    });
<?php } else { ?>
    }).change();
<?php } ?>
    
    $("#id_pessoa_tipo").change(function() {
        $("#linha_id_pessoa_fisica, #linha_id_pessoa_juridica").hide();
<?php
$tb = new TbPessoaTipo();
$pf = $tb->getPorChave("PF");
if ($pf) {
?>
        if ($(this).val() == "<?php echo $pf->getId(); ?>") {
            $("#linha_id_pessoa_fisica").show();
        }
<?php } ?>
<?php
$pj = $tb->getPorChave("PJ");
if ($pj) {
?>
        if ($(this).val() == "<?php echo $pj->getId(); ?>") {
            $("#linha_id_pessoa_juridica").show();
        }
<?php } ?>
    }).change();
    $("#isento").change(function() {
        $(".linha_valor, .linha_motivo").hide();
        if ($(this).val() == "S") {
            $(".linha_motivo").show();
        } else {
            $(".linha_valor").show();
        }
    }).change();
    $("#titulo").focus();
});

function atualiza_datas() {
    $(".linha_dados").hide();
    $(".field").val("");
    $("#valor").text("");
    if (ajax_obj) {
        ajax_obj.abort();
    }
    if ($("#id_servico_transporte_grupo").val().length && $("#ano_referencia").val().length) {
        ajax_obj = $.ajax({
            "url": "<?php echo Escola_Util::getBaseUrl(); ?>/servicotransportegrupo/info/format/json/",
            "type": "POST",
            "data": { "ano_referencia": $("#ano_referencia").val(), "id_servico_transporte_grupo" : $("#id_servico_transporte_grupo").val(), "id_transporte" : $("#id_transporte").val() },
            "success": function(view) {
                if (view.result) {
                    var obj = view.result;
                    $("#valor").text(obj.valor);
                    $("#data_validade").val(obj.data_validade);
                    $("#data_vencimento").val(obj.data_vencimento);
                    $(".linha_dados").show();
                    //$("#ano_referencia").focus().select();
                }
            }
        });
    }
}