<?php
class TbArquivo extends Escola_Tabela {
	protected $_name = "arquivo";
	protected $_rowClass = "Arquivo";
    protected $_dependentTables = array("TbVinculoLote", "TbVinculoLoteOcorrencia", "TbAutoInfracaoNotificacao");
	protected $_referenceMap = array("ArquivoTipo" => array("columns" => array("id_arquivo_tipo"),
												   "refTableClass" => "TbArquivoTipo",
												   "refColumns" => array("id_arquivo_tipo")));		
	
}