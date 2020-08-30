<?php
class TbMensagemTipo extends Escola_Tabela {
	protected $_name = "mensagem_tipo";
	protected $_rowClass = "MensagemTipo";
	protected $_dependentTables = array("Mensagem");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function recuperar() {
		$items = $this->listar();
		if (!$items || !count($items)) {
			$dados = array("T" => "TODOS",
						   "S" => "SETOR ATUAL E SUBORDINADO",
						   "A" => "SOMENTE SETOR ATUAL",
                           "P" => "PESSOAL");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}