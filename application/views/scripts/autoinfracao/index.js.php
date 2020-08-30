$(document).ready(function() {
    $("#filtro_codigo_inicio").blur(function() {
        $(this).val($(this).val().toUpperCase());
        $(".show_alfa").text($(this).val().substring(0,1));
    }).blur();
    $("#filtro_caracter").blur(function() {
        $(this).val($(this).val().toUpperCase());
        $("#caracter_final").val($(this).val());
    }).blur();
    $(".caracter").width("40px");
    $("#filtro_codigo_fim").mask("99999999");
});