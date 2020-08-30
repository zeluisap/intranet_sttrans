<?php
class RotaParada extends Escola_Entidade {
	
    public function getErrors() {
        $msgs = array();
        if (!$this->id_rota) {
            $msgs[] = "CAMPO ROTA OBRIGATÓRIO!";  
        }
        if (!$this->id_onibus_parada) {
            $msgs[] = "CAMPO PARADA OBRIGATÓRIO!";
        }
        if (!count($msgs)) {
            $tb = new TbRotaParada();
            $sql = $tb->select();
            $sql->where("id_rota = {$this->id_rota}");
            $sql->where("id_onibus_parada = {$this->id_onibus_parada}");
            $sql->where("id_rota_parada <> {$this->getId()}");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                $msgs[] = "PONTO DE ÔNIBUS JÁ VINCULADO A ESTA ROTA!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function save($flag = false) {
        if ($this->ordem) {
            $tb = $this->getTable();
            $sql = $tb->select();
            $sql->where("id_rota = {$this->id_rota}");
            $sql->where("ordem = {$this->ordem}");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                foreach ($rs as $obj) {
                    $obj->ordem = $obj->ordem + 1;
                    $obj->save();
                }
            }
        } else {
            $this->ordem = TbRotaParada::pega_ultima_ordem($this->findParentRow("TbRota")) + 1;
        }
        return parent::save($flag);
    }
}