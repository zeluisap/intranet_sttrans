<?php
class Rota extends Escola_Entidade {
	
    public function setFromArray(array $dados) {
        if (isset($dados["id_tarifa"]) && $dados["id_tarifa"]) {
            $tarifa = TbTarifa::pegaPorId($dados["id_tarifa"]);
            if ($tarifa) {
                $to = $tarifa->pega_ocorrencia_atual();
                if ($to) {
                    $dados["id_tarifa_ocorrencia"] = $to->getId();
                }
            }
        }
        if (isset($dados["km"])) {
            $dados["km"] = Escola_Util::montaNumero($dados["km"]);
        }
        parent::setFromArray($dados);
    }
	    
    public function getErrors() {
        $msgs = array();
        if (!$this->id_transporte) {
            $msgs[] = "CAMPO TRANSPORTE OBRIGATÓRIO!";
        }
        if (!$this->id_linha) {
            $msgs[] = "CAMPO LINHA OBRIGATÓRIO!";
        }
        if (!$this->id_rota_tipo) {
            $msgs[] = "CAMPO TIPO DE ROTA OBRIGATÓRIO!";
        }
        if (!$this->id_tarifa_ocorrencia) {
            $msgs[] = "CAMPO TARIFA OBRIGATÓRIO!";
        }
        if (!$this->km) {
            $msgs[] = "CAMPO KM OBRIGATÓRIO!";
        }
        if (!$this->tempo_total) {
            $msgs[] = "CAMPO TEMPO TOTAL OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }    
    
    public function delete() {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $registros = $this->findDependentRowset("TbRotaDia");
            if ($registros && count($registros)) {
                foreach ($registros as $to) {
                    $to->delete();
                }
            }
            $return = parent::delete();
            $db->commit();
            return $return;
        } catch (Exception $ex) {
            $db->rollBack();
        }
        return false;
    }
    
    public function pega_tarifa() {
        $to = $this->findParentRow("TbTarifaOcorrencia");
        if ($to) {
            $tarifa = $to->findParentRow("TbTarifa");
            if ($tarifa) {
                return $tarifa;
            }
        }
        return false;
    }
    
    public function toString() {
        $flag = array();
        $rt = $this->findParentRow("TbRotaTipo");
        if ($rt) {
            $flag[] = $rt->toString();
        }
        $linha = $this->findParentRow("TbLinha");
        if ($linha) {
            $flag[] = $linha->toString();
        }
        if (count($flag)) {
            return implode(" - ", $flag);
        }
        return "";
    }
}