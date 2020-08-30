<?php
class Escola_Form_Element_Select_Table extends Zend_Form_Element_Select {
	protected $_pk_name;
	protected $_model;
	
	public function setPkName($pk_name) {
		$this->_pk_name = $pk_name;
	}
	
	public function setModel($model) {
		$this->_model = $model;
	}
	
	public function render(Zend_View_Interface $view = null) {
		$class = $this->getAttrib("class");
		if ($class) {
			$class = " class = '{$class}' ";
		}
		ob_start();
		$this->_carregaDados();
		$options = $this->getMultiOptions();
		if (count($options)) {
?>
			<div class="linha_<?php echo $this->getName(); ?> control-group">
				<label for="<?php echo $this->getName(); ?>" class="control-label"><?php echo $this->getLabel(); ?></label>
				<div class="controls">
					<select name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" <?php echo $class; ?>>
	<?php 
			foreach ($options as $k => $v) { 
				$select = "";
				if ($this->getValue() == $k) {
					$select = " selected ";
				}
	?>
						<option value="<?php echo $k; ?>" <?php echo $select; ?>><?php echo $v; ?></option>
	<?php } ?>
					</select>
				</div>
			</div>
<?php
		}
		$res = ob_get_contents();
		ob_end_clean();
		return $res;
	}
	
	protected function _carregaDados() {
		$tb_pais = new TbPais();
		$pais = $tb_pais->getPorDescricao("BRASIL");
		if ($pais) {
			$model = new $this->_model;
			$dados = $model->listar(array("id_pais" => $pais->id_pais));
			if ($dados) {
				$options = array(null => "==> SELECIONE <==");
				foreach ($dados as $dado) {	
					$options[$dado->{$this->_pk_name}] = $dado->toString();
				}
				$this->setMultiOptions($options);
			}
		}
	}
}