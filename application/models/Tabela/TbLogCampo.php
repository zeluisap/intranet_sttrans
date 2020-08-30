<?php
class TbLogCampo extends Escola_Tabela_Log {
	protected $_name = "log_campo";
	protected $_rowClass = "LogCampo";
	protected $_referenceMap = array("Log" => array("columns" => array("id_log"),
															 "refTableClass" => "TbLog",
															 "refColumns" => array("id_log")));
}