<?php
class VinculoLoteStatus extends Escola_Entidade {
    
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
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_vinculo_lote_status <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "STATUS DO LOTE JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function aguardando_liberacao() {
        return ($this->chave == "AL");
    }
    
    public function aguardando_aprovacao() {
        return ($this->chave == "AG");
    }
    
    public function aprovado() {
        return ($this->chave == "AP");
    }
    
    public function nf() {
        return ($this->chave == "NF");
    }
    
    public function recurso() {
        return ($this->chave == "RC");
    }
    
    public function pago() {
        return ($this->chave == "PG");
    }
    
    public function aguardando_pc() {
        return ($this->chave == "APC");
    }
    
    public function pc() {
        return ($this->chave == "PCC");
    }
}