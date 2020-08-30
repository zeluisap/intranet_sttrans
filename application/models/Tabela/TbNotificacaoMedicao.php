<?php
class TbNotificacaoMedicao extends Escola_Tabela {
	protected $_name = "notificacao_medicao";
	protected $_rowClass = "NotificacaoMedicao";
	protected $_referenceMap = array("AutoInfracaoNotificacao" => array("columns" => array("id_auto_infracao_notificacao"),
												   "refTableClass" => "TbAutoInfracaoNotificacao",
												   "refColumns" => array("id_auto_infracao_notificacao")));
}