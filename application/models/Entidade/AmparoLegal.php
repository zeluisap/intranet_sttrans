<?php
class AmparoLegal extends Escola_Entidade {
    
    public function setFromArray(array $data) {
        if (isset($data["descricao"])) {
            $data["descricao"] = Escola_Util::maiuscula($data["descricao"]);
        }
        parent::setFromArray($data);
    }
    
	public function toString() {
		return $this->descricao;
	}
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" descricao = '{$this->descricao}' and id_amparo_legal <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "AMPARO LEGAL JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $ais = $this->findDependentRowset("TbAmparoLegalItem");
        if ($ais && count($ais)) {
            $msgs[] = "Existem Itens vinculados a este Registro!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
}