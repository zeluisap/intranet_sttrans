<?php
class ServicoTerceiro extends Escola_Entidade {
    
    public function toString() {
        $item = array();
        $pf = $this->findParentRow("TbPessoa");
        if ($pf) {
            $item[] = $pf->toString();
        }
        $item[] = $this->servico_realizado;
        return implode(" - ", $item);
    }
    
    public function getErrors() {
		$msgs = array();
        if (!trim($this->id_pessoa)) {
			$msgs[] = "CAMPO BENEFICIÁRIO OBRIGATÓRIO!";
		}
        if (!trim($this->servico_realizado)) {
			$msgs[] = "CAMPO SERVIÇO REALIZADO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function toHTML(Zend_View_Abstract $view) {
        ob_start();
        $pessoa = $this->findParentRow("TbPessoa");
        if ($pessoa) {
?>
<dl class="dl-horizontal">
    <dt>Beneficiário:</dt>
    <dd><?php echo $pessoa->toString(); ?></dd>
</dl>
<?php } ?>
<dl class="dl-horizontal">
    <dt>Serviço Realizado:</dt>
    <dd><?php echo $this->servico_realizado; ?></dd>
</dl>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}