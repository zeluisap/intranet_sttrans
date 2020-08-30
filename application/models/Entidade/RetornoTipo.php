<?php
class RetornoTipo extends Escola_Entidade {
    
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
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_retorno_tipo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TIPO DE ARQUIVO DE RETORNO JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbRetorno");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function pegaObjeto() {
        $class_name = "Escola_Retorno_" . $this->chave;
        try {
            $obj = new $class_name;
            return $obj;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function processar_arquivo($arquivo) {
        $objeto = $this->pegaObjeto();
        if ($objeto) {
            $retorno = $objeto->processar_arquivo($arquivo);
            return $retorno;
        }
    }
}