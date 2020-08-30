<?php
class TarifaOcorrencia extends Escola_Entidade {
	
    public function init() {
        if (!$this->getId()) {
            $this->data_inicio = date("Y-m-d");
        }
    }
    
    public function setFromArray(array $dados) {
//                if (isset($dados["valor"]) && $dados["valor"]) {
//                    $this->_valor->setFromArray(array("valor" => $dados["valor"]));
//                }
		if (isset($dados["data_inicio"])) {
			$dados["data_inicio"] = Escola_Util::montaData($dados["data_inicio"]);
		}
                if (isset($dados["data_final"])) {
			$dados["data_final"] = Escola_Util::montaData($dados["data_final"]);
		}
        parent::setFromArray($dados);
    }
        
    public function getErrors() {
        $msgs = array();
        if (!$this->id_tarifa) {
                $msgs[] = "CAMPO TARIFA OBRIGATÓRIO!";
        }
        if (!Escola_Util::validaData($this->data_inicio)) {
			$msgs[] = "CAMPO DATA INVÁLIDO!";
		}
        if (count($msgs)) {
                return $msgs;
        }
        return false;
    }
    
    public function toString() {
        $tarifa = $this->findParentRow("TbTarifa");
        if ($tarifa) {
            return $tarifa->toString();
        }
        return "";
    }
}