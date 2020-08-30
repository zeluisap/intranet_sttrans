<?php
class ServicoReferencia extends Escola_Entidade {
    
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
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_servico_referencia <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "REFERÊNCIA DE SERVIÇO JÁ CADASTRADA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbServico");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function transporte() {
        return ($this->chave == "TR");
    }
    
    public function veiculo() {
        return ($this->chave == "TV");
    }
    
    public function pessoa() {
        return ($this->chave == "TP");
    }
    
    public function pegaReferencia($chave) {
        $tb = false;
        if ($this->transporte()) {
            $tb = new TbTransporte();
        } elseif ($this->veiculo()) {
            $tb = new TbTransporteVeiculo();
        } elseif ($this->pessoa()) {
            $tb = new TbTransportePessoa();
        }
        if ($tb) {
            $obj = $tb->getPorId($chave);
            if ($obj) {
                return $obj;
            }
        }
        return false;
    }
}