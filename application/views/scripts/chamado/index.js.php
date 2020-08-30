$(document).ready(
    function() {
        $("#filtro_avancado").change(
            function() {
                if ($(this).attr("checked")) {
                    $(".linha_avancado").show();
                } else {
                    limpaFiltroAvancado();
                    $(".linha_avancado").hide();
                }
            }
        );
        $("#filtro_opcao").change(
        	function() {
        		$(".linha_setor").hide();
        		if ($(this).val() == "cx") {
        			$(".linha_setor").show();
        		}
        	}
        ).change();
		var filtros = $(".filtro_avancado");
		if (filtros.length) {
			filtros.each(function() {
				if ($(this).val() != "") {
                    $("#filtro_avancado").attr("checked", true).change();
					return true;
				}
			});
		}
    }
);

function limpaFiltroAvancado() {
    $(".filtro_avancado").val("");
}