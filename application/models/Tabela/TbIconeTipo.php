<?php
class TbIconeTipo extends Escola_Tabela {
	protected $_name = "icone_tipo";
	protected $_rowClass = "IconeTipo";
	protected $_dependentTables = array("TbIcone");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("descricao");
        return $sql;
    }

    public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("WEB" => "Aplicações Web",
						   "TEXT" => "Ícones de Edição de Texto",
							"DIRE" => "Ícones de Direção",
							"VIDE" => "Ícones de Vídeo",
							"SOCI" => "Ícones de Redes Sociais",
							"MED" => "Ícones Médicos");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}