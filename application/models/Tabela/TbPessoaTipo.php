<?php 
class TbPessoaTipo extends Escola_Tabela {
	protected $_name = "pessoa_tipo";
	protected $_rowClass = "PessoaTipo";
	protected $_dependentTables = array("TbPessoa");
	
	public function init() {
		$pts = $this->fetchAll();
		if (!$pts->count()) {	
			$pt = $this->createRow();
			$pt->setFromArray(array("chave" => "PF",
									"descricao" => "PESSOA FÍSICA"));
			$pt->save();
			$pt = $this->createRow();
			$pt->setFromArray(array("chave" => "PJ",
									"descricao" => "PESSOA JURÍDICA"));
			$pt->save();
		}
	}

	public function getPorChave($chave) {
		$pts = $this->fetchAll("chave = '$chave'");
		if ($pts->count()) {
			return $pts->current();
		}
		return false;
	}
	
}