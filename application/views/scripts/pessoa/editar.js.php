<?php
?>
$(document).ready(
	function() {
        $("#possui_cnh").change(function() {
            $("#pessoa_motorista").hide();
            if ($(this).val() == "S") {
                $("#pessoa_motorista").show();
            }
        }).change();
		$("#email").focus();
	}
);