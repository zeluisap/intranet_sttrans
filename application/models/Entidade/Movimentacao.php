<?php
class Movimentacao extends Escola_Entidade {
	
    public function init() {
		if (!$this->getId()) {
			$this->data_movimentacao = date("Y-m-d");
			$this->hora_movimentacao = date("H:i:s");
            $this->id_movimentacao_recebe = 0;
		}
	}
	
    public function setFromArray(array $dados) {
		if (isset($dados["data_movimentacao"])) {
			$dados["data_movimentacao"] = Escola_Util::montaData($dados["data_movimentacao"]);
		}
        parent::setFromArray($dados);
    }
	    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_movimentacao_tipo) {
			$msgs[] = "CAMPO TIPO DE MOVIMENTAÇÃO OBRIGATÓRIO!";
		}
		if (!$this->id_documento) {
			$msgs[] = "CAMPO DOCUMENTO OBRIGATÓRIO!";
		}
		if (!$this->id_funcionario) {
			$msgs[] = "CAMPO FUNCIONÁRIO OBRIGATÓRIO!";
		}
		if (!$this->id_setor) {
			$msgs[] = "CAMPO SETOR OBRIGATÓRIO!";
		}
        $mt = $this->findParentRow("TbMovimentacaoTipo");
        if ($mt && $mt->envio()) {
            if (!$this->despacho) {
                $msgs[] = "CAMPO DESPACHO OBRIGATÓRIO, PARA A MOVIMENTAÇÃO ENCAMINHAR!";
            }
            if (!$this->id_destino) {
                $msgs[] = "CAMPO DESTINO OBRIGATÓRIO, PARA A MOVIMENTAÇÃO ENCAMINHAR!";
            }
        }
        if ($mt && $mt->cancelar_arquivar()) {
            if (!$this->despacho) {
                $msgs[] = "CAMPO MOTIVO DO CANCELAMENTO É OBRIGATÓRIO, PARA A MOVIMENTAÇÃO CANCELAR ARQUIVAMENTO!";
            }
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function destinoSetor() {
        return ($this->tipo_destino == "S");
    }
    
    public function destinoFuncionario() {
        return ($this->tipo_destino == "F");
    }
    
    public function mostrarStatus() {
        if ($this->findParentRow("TbMovimentacaoTipo")->envio()) {
            if ($this->id_movimentacao_recebe) {
                return "RECEBIDO";
            }
            return "PENDENTE";
        }
        return "";
    }
    
    public function pendente() {
        if ($this->mostrarStatus() == "PENDENTE") {
            return true;
        }
        return false;
    }
    
    public function pegaObjDestino() {
        $obj = false;
        switch ($this->tipo_destino) {
            case "F": $obj = TbFuncionario::pegaPorId($this->id_destino); break;
            case "S": $obj = TbSetor::pegaPorId($this->id_destino); break;
        }
        return $obj;
    }
    
    public function mostrarDestino() {
        $obj = $this->pegaObjDestino();
        if ($obj) {
            return $obj->toString();
        }
        return "";
    }
}