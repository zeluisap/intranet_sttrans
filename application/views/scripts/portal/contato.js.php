$(document).ready(function() {
    $("#btn_limpar").click(function() {
        $(".field").val("");
        $("#nome").focus();
    });
    $("#nome").focus();
});