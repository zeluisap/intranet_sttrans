$(document).ready(
	function() {
        $("#formulario").submit(
            function(event) {
                if ($("#operacao").val() == "destino") {
                    procurarDestino();
                    return false
                }
                return true;
            }
        );
		$("#despacho").focus().select();
	}
);