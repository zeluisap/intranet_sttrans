<?php
class TbInfracao extends Escola_Tabela {
	protected $_name = "infracao";
	protected $_rowClass = "Infracao";
	protected $_referenceMap = array("AmparoLegal" => array("columns" => array("id_amparo_legal"),
                                                            "refTableClass" => "TbAmparoLegal",
                                                            "refColumns" => array("id_amparo_legal")),
                                     "Moeda" => array("columns" => array("id_moeda"),
                                                            "refTableClass" => "TbMoeda",
                                                            "refColumns" => array("id_moeda")),
                                     "Valor" => array("columns" => array("id_valor"),
                                                            "refTableClass" => "TbValor",
                                                            "refColumns" => array("id_valor")));
	protected $_dependentTables = array("TbAutoInfracaoNotificacao");
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_amparo_legal"]) && $dados["id_amparo_legal"]) {
            $sql->where("id_amparo_legal = {$dados["id_amparo_legal"]}");
        }
        if (isset($dados["descricao"]) && $dados["descricao"]) {
            $sql->where("descricao like '%{$dados["descricao"]}%'");
        }
        $sql->order("descricao"); 
        return $sql;
    }
    
    public function getPorCodigo($codigo) {
        $codigo = Escola_Util::maiuscula($codigo);
		$uss = $this->fetchAll(" codigo = '{$codigo}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
}