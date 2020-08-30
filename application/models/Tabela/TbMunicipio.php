<?php 
class TbMunicipio extends Escola_Tabela {
	protected $_name = "municipio";
	protected $_rowClass = "Municipio";
	protected $_dependentTables = array("TbPessoaFisica", "TbBairro");
	protected $_referenceMap = array("Uf" => array("columns" => array("id_uf"),
												   "refTableClass" => "TbUf",
												   "refColumns" => array("id_uf")));
	
	public function listar($dados) {
		$where = array();
		if (isset($dados["dinamic_id_uf"]) && $dados["dinamic_id_uf"] && ($dados["dinamic_id_uf"] != "null")) {
			$where[] = " id_uf = " . $dados["dinamic_id_uf"];
		} elseif (isset($dados["id_uf"]) && $dados["id_uf"] && ($dados["id_uf"] != "null")) {
			$where[] = " id_uf = " . $dados["id_uf"];
		}
		if (isset($dados["descricao"]) && $dados["descricao"] && ($dados["descricao"] != "null")) {
			$dados["descricao"] = utf8_decode($dados["descricao"]); 
			$where[] = " descricao like '%" . $dados["descricao"] . "%' ";
		}
		$select = $this->select();
		if (count($where)) {
			$select->where(implode(" and ", $where));
		}
		$select->order("descricao");
		$rgs = $this->fetchAll($select);
		if ($rgs->count()) {
			return $rgs;
		}
		return false;
	}
	
	public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
	
	public function recuperar() {
		$items = $this->fetchAll();
		if ($items && !count($items)) {
			$tb = new TbUf();
			$ufs = $tb->fetchAll("sigla = 'AP'");
			if ($ufs) {
				$uf = $ufs->current();
				$dados = array("descricao" => "MACAPÃ", "id_uf" => $uf->getId());
				foreach ($dados as $chave => $descricao) {
					$item = $this->createRow();
					$item->setFromArray($dados);
					$item->save();
				}
			}
		}
	}
}