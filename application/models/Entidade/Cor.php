<?php
class Cor extends Escola_Entidade {
    
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
        $rg = $this->getTable()->fetchAll(" descricao = '{$this->descricao}' and id_cor <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "COR JÁ CADASTRADA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbVeiculo");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
}