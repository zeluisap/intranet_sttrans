$(document).ready(function() {
    $(".filtro_pf, .filtro_pj").hide();
    $("#filtro_id_pessoa_tipo").change(function() {
        $(".filtro_pf, .filtro_pj").hide();
        var sigla = $("#filtro_id_pessoa_tipo option:selected").attr("rel");
        if (sigla && sigla.length) {
            $(".filtro_" + sigla).show();
            $(".filtro_" + sigla).first().find("input").focus();
        }
    }).change();
    $("#jan_id_pessoa_tipo").change(function() {
        $(".jan_pf, .jan_pj").hide();
        var sigla = $("#jan_id_pessoa_tipo option:selected").attr("rel");
        if (sigla && sigla.length) {
            $(".jan_" + sigla).show();
            $(".jan_" + sigla).first().find("input").focus();
        }
    });
    $("#janela_pessoa_tipo").css( { "width": "700px", "margin-left": "-350px" } );
    $("#janela_pessoa_tipo").modal("hide");
    $("#janela_pessoa_tipo").on("show", function() {
        $("#jan_id_pessoa_tipo, .jan_pf, .jan_pj").val("");
        $("#jan_id_pessoa_tipo").change();
    });
    $("#janela_pessoa_tipo").on("shown", function() {
        $("#jan_id_pessoa_tipo").focus();
    });
    $("#janela_pessoa_tipo").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            $("#jan_formulario").submit();
        }
    });
});

function adicionar() {
    $("#janela_pessoa_tipo").modal("show");
}