$(document).ready(function() {
	$(".link_modulo").click(function(event) {
		event.preventDefault();
		var obj = $(".lista_pacote[value='" + $(this).attr("id") + "']"); 
		obj.attr("checked", !obj.attr("checked"));
	});
});