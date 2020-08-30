<?php
class Escola_View_Helper_GetHeaderGrupo {
	function getHeaderGrupo($chave = null) {

		$session = Escola_Session::getInstance();
        $grupos = $session->header_grupo;
        
        if (Escola_Util::vazio($grupos)) {
            return null;
        }

        if (Escola_Util::vazio($chave)) {
            return $grupos;
        }

        if (!isset($grupos[$chave])) {
            return null;
        }

        return $grupos[$chave];
	}
}