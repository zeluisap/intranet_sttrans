var ajax_obj = false;
$(document).ready(
    function() {
<?php if (!$this->view->registro->getId()) { ?>
        $("#id_documento_tipo").change(function() {
            $.limpaAlerta();
            $(".documento_conteudo").html("");
            if ($(this).val().length) {
                try {
                    if (ajax_obj) {
                        ajax_obj.abort();
                    }
                    ajax_obj = $.ajax({
                        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/documento/toform/format/json/",
                        "type" : "POST",
                        "data" : { "id_documento_tipo": $("#id_documento_tipo").val(),
                                   "id_funcionario" : "<?php echo $this->view->funcionario->getId(); ?>",
                                   "id_documento" : "<?php echo $this->view->registro->getId(); ?>" },
                        "success" : function(view) {
                            try {
                                if (!view.result) {
                                    throw { "message": "FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!" };
                                }
                                $(".documento_conteudo").html(view.result);    
                            } catch (ex) {
                                $.mensagemAlerta({ "mensagem" : ex.message });
                            }                            
                        },
                        "error" : function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR);
                            console.log(textStatus);
                            console.log(errorThrown);
                        }
                    });
                } catch (ex) {
                    $.mensagemAlerta({ "mensagem" : ex.message });
                }
            }
        });
<?php } ?>
    }
);