$(document).ready(function() {
    $("#ck").change(function() {
        var checked = $(this).attr("checked");
        if (checked == undefined) {
            checked = false;
        }
        $(".lista").attr("checked", checked);
        $(".lista").change();
    }).change();
    $(".lista").change(function() {
        $(".btn_devolver").hide();
        if ($(".lista:checked").length) {
            $(".btn_devolver").show();
        }
    }).change();
    $("#filtro_codigo_inicio").blur(function() {
        $(this).val($(this).val().toUpperCase());
        $(".show_alfa").text($(this).val().substring(0,1));
    }).blur();
    $("#filtro_caracter").blur(function() {
        $(this).val($(this).val().toUpperCase());
        $("#caracter_final").val($(this).val());
    }).blur();
    $("#idLimparPesquisa").click(function() {
        $("#formulario").submit();
    });
    $(".caracter").width("40px");
});

function devolver() {
    $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "devolver")); ?>");
    $("#formulario").submit();
}