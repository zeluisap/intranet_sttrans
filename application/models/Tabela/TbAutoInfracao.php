<?php
class TbAutoInfracao extends Escola_Tabela {
	protected $_name = "auto_infracao";
	protected $_rowClass = "AutoInfracao";
	protected $_referenceMap = array("AutoInfracaoStatus" => array("columns" => array("id_auto_infracao_status"),
												   "refTableClass" => "TbAutoInfracaoStatus",
												   "refColumns" => array("id_auto_infracao_status")),
                                     "AutoInfracaoDevolucaoStatus" => array("columns" => array("id_auto_infracao_devolucao_status"),
												   "refTableClass" => "TbAutoInfracaoDevolucaoStatus",
												   "refColumns" => array("id_auto_infracao_devolucao_status")),
                                     "Agente" => array("columns" => array("id_agente"),
												   "refTableClass" => "TbAgente",
												   "refColumns" => array("id_agente")),
                                     "ServicoTipo" => array("columns" => array("id_servico_tipo"),
												   "refTableClass" => "TbServicoTipo",
												   "refColumns" => array("id_servico_tipo")));
	protected $_dependentTables = array("TbAutoInfracao");
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_auto_infracao_devolucao_status"]) && $dados["id_auto_infracao_devolucao_status"]) {
            $sql->where("id_auto_infracao_devolucao_status = {$dados["id_auto_infracao_devolucao_status"]}");
        }
        if (isset($dados["id_auto_infracao_status"]) && $dados["id_auto_infracao_status"]) {
            $sql->where("id_auto_infracao_status = {$dados["id_auto_infracao_status"]}");
        }
        if (isset($dados["filtro_id_auto_infracao_status"]) && $dados["filtro_id_auto_infracao_status"]) {
            $sql->where("id_auto_infracao_status = {$dados["filtro_id_auto_infracao_status"]}");
        }        
        if (isset($dados["id_agente"]) && $dados["id_agente"]) {
            $sql->where("id_agente = {$dados["id_agente"]}");
        }
        if (isset($dados["filtro_id_agente"]) && $dados["filtro_id_agente"]) {
            $sql->where("id_agente = {$dados["filtro_id_agente"]}");
        }
        if (isset($dados["codigo"]) && $dados["codigo"]) {
            $sql->where("codigo = {$dados["codigo"]}");
        }
        if (isset($dados["filtro_caracter"]) && $dados["filtro_caracter"]) {
            $sql->where("alfa = '{$dados["filtro_caracter"]}'");
        }
        if (isset($dados["filtro_codigo_inicio"]) && $dados["filtro_codigo_inicio"]) {
            $sql->where("codigo >= {$dados["filtro_codigo_inicio"]}");
        }
        if (isset($dados["filtro_codigo_final"]) && $dados["filtro_codigo_final"]) {
            $sql->where("codigo <= {$dados["filtro_codigo_final"]}");
        }
        if (isset($dados["filtro_id_servico_tipo"]) && $dados["filtro_id_servico_tipo"]) {
            $sql->where("id_servico_tipo = {$dados["filtro_id_servico_tipo"]}");
        }
        if (isset($dados["id_servico_tipo"]) && $dados["id_servico_tipo"]) {
            $sql->where("id_servico_tipo = {$dados["id_servico_tipo"]}");
        }
        $sql->order("alfa"); 
		$sql->order("codigo"); 
        $sql->order("id_agente"); 
        return $sql;
    }
	
	public function getPorCodigo($codigo) {
		$uss = $this->fetchAll(" codigo = {$codigo} ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}
}