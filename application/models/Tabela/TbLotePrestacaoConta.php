<?php
class TbLotePrestacaoConta extends Escola_Tabela {
	protected $_name = "lote_prestacao_conta";
	protected $_rowClass = "LotePrestacaoConta";
	protected $_referenceMap = array("PrevisaoTipo" => array("columns" => array("id_previsao_tipo"),
                                                                                "refTableClass" => "TbPrevisaoTipo",
                                                                                "refColumns" => array("id_previsao_tipo")),
                                     "BolsaTipo" => array("columns" => array("id_bolsa_tipo"),
                                                                             "refTableClass" => "TbBolsaTipo",
                                                                             "refColumns" => array("id_bolsa_tipo")),
                                     "Arquivo" => array("columns" => array("id_arquivo"),
                                                                           "refTableClass" => "TbArquivo",
                                                                           "refColumns" => array("id_arquivo")),
                                    "VinculoLote" => array("columns" => array("id_vinculo_lote"),
                                                              "refTableClass" => "TbVinculoLote",
                                                              "refColumns" => array("id_vinculo_lote")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("lpc" => "lote_prestacao_conta"));
        if (isset($dados["id_vinculo_lote"]) && $dados["id_vinculo_lote"]) {
            $sql->where("lpc.id_vinculo_lote = {$dados["id_vinculo_lote"]}");
        }
        if (isset($dados["id_previsao_tipo"]) && $dados["id_previsao_tipo"]) {
            $sql->where("lpc.id_previsao_tipo = {$dados["id_previsao_tipo"]}");
        }
        if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
            $sql->where("lpc.id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
        }
        $sql->order("lpc.id_previsao_tipo");
        $sql->order("lpc.id_bolsa_tipo");
        
        return $sql;
    }
}