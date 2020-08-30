<?php
class TbComentarioStatus extends Escola_Tabela {
	protected $_name = "comentario_status";
	protected $_rowClass = "ComentarioStatus";
	protected $_dependentTables = array("TbComentario");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function listar() {
		$sql = $this->select();
		$sql->order("descricao");
		$rg = $this->fetchAll($sql);
		if (count($rg)) {
			return $rg;
		}
		return false;
	}
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("A" => "AGUARDANDO CONFIRMAÃÃO",
						   "P" => "PERMITIDO",
						   "N" => "NEGADO");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}