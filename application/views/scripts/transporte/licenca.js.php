$(document).ready(function() {
    
});

function boleto() {
/*
    if ($(".lista:checked").length <= 0) {
        $(".lista").attr("checked", true);
    }
*/
    $("#formulario").attr("action", "<?php echo Escola_Util::url(array("action" => "boletovencimento")); ?>");
    $("#formulario").submit();
}