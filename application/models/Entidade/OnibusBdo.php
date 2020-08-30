<?php
class OnibusBdo extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data_bdo = date("Y-m-d");
        }
    }
	
    public function setFromArray(array $dados) {
        if (isset($dados["data_bdo"])) {
            $dados["data_bdo"] = Escola_Util::montaData($dados["data_bdo"]);
        }
        if (isset($dados["km_inicial"])) {
            $dados["km_inicial"] = Escola_Util::montaNumero($dados["km_inicial"]);
        }
        if (isset($dados["km_final"])) {
            $dados["km_final"] = Escola_Util::montaNumero($dados["km_final"]);
        }
        parent::setFromArray($dados);
    }
	    
    public function getErrors() {
        $msgs = array();
        if ($this->bdo && !is_numeric($this->bdo)) {
            $msgs[] = "CAMPO BDO PRECISA SER NUMÉRICO!";
        }
        if (!$this->data_bdo) {
            $msgs[] = "CAMPO DATA OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaData($this->data_bdo)) {
            $msgs[] = "CAMPO DATA INVÁLIDO!";
        }
        if (!$this->id_rota) {
            $msgs[] = "CAMPO ROTA OBRIGATÓRIO!";
        }
        if (!$this->id_transporte_veiculo) {
            $msgs[] = "CAMPO TRANSPORTE VEICULO OBRIGATÓRIO!";
        }
        if (!$this->id_tarifa_ocorrencia) {
            $msgs[] = "CAMPO TARIFA OBRIGATÓRIO!";
        }
        if (!$this->hora_saida) {
            $msgs[] = "CAMPO HORA SAÍDA OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaHora($this->hora_saida)) {
            $msgs[] = "CAMPO HORA SAÍDA INVÁLIDO!";
        }
        if (!$this->hora_chegada) {
            $msgs[] = "CAMPO HORA CHEGADA OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaHora($this->hora_chegada)) {
            $msgs[] = "CAMPO HORA CHEGADA INVÁLIDO!";
        }
        if (!is_numeric($this->km_inicial)) {
            $msgs[] = "CAMPO KM INICIAL PRECISA SER NUMÉRICO!";
        } else {
            $this->km_inicial = (float)$this->km_inicial;
        }
        if (!$this->km_inicial) {
            $msgs[] = "CAMPO KM INICIAL OBRIGATÓRIO!";
        } 
        if (!is_numeric($this->km_final)) {
            $msgs[] = "CAMPO KM FINAL PRECISA SER NUMÉRICO!";
        } else {
            $this->km_final = (float)$this->km_final;
        }
        if (!$this->km_final) {
            $msgs[] = "CAMPO KM FINAL OBRIGATÓRIO!";
        }
        
        if (!is_numeric($this->viagens)) {
            $msgs[] = "CAMPO VIAGENS PRECISA SER NUMÉRICO!";
        } else {
            $this->viagens = (int)$this->viagens;
        }
        if (!$this->viagens) {
            $msgs[] = "CAMPO VIAGENS OBRIGATÓRIO!";
        } 
        if (!count($msgs)) {
            if ($this->km_inicial > $this->km_final) {
                $msgs[] = "QUILOMETRAGEM INICIAL NÃO PODE SER MAIOR QUE A FINAL!";
            }
        }
        if (!count($msgs)) {
            if ($this->bdo) {
                $tb = new TbOnibusBdo();
                $sql = $tb->select();
                $sql->where("bdo = {$this->bdo}");
                $sql->where("id_onibus_bdo <> {$this->getId()}");
                $rs = $tb->fetchAll($sql);
                if ($rs && count($rs)) {
                    $msgs[] = "EXISTE UM OUTRO REGISTRO CADASTRADO COM ESTE BDO!";
                }
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function save($flag = false) {
        if (!$this->bdo) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("onibus_bdo"), array("maximo" => "max(bdo)"));
            $stmt = $db->query($sql);
            if ($stmt && $stmt->rowCount()) {
                $obj = $stmt->fetchObject();
                $this->bdo = $obj->maximo + 1;
            } else {
                $this->bdo = 1;
            }
        }
        return parent::save($flag);
    }
    
    public function delete() {
        if ($this->getId()) {
            $tb = new TbOnibusBdoTarifa();
            $rs = $tb->listar(array("id_onibus_bdo" => $this->getId()));
            if ($rs && count($rs)) {
                foreach ($rs as $obj) {
                    $obj->delete();
                }
            }
        }
        return parent::delete();
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
}