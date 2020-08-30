<?php
class Linha extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
     
    public function setFromArray(array $dados) {
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" descricao = '{$this->descricao}' and id_linha <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "LINHA DE ÔNIBUS JÁ CADASTRADA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbRota");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
                return $msgs;
        }
        return false;        
    }
    
}