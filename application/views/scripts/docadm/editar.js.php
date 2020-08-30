var ajax_obj = false;
$(document).ready(
	function() {
        $("#id_documento_tipo").change(function() {
            $(".linha_numero").hide();
            var id_documento_tipo = $(this).val();
            if (ajax_obj) {
                ajax_obj.abort();
            }
            if ($.trim(id_documento_tipo) != "") {
                ajax_obj = $.ajax({
                    "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/documentotipo/dados/format/json/",
                    "type" : "POST",
                    "data" : { "id": id_documento_tipo },
                    "success" : function(result) {
                        if (result.result && result.result.possui_numero) {
                            $(".linha_numero").show();
                        }
                    }
                });
            }
        }).change();
		$("#id_documento_tipo").focus().select();
	}
);