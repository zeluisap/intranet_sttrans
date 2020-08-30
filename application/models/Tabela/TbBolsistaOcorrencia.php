<?php
class TbBolsistaOcorrencia extends Escola_Tabela {
	protected $_name = "bolsista_ocorrencia";
	protected $_rowClass = "BolsistaOcorrencia";
	protected $_referenceMap = array("VinculoLoteItem" => array("columns" => array("id_vinculo_lote_item"),
												   "refTableClass" => "TbVinculoLoteItem",
												   "refColumns" => array("id_vinculo_lote_item")),
                                     "Usuario" => array("columns" => array("id_usuario"),
												   "refTableClass" => "TbUsuario",
												   "refColumns" => array("id_usuario")),
                                     "Bolsista" => array("columns" => array("id_bolsista"),
												   "refTableClass" => "TbBolsista",
												   "refColumns" => array("id_bolsista")));
	public function getSql($dados = array()) {
        $select = $this->select();
        if (isset($dados["id_bolsista"]) && $dados["id_bolsista"]) {
            $select->where("id_bolsista = {$dados["id_bolsista"]}");
        }
        if (isset($dados["id_vinculo_lote_item"]) && $dados["id_vinculo_lote_item"]) {
            $select->where("id_vinculo_lote_item = {$dados["id_vinculo_lote_item"]}");
        }
		$select->order("data");
        $select->order("id_bolsista_ocorrencia"); 
        return $select;
    }	
}