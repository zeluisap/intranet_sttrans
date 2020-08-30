<?php
?>
$(document).ready(
	function() {
        $("#id_moeda").change(function() {
            $(".addonvalor").text("");
            var ctrl = $("#id_moeda option:selected");
            if (ctrl.length) {
                $(".addonvalor").text(ctrl.attr("rel"));
            }
        }).change();
		$("#codigo").focus();
	}
);