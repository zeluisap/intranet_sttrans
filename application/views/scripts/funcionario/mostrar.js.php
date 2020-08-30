$(document).ready(function() {
    CKEDITOR.replace("jan_mensagem", {
        toolbar : [ [ 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike','-','Link', '-', 'MyButton' ] ]
    });
    $("#bt_jan_cancelar").click(fecharJanela);
    $("#janela_mensagem").css( { "width": "900px", "margin-left": "-450px", "top": "50px" } );
});

function mensagem() {
    maskara(1);
    $("#janela_mensagem").show();
    $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "funcionario", "action" => "mensagem")); ?>");
}

function fecharJanela() {
    $("#mask1").remove();
    $("#janela_mensagem").hide();
    $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => "funcionario", "action" => "funcionario")); ?>");
}