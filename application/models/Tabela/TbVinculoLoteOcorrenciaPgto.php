<?php
class TbVinculoLoteOcorrenciaPgto extends Escola_Tabela {
	protected $_name = "vinculo_lote_ocorrencia_pgto";
	protected $_rowClass = "VinculoLoteOcorrenciaPgto";
	protected $_referenceMap = array("VinculoLoteOcorrencia" => array("columns" => array("id_vinculo_lote_ocorrencia"),
                                                                          "refTableClass" => "TbVinculoLoteOcorrencia",
                                                                          "refColumns" => array("id_vinculo_lote_ocorrencia")),
                                     "FormaPagamento" => array("columns" => array("id_forma_pagamento"),
                                                                                  "refTableClass" => "TbFormaPagamento",
                                                                                  "refColumns" => array("id_forma_pagamento")),
                                     "DocComprovacao" => array("columns" => array("id_doc_comprovacao"),
                                                                                  "refTableClass" => "TbDocComprovacao",
                                                                                  "refColumns" => array("id_doc_comprovacao")),
                                     "PrevisaoTipo" => array("columns" => array("id_previsao_tipo"),
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
                                                              "refColumns" => array("id_vinculo_lote")),
                                    "Valor" => array("columns" => array("id_valor"),
                                                              "refTableClass" => "TbValor",
                                                              "refColumns" => array("id_valor")),
                                    "VinculoMovimento" => array("columns" => array("id_vinculo_movimento"),
                                                              "refTableClass" => "TbVinculoMovimento",
                                                              "refColumns" => array("id_vinculo_movimento")));	
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->from(array("vlop" => "vinculo_lote_ocorrencia_pgto"));
        if (isset($dados["id_vinculo_lote"]) && $dados["id_vinculo_lote"]) {
            $sql->where("vlop.id_vinculo_lote = {$dados["id_vinculo_lote"]}");
        }
        if (isset($dados["id_vinculo_lote_ocorrencia"]) && $dados["id_vinculo_lote_ocorrencia"]) {
            $sql->where("vlop.id_vinculo_lote_ocorrencia = {$dados["id_vinculo_lote_ocorrencia"]}");
        }
        if (isset($dados["id_previsao_tipo"]) && $dados["id_previsao_tipo"]) {
            $sql->where("vlop.id_previsao_tipo = {$dados["id_previsao_tipo"]}");
        }
        if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
            $sql->where("vlop.id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
        }
        $sql->order("vlop.id_previsao_tipo");
        $sql->order("vlop.id_bolsa_tipo");
        
        return $sql;
    }
}