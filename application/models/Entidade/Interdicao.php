<?php
class Interdicao extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->isento = "N";
        }
    }
    
    public function setFromArray(array $data) {
        if (isset($data["titulo"])) {
            $data["titulo"] = Escola_Util::maiuscula($data["titulo"]);
        }
        parent::setFromArray($data);
    }
    
    public function pegaSolicitacao() {
        if ($this->getId()) {
            $tb = new TbServicoSolicitacao();
            $sss = $tb->listar(array("tipo" => "IN", "chave" => $this->getId()));
            if ($sss && count($sss)) {
                return $sss->current();
            }
        }
        return false;
    }
    
    public function getErrors() {
		$msgs = array();
        if (!trim($this->titulo)) {
			$msgs[] = "CAMPO TÍTULO OBRIGATÓRIO!";
		}        
        if (!trim($this->id_pessoa)) {
			$msgs[] = "CAMPO PESSOA OBRIGATÓRIO!";
		}
        if (!trim($this->informacoes)) {
			$msgs[] = "CAMPO INFORMAÇÕES OBRIGATÓRIO!";
		}
        if ($this->isento() && !trim($this->isento_motivo)) {
            $msgs[] = "CAMPO MOTIVO DA ISENÇÃO OBRIGATÓRIO PARA AUTORIZAÇÃO DE INTERDIÇÃO ISENTA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function pago() {
        $ss = $this->pegaSolicitacao();
        if ($ss) {
            return $ss->pago();
        }
        return false;
    }
        
    public function getDeleteErrors() {
        $msgs = array();
        $ss = $this->pegaSolicitacao();
        if ($ss && $ss->pago()) {
            $msgs[] = "Falha ao Executar Operação, Solicitação de Interdição Já paga!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function delete() {
        $ss = $this->pegaSolicitacao();
        if ($ss && !$ss->pago()) {
            $ss->delete();
            parent::delete();
        }
    }
    
    public function toString() {
        return $this->titulo;
    }
    
    public function isento() {
        return ($this->isento == "S");
    }
    
    public function mostrarIsento() {
        if ($this->isento()) {
            return "SIM";
        }
        return "NÃO";
    }
}