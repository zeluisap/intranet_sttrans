<?php
class Escola_Form_Element_Select_Dinamic extends Zend_Form_Element {
	protected $_flag; 
	protected $_url = "";
	protected $_baseUrl;
	protected $_dados = array();
	protected $_idParents = array();
	protected $_idValue = "";
	protected $_table = "";
	
	public function init() {
		$front = Zend_Controller_Front::getInstance();
		$this->setBaseUrl($front->getBaseUrl());
	}
	
	public function render(Zend_View_Interface $view = null) {
		$value = "";
		$id = "";
		if ($this->getValue()) {
			$value = $this->getValue();
			$id = "";
		} else {
			$table = $this->getTable();
			if ($table) {
				$tb = new $table;
				$registro = $tb->getPorId($this->getIdValue());
				if ($registro) {
					$id = $this->getIdValue();
					$value = $registro->toString();
				}
			}
		}
		ob_start();
		?>		
<script type="text/javascript">
$(document).ready(
	function() {
		var default_id = "<?php echo $id; ?>";
		var default_descricao = "<?php echo $value; ?>";
		var flag = false;
		if (obj_ajax == "undefined") {
			var obj_ajax = false;
		}
<?php  if (count($this->_idParents)) { ?>
		$("#<?php echo implode(", #", $this->_idParents); ?>").change(
			function() {
				obj_ajax = $.ajax({
					"url" : "<?php echo $this->_url; ?>",
					"type" : "POST",
					"data" : <?php echo $this->showDados(); ?>,
					"success" : function(obj_view) {
						flag = false;
						if (obj_view.result && obj_view.result.length) {
							for (var x = 0; x < obj_view.result.length; x++) {
								var obj = obj_view.result[x];
								if (obj.id == default_id) {
									flag = true;
									break;
								}
							}
						}
						if (!flag) {
							$("#<?php echo $this->getName(); ?>").val("").change();
							$("#<?php echo $this->getName(); ?>_desc").val("").change();
							$("#<?php echo $this->_flag; ?>").val("").change();
						}
					},
					"complete" : function() {
						$("#img-loading_<?php echo $this->_flag; ?>").html("");
					}
				});
			}
		);
<?php } ?>
		$("#<?php echo $this->_flag; ?>").keyup(
			function(evt) {
				if (evt.keyCode == 40 && $("#id_<?php echo $this->_flag; ?>").css("display") != "none") {
					$("#id_<?php echo $this->_flag; ?>").children().attr("selected", false);
					$("#id_<?php echo $this->_flag; ?>").children().first().attr("selected", true);
					$("#id_<?php echo $this->_flag; ?>").focus();
				} else {
					if (obj_ajax) {
						obj_ajax.abort();
					}
					var ctrl = $("#id_<?php echo $this->_flag; ?>");
					if ($("#<?php echo $this->getName(); ?>_desc").val() != $("#<?php echo $this->_flag; ?>").val()) {
						$("#<?php echo $this->getName(); ?>, #<?php echo $this->getName(); ?>_desc").val("");
					}
					ctrl.hide();
					ctrl.children().remove();
					if ($(this).val().length) {
						$("<img src='<?php echo $this->_baseUrl; ?>/img/ajax-loader.gif'>").appendTo($("#img-loading_<?php echo $this->_flag; ?>"));
						obj_ajax = $.ajax({
							"url" : "<?php echo $this->_url; ?>",
							"type" : "POST",
							"data" : <?php echo $this->showDados(); ?>,
							"success" : function(obj_view) {
								var flag = false;
								if (obj_view.result && obj_view.result.length) {
									for (var x = 0; x < obj_view.result.length; x++) {
										var obj = obj_view.result[x];
										if ($("#<?php echo $this->getName(); ?>").val() == obj.id) {
											flag = true;
										}
										$("<option value='" + obj.id + "'>" + obj.descricao + "</option>").appendTo(ctrl);
									}
									ctrl.children().first().attr("selected", true);
									ctrl.show();
								}
								if (!flag && $("#<?php echo $this->getName(); ?>").val().length) {
									$("#<?php echo $this->getName(); ?>, #<?php echo $this->getName(); ?>_desc").val("");
								}
							},
							"complete" : function() {
								$("#img-loading_<?php echo $this->_flag; ?>").html("");
							}
						});
					}
				}
			}
		);
		$("#id_<?php echo $this->_flag; ?>").keyup(
			function(evt) {
				if (evt.keyCode == 13) {
					var ops = $("#id_<?php echo $this->_flag; ?> option:selected");
					$("#<?php echo $this->_flag; ?>, #<?php echo $this->getName(); ?>_desc").val(ops.text());
					$("#id_<?php echo $this->_flag; ?>").hide();
					$("#<?php echo $this->_flag; ?>").focus();
					$("#<?php echo $this->getName(); ?>").val(ops.attr("value"));
				}
			}
		);
		$("#id_<?php echo $this->_flag; ?>").click(
			function() {
				var ops = $("#id_<?php echo $this->_flag; ?> option:selected");
				$("#<?php echo $this->_flag; ?>, #<?php echo $this->getName(); ?>_desc").val(ops.text());
				$("#id_<?php echo $this->_flag; ?>").hide();
				$("#<?php echo $this->_flag; ?>").focus();
				$("#<?php echo $this->getName(); ?>").val(ops.attr("value"));
			}
		);
		$("#<?php echo $this->_flag; ?>").focus(
			function() {
				var pos = $("#<?php echo $this->_flag; ?>").offset();
				$("#id_<?php echo $this->_flag; ?>").css({ "position" : "absolute",
															 "left" : pos.left,
															 "width" : $("#<?php echo $this->_flag; ?>").width() + 18,
															 "margin-top" : "-4px",
															 "height" : "200px",
															 "z-index": "11" }).hide();
			}
		);
        $("#<?php echo $this->getName(); ?>").change(function() {
            $("#show_<?php echo $this->getName(); ?>").text($(this).val());
        });
	}
)
</script>
		<div id="linha_<?php echo $this->_flag; ?>" class="control-group">
			<label for="municipio" class="control-label"><?php echo $this->getLabel(); ?></label>
            <div class="controls">
                <input type="hidden" name="<?php echo $this->getName(); ?>_desc" id="<?php echo $this->getName(); ?>_desc" value="<?php echo $value; ?>" />
                <input type="hidden" name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" size="1" readonly value="<?php echo $id; ?>" />
                <div class="input-prepend">
                    <div class="add-on">
                        <div class="show_<?php echo $this->getName(); ?>"><?php echo $id; ?></div>
                    </div>
                    <input type="text" name="<?php echo $this->_flag; ?>" id="<?php echo $this->_flag; ?>" size="60" autocomplete="off" value="<?php echo $value; ?>" />
                    <span id="img-loading_<?php echo $this->_flag; ?>" class="img-loading"></span>
                    <div id="cx_<?php echo $this->_flag; ?>">
                        <select name="id_<?php echo $this->_flag; ?>" id="id_<?php echo $this->_flag; ?>" multiple size="8" style="display:none">
                        </select>
                    </div>
                </div>
            </div>
		</div>
		<?php
		$ctrl = ob_get_contents();
		ob_end_clean();
		return $ctrl;
	}
	
	public function setFlag($flag) {
		$this->_flag = $flag;
	}
	
	public function setUrl($url) {
		$this->_url = $url;
	}
	
	public function setBaseUrl($url) {
		$this->_baseUrl = $url;
	}
	
	public function setDados($dados) {
		if (is_array($dados)) {
			$this->_dados = $dados;
		}
	}
	
	public function showDados() {
		$dados = array();
		if (count($this->_dados)) {
			foreach ($this->_dados as $k => $v) {
				$dados[] = " '{$k}' : $v ";
			}
		}
		return "{" . implode(", ", $dados) . "}";
	}
	
	public function setIdParents($array) {
		if (is_array($array)) {
			$this->_idParents = $array;
		}
	}
	
	public function setIdValue($id = null) {
		$this->_idValue = $id;
	}
	
	public function getIdValue() {
		return $this->_idValue;
	}
	
	public function setTable($table) {
		$this->_table = $table;
	}
	
	public function getTable() {
		return $this->_table;
	}
}