<?php
class TbTelefoneTipo extends Escola_Tabela {
	protected $_name = "telefone_tipo";
	protected $_rowClass = "TelefoneTipo";
	protected $_dependentTables = array("TbTelefone");
	
	public function init() {
		$tts = $this->fetchAll();
		if (!$tts->count()) {
			$tt = $this->createRow();
			$tt->setFromArray(array("chave" => "f",
									"descricao" => "fixo"));
			$tt->save();
			$tt = $this->createRow();
			$tt->setFromArray(array("chave" => "c",
									"descricao" => "celular"));
			$tt->save();
		}
	}
	
	public function getPorChave($chave) {
		$tts = $this->fetchAll(" chave = '{$chave}' ");
		if (count($tts)) {
			return $tts->current();
		}
		return false;
	}
}