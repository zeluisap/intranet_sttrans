<?php
class TbVinculoMovimento extends Escola_Tabela {
	protected $_name = "vinculo_movimento";
	protected $_rowClass = "VinculoMovimento";
        protected $_dependentTables = array("TbVinculoLoteOcorrenciaPgto");
	protected $_referenceMap = array("FormaPagamento" => array("columns" => array("id_forma_pagamento"),
												   "refTableClass" => "TbFormaPagamento",
												   "refColumns" => array("id_forma_pagamento")),
                                     "VinculoMovimentoTipo" => array("columns" => array("id_vinculo_movimento_tipo"),
												   "refTableClass" => "TbVinculoMovimentoTipo",
												   "refColumns" => array("id_vinculo_movimento_tipo")),
                                     "Valor" => array("columns" => array("id_valor"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")),
                                     "ValorAnterior" => array("columns" => array("id_valor_anterior"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")),
                                     "DespesaTipo" => array("columns" => array("id_despesa_tipo"),
												   "refTableClass" => "TbDespesaTipo",
												   "refColumns" => array("id_despesa_tipo")));
    
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("vm" => "vinculo_movimento"));
        if (isset($dados["filtro_id_info_bancaria"]) && $dados["filtro_id_info_bancaria"]) {
            $sql->join(array("ibr" => "info_bancaria_ref"), "vm.id_vinculo_movimento = ibr.chave", array());
            $sql->where("ibr.tipo = 'VM'");
            $sql->where("ibr.id_info_bancaria = '{$dados["filtro_id_info_bancaria"]}'");
        }
        if (isset($dados["filtro_data_inicio"]) && $dados["filtro_data_inicio"]) {
            $dados["filtro_data_inicio"] = Escola_Util::formatData($dados["filtro_data_inicio"]);
            $sql->where("vm.data_movimento >= '{$dados["filtro_data_inicio"]}'");
        }
        if (isset($dados["filtro_data_fim"]) && $dados["filtro_data_fim"]) {
            $dados["filtro_data_fim"] = Escola_Util::formatData($dados["filtro_data_fim"]);
            $sql->where("vm.data_movimento <= '{$dados["filtro_data_fim"]}'");
        }
        $sql->order("vm.data_movimento");
        $sql->order("vm.id_vinculo_movimento");
        return $sql;
    }
    
    public function getPorId($id) {
        try {
            $obj = parent::getPorId($id);
            if ($obj) {
                $vmt = $obj->findParentRow("TbVinculoMovimentoTipo");
                if ($vmt && $vmt->getId()) {
                    $class_name = "VinculoMovimento_" . $vmt->chave;
                    $dados = $obj->toArray();
                    $stored = true;
                    $obj = new $class_name(array("table" => $this, "data" => $dados, "stored" => $stored));
                    return $obj;
                }
                return $obj;
            }
        } catch (Exception $e) {
            die($e->getMessage());
            return false;
        }
        return false;        
    }
    
    public function createRowReceita() {
        try {
            $obj = parent::createRow();
            return new VinculoMovimento_RE(array("table" => $this, "data" => $obj->toArray(), "stored" => false));
        } catch (Exception $e) {
            die($e->getMessage());
            return false;
        }
        return $obj;
    }
    
    public function createRowDespesa() {
        try {
            $obj = parent::createRow();
            return new VinculoMovimento_DE(array("table" => $this, "data" => $obj->toArray(), "stored" => false));
        } catch (Exception $e) {
            die($e->getMessage());
            return false;
        }
        return $obj;
    }
}