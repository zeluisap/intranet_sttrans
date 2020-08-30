<?php
class TbTarifaOcorrencia extends Escola_Tabela {
    protected $_name = "tarifa_ocorrencia";
    protected $_rowClass = "TarifaOcorrencia";
    protected $_referenceMap = array("Tarifa" => array("columns" => array("id_tarifa"),
                                                            "refTableClass" => "TbTarifa",
                                                            "refColumns" => array("id_tarifa")),
                                        "Valor" => array("columns" => array("id_valor"),
                                                            "refTableClass" => "TbValor",
                                                            "refColumns" => array("id_valor")));
    protected $_dependentTables = array("TbRota","TbOnibusBdoTarifa");
}