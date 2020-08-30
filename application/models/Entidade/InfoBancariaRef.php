<?php
class InfoBancariaRef extends Escola_Entidade {

    public function setFromArray(array $dados) {
		if (isset($dados["tipo"])) {
			$filter = new Zend_Filter_StringToUpper();
			$dados["tipo"] = $filter->filter(utf8_decode($dados["tipo"]));
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->tipo)) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("id_info_bancaria = '{$this->id_info_bancaria}' and tipo = '{$this->tipo}' and chave = '{$this->chave}' and id_info_bancaria_ref <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "REFERÊNCIA DE INFORMAÇÃO JÁ CADASTRADA!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function getObjeto() {
        if ($this->chave) {
            switch ($this->tipo) {
                case "P": $obj = TbPessoa::pegaPorId($this->chave);
                          if ($obj) {
                              return $obj;
                          }
                          break;
            }
        }
        return false;
    }
    
    public function pessoa() {
        return ($this->tipo == "P");
    }
}