<?php
class Agente extends Escola_Entidade {
	public function toString() {
		$txt = array();
        if ($this->codigo) {
            $txt[] = $this->codigo;
        }
        $funcionario = $this->findParentRow("TbFuncionario");
        if ($funcionario) {
            $txt[] = $funcionario->pega_pessoa_fisica()->nome;
            $txt[] = $funcionario->findParentRow("TbCargo")->toString();
        }
        if (count($txt)) {
            return implode(" - ", $txt);
        }
        return "";
	}
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->codigo)) {
			$msgs[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
		}
		if (!trim($this->id_funcionario)) {
			$msgs[] = "NENHUM FUNCIONÁRIO VINCULADO!";
		}
        $rg = $this->getTable()->fetchAll(" codigo = '{$this->codigo}' and id_agente <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "CÓDIGO DE AGENTE JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbAutoInfracao");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
}