<?php
class RotaViagem extends Escola_Entidade {
	
    public function getErrors() {
        $msgs = array();
        if (!$this->id_rota) {
            $msgs[] = "CAMPO ROTA OBRIGATÓRIO!";
        }
        if (!$this->dia_semana) {
            $msgs[] = "CAMPO DIA DA SEMANA OBRIGATÓRIO!";
        }
        if (!$this->hora_saida) {
            $msgs[] = "CAMPO HORA SAÍDA OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaHora($this->hora_saida)) {
            $msgs[] = "CAMPO HORA SAÍDA INVÁLIDO!";
        }
        if (!count($msgs)) {
            $tb = new TbRotaViagem();
            $sql = $tb->select();
            $sql->where("id_rota = {$this->id_rota}");
            $sql->where("dia_semana = {$this->dia_semana}");
            $sql->where("hora_saida = '{$this->hora_saida}'");
            $sql->where("id_rota_viagem <> {$this->getId()}");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                $msgs[] = "JÁ EXISTE UM REGISTRO CADASTRADO COM ESSAS INFORMAÇÕES!";
            }
        }
        if (count($msgs)) {
                return $msgs;
        }
        return false;
    }
}