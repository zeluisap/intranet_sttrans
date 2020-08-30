$(document).ready(function() {
    $("#janela_pc").modal("hide").css({"width": "900px", "margin-left": "-450px"});
    $("#janela_pc").on("show", function() {
        $("#janela_pc_arquivo").val("");
        $("#formulario").attr("action", "<?php echo Escola_Util::url(array("controller" => $this->getRequest()->getControllerName(), "action" => "addprestacaoconta")); ?>");
    });
    $("#janela_pc").on("shown", function() {
        $("#janela_pc_arquivo").focus();
    });
    $("#janela_pc").on("hide", function() {
        $("#formulario").attr("action", "<?php echo Escola_Util::url(array("controller" => $this->getRequest()->getControllerName(), "action" => "index")); ?>");
    });
    $(".link_pc").click(function(event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var obj = pagamentos[id];
        $("#janela_pc_id").val(obj.id_vinculo_lote);
        $("#show_lote").text(obj.referencia);
        $("#janela_pc").modal("show");
    });
});