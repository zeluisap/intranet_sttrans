<?php
class PessoaMotorista extends Escola_Entidade {
	
	public function getPessoaFisica() {
		$pf = $this->findParentRow("TbPessoaFisica");
		if (!$pf) {
			return null;
		}
		return $pf;
	}
    
    public function setFromArray($dados = array()) {
        if (isset($dados["cnh_validade"])) {
            $dados["cnh_validade"] = Escola_Util::montaData($dados["cnh_validade"]);
        }
        if (isset($dados["cnh_primeira_habilitacao"])) {
            $dados["cnh_primeira_habilitacao"] = Escola_Util::montaData($dados["cnh_primeira_habilitacao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
        if (!trim($this->id_pessoa_fisica)) {
			$msgs[] = "CAMPO PESSOA FÍSICA OBRIGATÓRIO!";
		}        
		if (!trim($this->cnh_numero)) {
			$msgs[] = "CAMPO NÚMERO DA CNH OBRIGATÓRIO!";
		}
        if (!trim($this->id_cnh_categoria)) {
			$msgs[] = "CAMPO CATEGORIA DA CNH OBRIGATÓRIO!";
		}        
        if (!trim($this->id_uf)) {
            $msgs[] = "CAMPO UF OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" cnh_numero = '{$this->cnh_numero}' and id_pessoa_fisica = {$this->id_pessoa_fisica} and  id_pessoa_motorista <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "MOTORISTA JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTransporteVeiculo");
        if ($registros) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function render(Zend_View_Abstract $view) {
        ob_start();
?>
<fieldset id="pessoa_motorista">
    <legend>INFORMAÇÕES DO MOTORISTA</legend>
    <div class="control-group">
        <label for="cnh_numero" class="control-label">CNH - Número:</label>
        <div class="controls">
            <input type="text" name="pessoa_motorista[cnh_numero]" id="cnh_numero" class="span2" value="<?php echo $this->cnh_numero; ?>" />
        </div>
    </div>
    <div class="control-group">
        <label for="cnh_registro" class="control-label">CNH - Registro:</label>
        <div class="controls">
            <input type="text" name="pessoa_motorista[cnh_registro]" id="cnh_registro" class="span2" value="<?php echo $this->cnh_registro; ?>" />
        </div>
    </div>
<?php
$tb = new TbCnhCategoria();
$items = $tb->listar();
if ($items) {
?>
    <div class="control-group">
        <label for="id_cnh_categoria" class="control-label">CNH - Categoria:</label>
        <div class="controls">
            <select name="pessoa_motorista[id_cnh_categoria]" id="id_cnh_categoria">
                <option value="" <?php echo (!$this->id_cnh_categoria)?"selected":""; ?>>==> SELECIONE <==</option>
<?php foreach ($items as $item) { ?>
                <option value="<?php echo $item->getId(); ?>" <?php echo ($this->id_cnh_categoria == $item->getId())?"selected":""; ?>><?php echo $item->codigo; ?> - <?php echo $item->descricao; ?></option>
<?php } ?>
            </select>
        </div>
    </div>
<?php } ?>
    <div class="control-group">
        <label for="cnh_validade" class="control-label">CNH - Validade:</label>
        <div class="controls">
            <input type="text" name="pessoa_motorista[cnh_validade]" id="cnh_validade" class="span2 data" value="<?php echo Escola_Util::formatData($this->cnh_validade); ?>" />
        </div>
    </div>
    <div class="control-group">
        <label for="cnh_primeira_habilitacao" class="control-label">Primeira Habilitação:</label>
        <div class="controls">
            <input type="text" name="pessoa_motorista[cnh_primeira_habilitacao]" id="cnh_primeira_habilitacao" class="span2 data" value="<?php echo Escola_Util::formatData($this->cnh_primeira_habilitacao); ?>" />
        </div>
    </div>
<?php
$tb = new TbUf();
$items = $tb->listar();
if ($items) {
?>
    <div class="control-group">
        <label for="cnh_id_uf" class="control-label">CNH - Estado:</label>
        <div class="controls">
            <select name="pessoa_motorista[id_uf]" id="cnh_id_uf">
                <option value="" <?php echo (!$this->id_uf)?"selected":""; ?>>==> SELECIONE <==</option>
<?php foreach ($items as $item) { ?>
                <option value="<?php echo $item->getId(); ?>" <?php echo ($this->id_uf == $item->getId())?"selected":""; ?>><?php echo $item->descricao; ?></option>
<?php } ?>
            </select>
        </div>
    </div>
<?php } ?>
</fieldset>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function toString() {
        $txt = array();
        $txt[] = $this->cnh_registro;
        $cnh_categoria = $this->findParentRow("TbCnhCategoria");
        if ($cnh_categoria) {
            $txt[] = "Categoria: {$cnh_categoria->codigo}";
        }
        $txt[] = "Validade: " . Escola_Util::formatData($this->cnh_validade);
        $uf = $this->findParentRow("TbUf");
        if ($uf) {
            $txt[] = $uf->sigla;
        }
        if (count($txt)) {
            return implode(" - ", $txt);
        }
        return "";
    }
    
    public function vencida() {
        $hoje = new Zend_Date();
        $validade = new Zend_Date($this->cnh_validade);
        return ($hoje->isLater($validade));
    }
}