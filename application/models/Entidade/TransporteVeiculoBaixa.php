<?php
class TransporteVeiculoBaixa extends Escola_Entidade {
    
    public function setFromArray(array $dados) {
        if (isset($dados["baixa_data"])) {
            $dados["baixa_data"] = Escola_Util::montaData($dados["baixa_data"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
        $tv = $this->findParentRow("TbTransporteVeiculo");
        if ($tv && $tv->baixa()) {
            //$msgs[] = "VEÍCULO JÁ ESTÁ EM BAIXA!";
        }
        if (!trim($this->baixa_data)) {
			$msgs[] = "CAMPO DATA DA BAIXA OBRIGATÓRIO!";
		} elseif (!Escola_Util::validaData($this->baixa_data)) {
			$msgs[] = "CAMPO DATA DA BAIXA INVÁLIDO!";
		}
        if (!trim($this->id_transporte_veiculo)) {
			$msgs[] = "CAMPO VEÍCULO OBRIGATÓRIO!";
		}        
		if (!trim($this->id_usuario)) {
			$msgs[] = "CAMPO USUÁRIO OBRIGATÓRIO!";
		}
        if (!trim($this->id_baixa_motivo)) {
			$msgs[] = "CAMPO MOTIVO DA BAIXA OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function save($flag = false) {
        parent::save($flag);
        if ($this->getId()) {
            $tv = $this->findParentRow("TbTransporteVeiculo");
            if ($tv && !$tv->baixa()) {
                $tb = new TbTransporteVeiculoStatus();
                $tvs = $tb->getPorChave("B");
                if ($tvs) {
                    $tv->id_transporte_veiculo_status = $tvs->getId();
                    $tv->save();
                }
            }
        }
    }
}