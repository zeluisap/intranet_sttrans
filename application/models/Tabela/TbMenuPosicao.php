<?php
class TbMenuPosicao extends Escola_Tabela {
	protected $_name = "menu_posicao";
	protected $_rowClass = "MenuPosicao";
	protected $_dependentTables = array("TbMenu");
	
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
        $dados = array("S" => "SUPERIOR",
                       "L" => "LATERAL",
                       "R" => "RODAPÃ",
                       "E" => "CENTRAL");
        foreach ($dados as $chave => $descricao) {
            $obj = $this->getPorChave($chave);
            if (!$obj) {
                $item = $this->createRow();
                $item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
                $item->save();
            }
        }
	}
}