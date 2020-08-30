<?php
class VinculoLoteItemStatus extends Escola_Entidade {
    
	public function toString() {
		return $this->descricao;
	}

	public function setFromArray(array $dados) {
        $filter = new Zend_Filter_StringToUpper();
		if (isset($dados["chave"])) {
			$dados["chave"] = $filter->filter(utf8_decode($dados["chave"]));
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = $filter->filter(utf8_decode($dados["descricao"]));
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_vinculo_lote_item_status <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "STATUS DO ÍTEM DO LOTE JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function problema() {
        return ($this->chave == "FL");
    }

    public function inapto() {
        return ($this->chave == "IN");
    }

    public function pendente() {
        return ($this->chave == "PP");
    }
    
    public function pagamento_pendente() {
        return $this->pendente();
    }
    
    public function pagamento_confirmado() {
        return ($this->chave == "PG");
    }
    
    public function toStringFmt() {
        $class = "label-info";
        if ($this->problema() || $this->inapto()) {
            $class = "label-warning";
        } 
        return '<span class="label ' . $class . '">' . $this->toString() . '</span>';
    }
}