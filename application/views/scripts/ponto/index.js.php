$(document).ready(
	function() {
		$(".link_lotacao").click(
			function(event) {
				event.preventDefault();
				var obj = $(".lista[value='" + $(this).attr("id") + "']");
				obj.attr("checked", !obj.attr("checked"));
				showImprimir();
			}
		);
		$("input:checkbox").change(showImprimir);
	}
);

function showImprimir() {
	$("#bt_imprimir").hide();
	if ($(".lista:checked").length) {
		$("#bt_imprimir").show();
	}
}

function imprimir() {
	$("#formulario").submit();
}