<?php
class Passagem extends Escola_Entidade {
    
    public function toString() {
        $item = array();
        $pf = $this->findParentRow("TbPessoaJuridica");
        if ($pf) {
            $item[] = $pf->toString();
        }
        $item[] = Escola_Util::formatData($this->data_ida);
        if ($this->data_volta) {
            $item[] = Escola_Util::formatData($this->data_volta);
        }
        $item[] = $this->trecho;
        return implode(" - ", $item);
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["data_ida"])) {
            $dados["data_ida"] = Escola_Util::montaData($dados["data_ida"]);
        }
        if (isset($dados["data_volta"])) {
            $dados["data_volta"] = Escola_Util::montaData($dados["data_volta"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
        if (!trim($this->id_pessoa_juridica)) {
			$msgs[] = "CAMPO BENEFICIÁRIO OBRIGATÓRIO!";
		}
		if (!Escola_Util::validaData($this->data_ida)) {
			$msgs[] = "CAMPO DATA DE IDA OBRIGATÓRIO!";
		}
		if ((int)Escola_Util::limpaNumero($this->data_volta) && !Escola_Util::validaData($this->data_volta)) {
			$msgs[] = "CAMPO DATA DE VOLTA INVÁLIDO!";
		}
        if (Escola_Util::validaData($this->data_ida) && Escola_Util::validaData($this->data_volta)) {
            $data_ida = new Zend_Date($this->data_ida);
            $data_volta = new Zend_Date($this->data_volta);
            if ($data_ida->isLater($data_volta)) {
                $msgs[] = "DATA DE IDA DEVE SER ANTERIOR OU IGUAL A DATA DE VOLTA!";
            }
        }
        if (!trim($this->trecho)) {
			$msgs[] = "CAMPO TRECHO OBRIGATÓRIO!";
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
    <dt>Trecho:</dt>
    <dd><?php echo $this->trecho; ?></dd>
</dl>
<dl class="dl-horizontal">
    <dt>Data Ida:</dt>
    <dd><?php echo Escola_Util::formatData($this->data_ida); ?></dd>
</dl>
<dl class="dl-horizontal">
    <dt>Data Volta:</dt>
    <dd><?php echo Escola_Util::formatData($this->data_volta); ?></dd>
</dl>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}