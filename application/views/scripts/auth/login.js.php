$(document).ready(
	function() {
		$("#login_cpf").focus();
		$("#bt_cadastro").click(
			function() {
				window.location = "auth/identify";
			}
		);
	}
);