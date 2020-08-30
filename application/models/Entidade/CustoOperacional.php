<?php
class CustoOperacional extends Escola_Entidade {
    
    public function toString() {
        $item = array();
        $pf = $this->findParentRow("TbPessoaJuridica");
        if ($pf) {
            $item[] = $pf->toString();
        }
        $item[] = $this->descricao;
        return implode(" - ", $item);
    }
    
    public function getErrors() {
		$msgs = array();
        if (!trim($this->id_pessoa_juridica)) {
			$msgs[] = "CAMPO BENEFICIÁRIO OBRIGATÓRIO!";
		}
        if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO DO CUSTO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function toHTML(Zend_View_Abstract $view) {
        ob_start();
        $pj = $this->findParentRow("TbPessoaJuridica");
        if ($pj) {
?>
<dl class="dl-horizontal">
    <dt>Beneficiário:</dt>
    <dd><?php echo $pj->toString(); ?></dd>
</dl>
<?php } ?>
<dl class="dl-horizontal">
    <dt>Descrição do Custo:</dt>
    <dd><?php echo $this->descricao; ?></dd>
</dl>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}