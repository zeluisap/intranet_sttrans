<?php
class TbRota extends Escola_Tabela {
	protected $_name = "rota";
	protected $_rowClass = "Rota";
	protected $_referenceMap = array("Linha" => array("columns" => array("id_linha"),
                                                            "refTableClass" => "TbLinha",
                                                            "refColumns" => array("id_linha")),
                                         "RotaTipo" => array("columns" => array("id_rota_tipo"),
                                                            "refTableClass" => "TbRotaTipo",
                                                            "refColumns" => array("id_rota_tipo")),
                                        "Transporte" => array("columns" => array("id_transporte"),
                                                            "refTableClass" => "TbTransporte",
                                                            "refColumns" => array("id_transporte")),
                                        "TarifaOcorrencia" => array("columns" => array("id_tarifa_ocorrencia"),
                                                            "refTableClass" => "TbTarifaOcorrencia",
                                                            "refColumns" => array("id_tarifa_ocorrencia")));
        protected $_dependentTables = array("TbRotaParada", "TbOnibusBdo", "TbRotaDia");
}