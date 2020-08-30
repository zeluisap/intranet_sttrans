<?php
class TbChamadoTipo extends Escola_Tabela {
	protected $_name = "chamado_tipo";
	protected $_rowClass = "ChamadoTipo";
	protected $_dependentTables = array("TbChamado");
	
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
			
	public function listarporpagina($dados = array()) {
		$select = $this->select();
		$select->order("descricao");
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage(50);
		return $paginator;
	}	
    
    public function pegaPorSetor($setor) {
  		$db = Zend_Registry::get("db");
   		$sql = $db->select();
   		$sql->from(array("cts" => "chamado_tipo_setor"), array("id_chamado_tipo"));
   		$sql->join(array("s" => "setor"), "cts.id_setor = s.id_setor", array());
   		if ($setor) {
   			$sql->where("cts.id_setor = " . $setor->getId());
   		}
   		$stmt = $db->query($sql);
   		if ($stmt && $stmt->rowCount()) {
   			$rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
   			$items = array();
   			foreach ($rg as $obj) {
   				$items[] = TbChamadoTipo::pegaPorId($obj->id_chamado_tipo);
   			}
   			return $items;
   		}
    	return false;
	}
}