<?php
class Periodicidade extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["chave"])) {
            $dados["chave"] = Escola_Util::maiuscula($dados["chave"]);
        }
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		if (!trim($this->qtd_meses)) {
			$msgs[] = "CAMPO QUANTIDADE DE MESES OBRIGATÓRIO!";
		}
		if (!is_numeric($this->qtd_meses)) {
			$msgs[] = "CAMPO QUANTIDADE DE MESES INVÁLIDO, SOMENTE NÚMEROS!";
		}
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_periodicidade <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "PERIODICIDADE JÁ CADASTRADA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbServicoTransporteGrupo");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function anual() {
        return ($this->chave == "A");
    }
    
    public function mensal() {
        return ($this->chave == "M");
    }
}