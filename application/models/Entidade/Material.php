<?php
class Material extends Escola_Entidade {
    
    protected $_valor = false;
    
    public function pega_valor() {
        if ($this->_valor) {
            return $this->_valor;
        }
        $valor = $this->findParentRow("TbValor");
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }
    
    public function init() {
        parent::init();
        $this->_valor = $this->pega_valor();
    }
    
    public function toString() {
        $item = array();
        $mti = $this->findParentRow("TbMaterialTipoItem");
        if ($mti) {
            $item[] = $mti->toString();
        }
        $pf = $this->findParentRow("TbPessoaJuridica");
        if ($pf) {
            $item[] = $pf->toString();
        }
        $item[] = $this->descricao;
        return implode(" - ", $item);
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["valor_unitario"]) && $dados["valor_unitario"]) {
            $this->_valor->setFromArray(array("valor" => $dados["valor_unitario"]));
        }
        if (isset($dados["quantidade"])) {
            $dados["quantidade"] = Escola_Util::montaNumero($dados["quantidade"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->id_material_tipo_item)) {
			$msgs[] = "CAMPO TIPO DE MATERIAL OBRIGATÓRIO!";
		}
        if (!trim($this->id_material_unidade_tipo)) {
			$msgs[] = "CAMPO TIPO DE UNIDADE OBRIGATÓRIO!";
		}
        if (!trim($this->id_pessoa_juridica)) {
			$msgs[] = "CAMPO BENEFICIÁRIO OBRIGATÓRIO!";
		}
        if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        if (!trim($this->quantidade)) {
			$msgs[] = "CAMPO QUANTIDADE OBRIGATÓRIO!";
		}
        if (!(float)$this->_valor->valor) {
			$msgs[] = "CAMPO VALOR UNITÁRIO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function save($flag = false) {
        $this->id_valor_unitario = $this->_valor->save();
        parent::save($flag);
    }
    
    public function toHTML(Zend_View_Abstract $view) {
        ob_start();
        $tipo = $this->findParentRow("TbMaterialTipoItem");
        if ($tipo) {
?>
<dl class="dl-horizontal">
    <dt>Tipo:</dt>
    <dd><?php echo $tipo->toString(); ?></dd>
</dl>
<?php } ?>
<?php
        $unidade_tipo = $this->findParentRow("TbMaterialUnidadeTipo");
        if ($unidade_tipo) {
?>
<dl class="dl-horizontal">
    <dt>Unidade:</dt>
    <dd><?php echo $unidade_tipo->toString(); ?></dd>
</dl>
<?php } ?>
<?php
        $pj = $this->findParentRow("TbPessoaJuridica");
        if ($pj) {
?>
<dl class="dl-horizontal">
    <dt>Beneficiário:</dt>
    <dd><?php echo $pj->toString(); ?></dd>
</dl>
<?php } ?>
<dl class="dl-horizontal">
    <dt>Descrição:</dt>
    <dd><?php echo $this->descricao; ?></dd>
</dl>
<dl class="dl-horizontal">
    <dt>Quantidade:</dt>
    <dd><?php echo Escola_Util::number_format($this->quantidade); ?></dd>
</dl>
<dl class="dl-horizontal">
    <dt>Valor Unitário:</dt>
    <dd><?php echo $this->_valor->toString(); ?></dd>
</dl>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}