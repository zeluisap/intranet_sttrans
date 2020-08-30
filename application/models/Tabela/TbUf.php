<?php
class TbUf extends Escola_Tabela {
	protected $_name = "uf";
	protected $_rowClass = "Uf";
	protected $_dependentTables = array("TbMunicipio");
	protected $_referenceMap = array("Pais" => array("columns" => array("id_pais"),
												     "refTableClass" => "TbPais",
												     "refColumns" => array("id_pais")));
	public function listar($dados = array()) {
		$where = array();
		if (isset($dados["id_pais"]) && $dados["id_pais"]) {
			$where[] = " id_pais = " . $dados["id_pais"];
		}
		if (isset($dados["descricao"]) && $dados["descricao"]) {
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
	
	public function getPorSigla($sigla) {
		if ($sigla) {
			$ufs = $this->fetchAll(" sigla = '{$sigla}' ");
			if ($ufs->count()) {
				return $ufs->current();
			}
		}
		return false;
	}
	
	public function getPorDescricao($descricao) {
		if ($descricao) {
			$ufs = $this->fetchAll(" descricao = '{$descricao}' ");
			if ($ufs->count()) {
				return $ufs->current();
			}
		}
		return false;
	}
	
	public function recuperar() {
		$ufs = $this->listar();
		if (!$ufs) {
			$dados = array("AC" => "ACRE",
						   "AL" => "ALAGOAS",
						   "AM" => "AMAZONAS",
						   "AP" => "AMAPÃ",
						   "BA" => "BAHIA",
						   "CE" => "CEARÃ",
						   "DF" => "DISTRITO FEDERAL",
						   "ES" => "ESPÃRITO SANTO",
						   "GO" => "GOIÃS",
						   "MA" => "MARANHÃO",
						   "MG" => "MINAS GERAIS",
						   "MS" => "MATO GROSSO DO SUL",
						   "MT" => "MATO GROSSO",
						   "PA" => "PARÃ",
						   "PB" => "PARAÃBA",
						   "PE" => "PERNAMBUCO",
						   "PI" => "PIAUÃ",
						   "PR" => "PARANÃ",
						   "RJ" => "RIO DE JANEIRO",
						   "RN" => "RIO GRANDE DO NORTE",
						   "RO" => "RONDÃNIA",
						   "RR" => "RORAIMA",
						   "RS" => "RIO GRANDE DO SUL",
						   "SC" => "SANTA CATARINA",
						   "SE" => "SERGIPE",
						   "SP" => "SÃO PAULO",
						   "TO" => "TOCANTINS");
			$tb_paises = new TbPais();
			$pais = $tb_paises->getPorDescricao("BRASIL");
			if ($pais) {
				foreach ($dados as $sigla => $descricao) {
					$uf = $this->createRow();
					$uf->setFromArray(array("sigla" => $sigla, "descricao" => utf8_decode($descricao), "id_pais" => $pais->getId()));
					$uf->save();
				}
			}
		}
	}
}