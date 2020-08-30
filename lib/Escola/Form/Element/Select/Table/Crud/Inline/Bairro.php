<?php
class Escola_Form_Element_Select_Table_Crud_Inline_Bairro extends Escola_Form_Element_Select_Table {
	
	protected $_id_municipio = "";
    
    public function init() {
        parent::init();
        $this->setPkName("id_bairro");
        $this->setModel("TbBairro");        
    }
		
	public function set_id_municipio($vinculo) {
		$this->_id_municipio = $vinculo;
	}
	
	public function pega_id_municipio() {
		return $this->_id_municipio;
	}
    
	public function render(Zend_View_Interface $view = null) {
		ob_start();
		$this->_carregaDados();
		$options = $this->getMultiOptions();
?>
<script type="text/javascript">
    var <?php echo $this->getName(); ?>_id_default = "<?php echo $this->getValue(); ?>";
	$(document).ready(function() {
        $("#link_salvar_<?php echo $this->getName(); ?>").click(function() {
            ajax = $.ajax({
                "url" : "<?php echo $view->baseUrl(); ?>/bairro/salvar/format/json/",
                "type" : "POST",
                "data" : { "descricao" : $("#input_add_<?php echo $this->getName(); ?>").val(), "id_municipio": $("#<?php echo $this->_id_municipio; ?>").val() },
                "success" : function(obj_view) {
                    if (obj_view.result) {
                        if (obj_view.result.mensagem) {
                            $(".<?php echo $this->getName(); ?>_mensagem_erro").html(obj_view.result.mensagem);
                            $("#linha_<?php echo $this->getName(); ?>").addClass("warning");
                        }
                        if (obj_view.result.id) {
                            reload_crud_<?php echo $this->getName(); ?>(obj_view.result.id, $("#<?php echo $this->_id_municipio; ?>").val());
                        }
                    }
                }
            });
			return false;
		});
        $("#<?php echo $this->_id_municipio; ?>").change(function() {
			$("#linha_<?php echo $this->getName(); ?>").hide();
			if ($(this).val().length) {
                reload_crud_<?php echo $this->getName(); ?>(<?php echo $this->getName(); ?>_id_default, $(this).val());
                $("#linha_<?php echo $this->getName(); ?>").show();
			}
		}).change();
        $("#link_janela_crud_<?php echo $this->getName(); ?>").click(function(event) {
            event.preventDefault();
            $(".<?php echo $this->getName(); ?>_mensagem_erro").html("");
            $("#linha_<?php echo $this->getName(); ?>").removeClass("warning");
            $(".input_select_<?php echo $this->getName(); ?>").hide();
            $(".input_edicao_<?php echo $this->getName(); ?>").show();
            $("#input_add_<?php echo $this->getName(); ?>").val("").focus();
        });
        $("#link_cancelar_<?php echo $this->getName(); ?>").click(function(event) {
            event.preventDefault();
            $(".<?php echo $this->getName(); ?>_mensagem_erro").html("");
            $("#linha_<?php echo $this->getName(); ?>").removeClass("warning");
            $(".input_select_<?php echo $this->getName(); ?>").show();
            $(".input_edicao_<?php echo $this->getName(); ?>").hide();
            $("#<?php echo $this->getName(); ?>").focus();
        });
        $("#input_add_<?php echo $this->getName(); ?>").keypress(function(event) {
            $(".<?php echo $this->getName(); ?>_mensagem_erro").html("");
            $("#linha_<?php echo $this->getName(); ?>").removeClass("warning");            
            if (event.which == 13) {
                event.preventDefault();
                $("#link_salvar_<?php echo $this->getName(); ?>").click();
            }
        });
	});
    
    function reload_crud_<?php echo $this->getName(); ?>(default_id, id_municipio) {
        $(".<?php echo $this->getName(); ?>_mensagem_erro").html("");
        $("#linha_<?php echo $this->getName(); ?>").removeClass("warning");    
        var ctrl = $("#<?php echo $this->getName(); ?>");
		ctrl.children().remove();
		$("<option value=''>==> SELECIONE <==</option>").appendTo(ctrl);
		if ($("#<?php echo $this->_id_municipio; ?>").val().length) {
			ajax = $.ajax({
				"url" : "<?php echo $view->baseUrl(); ?>/bairro/listar/format/json/",
				"type" : "POST",
				"data" : { "id_municipio" : id_municipio },
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
                    $(".input_edicao_<?php echo $this->getName(); ?>").hide();
				}
			});			
		} else {
			ctrl.change();
		}
    }
</script>
        <div id="linha_<?php echo $this->getName(); ?>" class="control-group">
				<label for="<?php echo $this->getName(); ?>" class="control-label"><?php echo $this->getLabel(); ?></label>
                <div class="controls">
                    <div class="input-append input_select_<?php echo $this->getName(); ?>">
                        <select name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" class="input-xlarge">
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
                    <div class="input-append input_edicao_<?php echo $this->getName(); ?>" style="display:none">
                        <input type="text" name="input_add_<?php echo $this->getName(); ?>" id="input_add_<?php echo $this->getName(); ?>" class="input-xlarge" />
                        <a href="#" id="link_salvar_<?php echo $this->getName(); ?>" class="add-on">
                            <i class="icon-save"></i>
                        </a>
                        <a href="#" id="link_cancelar_<?php echo $this->getName(); ?>" class="add-on">
                            <i class="icon-remove-circle"></i>
                        </a>
                    </div>
                    <span class="help-inline <?php echo $this->getName(); ?>_mensagem_erro"></span>
                </div>
			</div>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}    
}