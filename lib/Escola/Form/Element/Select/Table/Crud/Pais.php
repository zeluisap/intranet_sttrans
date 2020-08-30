<?php
class Escola_Form_Element_Select_Table_Crud_Pais extends Escola_Form_Element_Select_Table_Crud {
    
	public function janela_modal(Zend_View_Interface $view = null) {
		ob_start();
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#janela_crud_<?php echo $this->getName(); ?> #bt_crud_submit").click(function() {
            ajax = $.ajax({
                "url" : "<?php echo $view->baseUrl(); ?>/pais/salvar/format/json/",
                "type" : "POST",
                "data" : { "descricao" : $("#janela_crud_<?php echo $this->getName(); ?> #descricao").val() },
                "success" : function(obj_view) {
                    if (obj_view.result) {
                        if (obj_view.result.mensagem) {
                            $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud .mensagem_erro").html(obj_view.result.mensagem);
                            $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud").show();
                        }
                        if (obj_view.result.id) {
                            reloadCrud_<?php echo $this->getValue(); ?>(obj_view.result.id);
                            //$("#mask1").remove();
                            $("#janela_crud_<?php echo $this->getName(); ?>").modal("hide");
                        }
                    }
                }
            });
			return false;
		});
        $("#janela_crud_<?php echo $this->getName(); ?>").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $("#janela_crud_<?php echo $this->getName(); ?> #bt_crud_submit").click();
            }
        });
        $("#descricao").keyup(function() {
            $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud").hide();
        });
        $("#janela_crud_<?php echo $this->getName(); ?>").css({ "width": "600px", "margin-left": "-300px" });
	});
	function reloadCrud_<?php echo $this->getValue(); ?>(default_id) {
        ajax = $.ajax({
            "url" : "<?php echo $view->baseUrl(); ?>/pais/listar/format/json/",
            "type" : "POST",
            "success" : function(obj_view) {
                if (obj_view.result) {
                    var ctrl = $("#<?php echo $this->getName(); ?>");
                    ctrl.children().remove();
                    $("<option value=''>==> SELECIONE <==</option>").appendTo(ctrl);
                    for (var x = 0; x < obj_view.result.length; x++) {
                        var obj = obj_view.result[x];
                        var selected = "";
                        if (obj.id == default_id) {
                            selected = " selected ";
                        }
                        $("<option value='" + obj.id + "' " + selected + ">" + obj.descricao + "</option>").appendTo(ctrl);
                    }
                }
                $("#<?php echo $this->getName(); ?>").change();
            }
        });
	}
</script>
<div id="janela_crud_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Adicionando Registro - País</h3>
    </div>
    <div class="modal-body">
        <div class="alert" id="msg_erro_crud">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div class="mensagem_erro"></div>
        </div>
        <div class="control-group">
            <div class="control-label">Descrição:</div>
            <div class="controls">
                <input type="text" name="descricao" id="descricao" maxlength="50" class="crud_cadastro span6" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <button class="btn btn-primary" id="bt_crud_submit">Salvar</button>
    </div>
</div>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}    
}