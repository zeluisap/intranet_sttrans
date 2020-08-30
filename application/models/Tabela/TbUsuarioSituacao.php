<?php
class TbUsuarioSituacao extends Escola_Tabela {
	protected $_name = "usuario_situacao";
	protected $_rowClass = "UsuarioSituacao";
	protected $_dependentTables = array("TbUsuario");
	
	public function init() {
		$usuarios = $this->fetchAll();
		if (!$usuarios->count()) {
			$usuario = $this->createRow();
			$usuario->setFromArray(array("chave" => "a",
										 "descricao" => "ativo"));
			$usuario->save();
			$usuario = $this->createRow();
			$usuario->setFromArray(array("chave" => "i",
										 "descricao" => "inativo"));
			$usuario->save();
		}
	}
	
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
			$dados = array("A" => "ATIVO",
						   "I" => "INATIVO");
			foreach ($dados as $sigla => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $sigla, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}