$(document).ready(
	function() {
		$("#finaliza").focus();
		$("#finaliza").change(
			function() {
				$("#linha_nota").hide();
				if ($(this).val() == "S") {
					$("#nota").val("");
					$("#linha_nota").show();
				}
			}
		);
	}
);