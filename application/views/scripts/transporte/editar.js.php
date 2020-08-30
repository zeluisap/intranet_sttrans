<?php
$obj_string = serialize($this->view);
?>
var ajax_obj = false;
$(document).ready(
	function() {
        $("#id_transporte_grupo").change(function() {
            if (ajax_obj) {
                ajax_obj.abort();
            }
            $(".transporte_field").remove();
            if ($(this).val().length > 0) {
                ajax_obj = $.ajax({
                    "url": "<?php echo Escola_Util::getBaseUrl(); ?>/transporte/transporteform/format/json/",
                    "type": "POST",
                    "data": { "id_transporte_grupo" : $(this).val(), "id_transporte" : $("#id").val() },
                    "success": function(view) {
                        if (view.result) {
                            $("#grupo_transporte").append(view.result);
                        }
                        $("#form_concessao").hide();
                        if (view.possui_concessao) {
                            $("#form_concessao").show();
                        }
                    }
                });
            }
        }).change();
		jQuery("id_transporte_grupo").focus();
	}
);