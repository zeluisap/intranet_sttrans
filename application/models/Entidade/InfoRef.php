<?php
class InfoRef extends Escola_Entidade {
    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_info) {
			$msgs[] = "CAMPO INFORMAÇÃO OBRIGATÓRIO!";
		}
		if ($this->getId()) {
			$tb = $this->getTable();
			$sql = $tb->select();
			$sql->where("id_info = " . $this->getId());
			$sql->where("tipo = '{$this->tipo}' ");
			$sql->where("chave = {$this->chave}");
			$sql->where("id_info_ref <> '" . $this->getId() . "'");
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
    
    public function toString() {
        return $this->titulo;
    }
	
	public function pegaObjeto() {
		if ($this->tipo == "A") {
			return TbArquivo::pegaPorId($this->chave);
		} elseif ($this->tipo == "I") {
			return TbInfo::pegaPorId($this->chave);
		}
		return false;
	}
}