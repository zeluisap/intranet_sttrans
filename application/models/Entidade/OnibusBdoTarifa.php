<?php
class OnibusBdoTarifa extends Escola_Entidade {
	
    public function getErrors() {
        $msgs = array();
        if (!$this->id_onibus_bdo) {
            $msgs[] = "CAMPO ONIBUS BDO OBRIGATÓRIO!";
        }
        if (!$this->id_tarifa_tipo) {
            $msgs[] = "CAMPO TIPO DE TARIFA OBRIGATÓRIO!";
        }
        if (!is_numeric($this->passageiros)) {
            $msgs[] = "CAMPO PASSAGEIROS PRECISA SER NUMÉRICO!";
        } else {
            $this->passageiros = (int)$this->passageiros;
        }
        if (!$this->passageiros) {
            $msgs[] = "CAMPO PASSAGEIROS OBRIGATÓRIO!";
        } 
        if (!count($msgs)) {
            $tb = new TbOnibusBdoTarifa();
            $sql = $tb->select();
            $sql->where("id_onibus_bdo = {$this->id_onibus_bdo}");
            $sql->where("id_tarifa_tipo = {$this->id_tarifa_tipo}");
            $sql->where("id_onibus_bdo_tarifa <> '{$this->id_onibus_bdo_tarifa}'");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                $msgs[] = "INFORMAÇÃO DE PASSAGEIROS NO BDO JÁ CADASTRADA!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
}