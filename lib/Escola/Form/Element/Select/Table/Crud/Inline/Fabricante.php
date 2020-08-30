<?php
class Escola_Form_Element_Select_Table_Crud_Inline_Fabricante extends Escola_Form_Element_Select_Table {
	
    public function init() {
        parent::init();
        $this->setPkName("id_fabricante");
        $this->setModel("TbFabricante");
    }
    
	public function render(Zend_View_Interface $view = null) {
		ob_start();
		$this->_carregaDados();
		$options = $this->getMultiOptions();
?>
<script type="text/javascript">
    var <?php echo $this->getName(); ?>_id_default = "<?php echo $this->getValue(); ?>";
	$(document).ready(function() {
        $("#<?php echo $this->getName(); ?>_btn_salvar").click(function() {
            ajax = $.ajax({
                "url" : "<?php echo $view->baseUrl(); ?>/fabricante/salvar/format/json/",
                "type" : "POST",
                "data" : { "descricao" : $("#<?php echo $this->getName(); ?>_descricao").val() },
                "success" : function(obj_view) {
                    if (obj_view.result) {
                        if (obj_view.result.mensagem) {
                            $(".<?php echo $this->getName(); ?>_mensagem_erro").html(obj_view.result.mensagem);
                            $("#<?php echo $this->getName(); ?>_input_edicao .alert").show();
                        }
                        if (obj_view.result.id) {
                            reload_crud_<?php echo $this->getName(); ?>(obj_view.result.id);
                        }
                    }
                }
            });
			return false;
		});
        $("#link_janela_crud_<?php echo $this->getName(); ?>").click(function(event) {
            event.preventDefault();
            $("#<?php echo $this->getName(); ?>_input_edicao .alert").hide();
            $(".input_select_<?php echo $this->getName(); ?>").hide();
            $("#<?php echo $this->getName(); ?>_input_edicao").show();
            $("#<?php echo $this->getName(); ?>_descricao").val("").first().focus();
        });
        $("#<?php echo $this->getName(); ?>_btn_cancelar").click(function() {
            $("#<?php echo $this->getName(); ?>_input_edicao .alert").hide();
            $(".input_select_<?php echo $this->getName(); ?>").show();
            $("#<?php echo $this->getName(); ?>_input_edicao").hide();
            $("#<?php echo $this->getName(); ?>").focus();
        });
        $("#<?php echo $this->getName(); ?>_input_edicao .control-label").css("width", "150px");
        $("#<?php echo $this->getName(); ?>_input_edicao .controls").css("margin-left", "160px");
	});
    
    function reload_crud_<?php echo $this->getName(); ?>(default_id) {
        $("#<?php echo $this->getName(); ?>_input_edicao .alert").hide;
        var ctrl = $("#<?php echo $this->getName(); ?>");
		ctrl.children().remove();
		$("<option value=''>==> SELECIONE <==</option>").appendTo(ctrl);
        ajax = $.ajax({
            "url" : "<?php echo $view->baseUrl(); ?>/fabricante/listar/format/json/",
            "type" : "POST",
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
                $(".input_select_<?php echo $this->getName(); ?>").show();
                $("#<?php echo $this->getName(); ?>_input_edicao").hide();
            }
        });			
    }
</script>
        <div id="linha_<?php echo $this->getName(); ?>" class="control-group">
				<label for="<?php echo $this->getName(); ?>" class="control-label"><?php echo $this->getLabel(); ?></label>
                <div class="controls">
                    <div class="input-append input_select_<?php echo $this->getName(); ?>">
                        <select name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" class="input-xxlarge">
<?php
        if (count($options)) {
            foreach ($options as $k => $v) {
            $select = "";
                if ($this->getValue() == $k) {
                    $select = " selected ";
                }
?>
                            <option value="<?php echo $k; ?>" <?php echo $select; ?>><?php echo $v; ?></option>
<?php }} ?>
                        </select>
                        <a href="#" id="link_janela_crud_<?php echo $this->getName(); ?>" class="add-on">
                            <i class="icon-plus-sign"></i>
                        </a>
                    </div>
                    <div class="well well-small hide" id="<?php echo $this->getName(); ?>_input_edicao">
                            <legend>Adicionar Fabricante</legend>
                            <div class="alert">
                                <span class="<?php echo $this->getName(); ?>_mensagem_erro"></span>
                            </div>                            
                            <div class="control-group">
                                <label for="<?php echo $this->getName(); ?>_descricao" class="control-label">Descrição:</label>
                                <div class="controls">
                                    <input type="text" name="<?php echo $this->getName(); ?>_descricao" id="<?php echo $this->getName(); ?>_descricao" class="span7" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label for=""></label>
                                <div class="controls">
                                    <input type="button" value="Salvar" class="btn btn-primary" id="<?php echo $this->getName(); ?>_btn_salvar" />
                                    <input type="button" value="Cancelar" class="btn btn-danger" id="<?php echo $this->getName(); ?>_btn_cancelar" />
                                </div>
                            </div>
                    </div>
                </div>
			</div>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}    
}