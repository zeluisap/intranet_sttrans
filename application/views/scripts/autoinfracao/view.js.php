var obj_ajax_notificacao = false;
$(document).ready(function() {
    $(".btn_view_notificacao").click(function(event) {
        event.preventDefault();
        var id = $(this).attr("id");
        if (obj_ajax_notificacao != false) {
            obj_ajax_notificacao.abort();
        }
        ajax_notificacao_obj = $.ajax({
            "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/autoinfracaonotificacao/html/format/json/",
            "type" : "POST",
            "data" : { "id": id },
            "success" : function(result) {
                if (result.html) {
                    $("#janela_notificacao .modal-body").html(result.html);
                }
                $("#janela_notificacao").modal("show");
            }
        });
    });
    $("#janela_notificacao").css({ "width": "800px", "margin-left":"-400px" }).modal("hide");
});