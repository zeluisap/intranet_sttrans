<?php
class Diaria extends Escola_Entidade {
    
    public function toString() {
        $item = array();
        $pf = $this->findParentRow("TbPessoaFisica");
        if ($pf) {
            $item[] = $pf->toString();
        }
        $item[] = $this->motivo;
        return implode(" - ", $item);
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["quantidade"])) {
            $dados["quantidade"] = Escola_Util::montaNumero($dados["quantidade"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
        if (!trim($this->id_pessoa_fisica)) {
			$msgs[] = "CAMPO BENEFICIÁRIO OBRIGATÓRIO!";
		}
        if (!trim($this->destino)) {
			$msgs[] = "CAMPO DESTINO OBRIGATÓRIO!";
		}
        if (!trim($this->motivo)) {
			$msgs[] = "CAMPO MOTIVO OBRIGATÓRIO!";
		}
        if (!$this->quantidade) {
			$msgs[] = "CAMPO QUANTIDADE OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function toHTML(Zend_View_Abstract $view) {
        ob_start();
        $pf = $this->findParentRow("TbPessoaFisica");
        if ($pf) {
?>
<dl class="dl-horizontal">
    <dt>Beneficiário:</dt>
    <dd><?php echo $pf->toString(); ?></dd>
</dl>
<?php } ?>
<dl class="dl-horizontal">
    <dt>Destino:</dt>
    <dd><?php echo $this->destino; ?></dd>
</dl>
<dl class="dl-horizontal">
    <dt>Motivo:</dt>
    <dd><?php echo $this->motivo; ?></dd>
</dl>
<dl class="dl-horizontal">
    <dt>Quantidade:</dt>
    <dd><?php echo Escola_Util::number_format($this->quantidade); ?></dd>
</dl>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}