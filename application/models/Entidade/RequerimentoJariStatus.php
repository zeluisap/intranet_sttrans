<?php
class RequerimentoJariStatus extends Escola_Entidade {
    
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
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_requerimento_jari_status <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "STATUS DE REQUERIMENTO JARI JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbRequerimentoJari");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function aguardando_resposta() {
        return ($this->chave == "AR");
    }
    
    public function deferimento_total() {
        return ($this->chave == "DT");
    }
    
    public function deferimento_parcial() {
        return ($this->chave == "DP");
    }
    
    public function indeferido() {
        return ($this->chave == "IN");
    }
}