$(document).ready(function() {
    $("#janela_add_lote").modal("hide");
    $("#janela_add_lote").on("show", function() {
        $("#jan_ano").val("<?php echo date("Y"); ?>");
        $("#jan_mes").val("<?php echo date("n"); ?>");
        $("#formulario").attr("action", "<?php echo Escola_Util::url(array("controller" => $this->getRequest()->getControllerName(), "action" => "addlote")); ?>");
    });
    $("#janela_add_lote").on("shown", function() {
        $("#jan_ano").focus();
    });
    $("#janela_add_lote").on("hide", function() {
        $("#formulario").attr("action", "<?php echo Escola_Util::url(array("controller" => $this->getRequest()->getControllerName(), "action" => "lote")); ?>");
    });
});

function adicionar() {
    $("#janela_add_lote").modal("show");
}