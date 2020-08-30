<?php
class TbBairro extends Escola_Tabela {
	protected $_name = "bairro";
	protected $_rowClass = "Bairro";
	protected $_dependentTables = array("TbEndereco");
	protected $_referenceMap = array("Uf" => array("columns" => array("id_municipio"),
												   "refTableClass" => "TbMunicipio",
												   "refColumns" => array("id_municipio")));	
						
    public function getSql($dados = array()) {
        $select = $this->select();
		if (isset($dados["id_municipio"]) && $dados["id_municipio"] && ($dados["id_municipio"] != "null")) {
			//$where[] = " id_municipio = " . $dados["id_municipio"];
			$select->where("id_municipio = ?", $dados["id_municipio"]);
		}
		if (isset($dados["descricao"]) && $dados["descricao"] && ($dados["descricao"] != "null")) {
			$dados["descricao"] = utf8_decode($dados["descricao"]);
			//$where[] = " descricao like '%" . $dados["descricao"] . "%' ";
			$select->where("descricao LIKE ?", "%" . $dados["descricao"] . "%");
		}
		$select->order("descricao"); 
        return $select;
    }
	
	public function recuperar() {
		$items = $this->fetchAll();
		if (!$items || !count($items)) {
			$tb_municipio = new TbMunicipio();
			$tb = new TbBairro();
			$municipio = $tb_municipio->getPorDescricao("MACAPÁ");
			if ($municipio) {
				$dados = array("descricao" => "CENTRAL", "id_municipio" => $municipio->getId());
				foreach ($dados as $chave => $descricao) {
					$item = $this->createRow();
					$item->setFromArray($dados);
					$item->save();
				}
			}
		}
	}
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
}