<?php
class TbSmtp extends Escola_Tabela {
	protected $_name = "smtp";
	protected $_rowClass = "Smtp";

    public static function getSmtp() {
        $tb = new TbSmtp();
        $objs = $tb->fetchAll();
        if ($objs && count($objs)) {
            return $objs->current();
        }
        $obj = $tb->createRow();
        return $obj;
    }
    
    public static function getSecurityTypes() {
        return array("tls", "ssl");
    }
}