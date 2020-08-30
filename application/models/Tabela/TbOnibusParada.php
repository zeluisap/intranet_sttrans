<?php
class TbOnibusParada extends Escola_Tabela {
        protected $_name = "onibus_parada";
        protected $_rowClass = "OnibusParada";
        protected $_referenceMap = array("OnibusParadaTipo" => array("columns" => array("id_onibus_parada_tipo"),
                                            "refTableClass" => "TbOnibusParadaTipo",
                                            "refColumns" => array("id_onibus_parada_tipo")));
        protected $_dependentTables = array("TbRotaParada");
}