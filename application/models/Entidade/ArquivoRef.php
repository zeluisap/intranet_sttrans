<?php
class ArquivoRef extends Escola_Entidade
{

    public function getErrors()
    {
        $msgs = array();
        if (!$this->id_arquivo) {
            $msgs[] = "CAMPO ARQUIVO OBRIGATÓRIO!";
        }
        if ($this->getId()) {
            $tb = $this->getTable();
            $sql = $tb->select();
            $sql->where("id_arquivo = " . $this->getId());
            $sql->where("tipo = '{$this->tipo}' ");
            $sql->where("chave = {$this->chave}");
            $sql->where("id_arquivo_ref <> '" . $this->getId() . "'");
            $rg = $tb->fetchAll($sql);
            if ($rg && count($rg)) {
                $msgs[] = "REFERÊNCIA JÁ CADASTRADA!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function pegaObjeto()
    {

        if ($this->tipo == "F") {
            return TbFuncionario::pegaPorId($this->chave);
		}
		
        if ($this->tipo == "I") {
            return TbInfo::pegaPorId($this->chave);
		}
		
        return false;
    }
}
