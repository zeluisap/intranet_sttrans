<?php
class TransportePessoaStatus extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["chave"])) {
            $dados["chave"] = Escola_Util::maiuscula($dados["chave"]);
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
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_transporte_pessoa_status <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "STATUS DE VÍNCULO DE TRANSPORTE E PESSOA JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTransportePessoa");
        if ($registros) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function ativo() {
        return ($this->chave == "A");
    }
}