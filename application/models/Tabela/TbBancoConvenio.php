<?php
class TbBancoConvenio extends Escola_Tabela {
	protected $_name = "banco_convenio";
	protected $_rowClass = "BancoConvenio";
    protected $_dependentTables = array("TbTransporteGrupo");
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["filtro_padrao"]) && $dados["filtro_padrao"]) {
            $sql->where("padrao = '{$dados["filtro_padrao"]}'");
        }
        $sql->order("descricao");
        $sql->order("convenio");
        return $sql;
    }
    
    public static function pegaPadrao() {
        $tb = new TbBancoConvenio();
        $stmt = $tb->listar(array("filtro_padrao" => "S"));
        if ($stmt && count($stmt)) {
            return $stmt->current();
        }
        return false;
    }
    
    public function pegaPorConvenio($convenio) {
		$stmt = $this->fetchAll(" convenio = '{$convenio}' ");
		if ($stmt && count($stmt)) {
			return $stmt->current();
		}
		return false;
    }
}