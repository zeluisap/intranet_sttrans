var ajax_obj = false;
$(document).ready(function() {
    $("#id_servico_transporte_grupo").change(function() {
        atualiza_datas();
    });
    $("#ano_referencia").change(function() {
        atualiza_datas();
    });
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
                    $("#data_inicio").val(obj.data_inicio);
                    $("#data_validade").val(obj.data_validade);
                    $("#data_vencimento").val(obj.data_vencimento);
                    $(".linha_dados").show();
                }
            }
        });
    }
}