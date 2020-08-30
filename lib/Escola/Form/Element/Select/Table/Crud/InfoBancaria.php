<?php
class Escola_Form_Element_Select_Table_Crud_InfoBancaria extends Escola_Form_Element_Select_Table_Crud {
	
	protected $_tipo = "";
    protected $_chave = 0;
    
    public function init() {
        parent::init();
        $this->setPkName("id_info_bancaria");
        $this->setModel("TbInfoBancaria");        
    }
	
	public function set_tipo($tipo) {
		$this->_tipo = $tipo;
	}
    
    public function set_chave($chave) {
        $this->_chave = $chave;
    }
    
	public function pega_tipo() {
		return $this->_tipo;
	}
    
    public function pega_chave() {
        return $this->_chave;
    }
    
	public function janela_modal(Zend_View_Interface $view = null) {
		ob_start();
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#janela_crud_<?php echo $this->getName(); ?> #bt_crud_submit").click(function() {
            ajax = $.ajax({
                "url" : "<?php echo $view->baseUrl(); ?>/infobancaria/salvar/format/json/",
                "type" : "POST",
                "data" : { "id_info_bancaria_tipo" : $("#<?php echo $this->getName(); ?>_id_info_bancaria_tipo").val(), 
                           "id_banco" : $("#<?php echo $this->getName(); ?>_id_banco").val(), 
                           "agencia" : $("#<?php echo $this->getName(); ?>_agencia").val(), 
                           "agencia_dv" : $("#<?php echo $this->getName(); ?>_agencia_dv").val(), 
                           "conta" : $("#<?php echo $this->getName(); ?>_conta").val(), 
                           "conta_dv" : $("#<?php echo $this->getName(); ?>_conta_dv").val(), 
                           "tipo": "<?php echo $this->_tipo; ?>", 
                           "chave": $("#<?php echo $this->_chave; ?>").val() },
                "success" : function(obj_view) {
                    if (obj_view.result) {
                        if (obj_view.result.mensagem) {
                            $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud .mensagem_erro").html(obj_view.result.mensagem);
                            $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud").show();
                        }
                        if (obj_view.result.id) {
                            reloadCrud<?php echo $this->getName(); ?>(obj_view.result.id);
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
        $("#<?php echo $this->_chave; ?>").change(function() {
            $("#linha_<?php echo $this->getName(); ?>").hide();
            if ($("#<?php echo $this->_chave; ?>").val().length) {
                $("#linha_<?php echo $this->getName(); ?>").show();
                reloadCrud<?php echo $this->getName(); ?>(<?php echo $this->getName(); ?>_id_default);
            }
        }).change();
        $("#janela_crud_<?php echo $this->getName(); ?>").css({ "width": "700px", "margin-left": "-350px" });
	});
	function reloadCrud<?php echo $this->getName(); ?>(default_id) {
		var ctrl = $("#<?php echo $this->getName(); ?>");
        var tipo = "P";
        var chave = $("#<?php echo $this->_chave; ?>").val();
		ctrl.children().remove();
		$("<option value=''>==> SELECIONE <==</option>").appendTo(ctrl);
		if ($("#<?php echo $this->_chave; ?>").val().length) {
			ajax = $.ajax({
				"url" : "<?php echo $view->baseUrl(); ?>/infobancaria/listar/format/json/",
				"type" : "POST",
				"data" : { "tipo" : tipo, "chave": chave },
				"success" : function(obj_view) {
					if (obj_view.result) {
						for (var x = 0; x < obj_view.result.length; x++) {
							var obj = obj_view.result[x];
							var selected = "";
							if (obj.id == default_id) {
								selected = " selected ";
							}
							$("<option value='" + obj.id + "' " + selected + ">" + obj.descricao + "</option>").appendTo(ctrl);
						}
					}
					ctrl.change();
				}
			});
		} else {
			ctrl.change();
		}
	}
</script>
<div id="janela_crud_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 id="myModalLabel">Adicionando Registro - Informações Bancárias</h4>
    </div>
    <div class="modal-body">
        <div class="alert" id="msg_erro_crud">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div class="mensagem_erro"></div>
        </div>
        <fieldset>
        <?php
            $ctrl = new Escola_Form_Element_Select_Table($this->getName() . "_id_info_bancaria_tipo");
            $ctrl->setPkName("id_info_bancaria_tipo");
            $ctrl->setModel("TbInfoBancariaTipo");
            $ctrl->setLabel("Tipo: ");
            $ctrl->setAttrib("class", "crud_cadastro");
            echo $ctrl->render($view);
            
            $ctrl = new Escola_Form_Element_Select_Table($this->getName() . "_id_banco");
            $ctrl->setPkName("id_banco");
            $ctrl->setModel("TbBanco");
            $ctrl->setLabel("Banco: ");
            $ctrl->setAttrib("class", "crud_cadastro");
            echo $ctrl->render($view);
        ?>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_agencia" class="control-label">Agência:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_agencia" id="<?php echo $this->getName(); ?>_agencia" class="span2 crud_cadastro" value="" /> - <input type="text" name="<?php echo $this->getName(); ?>_agencia_dv" id="<?php echo $this->getName(); ?>_agencia_dv" class="span1 crud_cadastro" value="" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_conta" class="control-label">Conta:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_conta" id="<?php echo $this->getName(); ?>_conta" class="span3 crud_cadastro" value="" /> - <input type="text" name="<?php echo $this->getName(); ?>_conta_dv" id="<?php echo $this->getName(); ?>_conta_dv" class="span1 crud_cadastro" value="" />
                </div>
            </div>
        </fieldset>
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