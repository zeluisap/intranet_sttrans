<?php
class TbUsuarioSenha extends Escola_Tabela {
	protected $_name = "usuario_senha";
	protected $_rowClass = "UsuarioSenha";
	protected $_referenceMap = array("Usuario" => array("columns" => array("id_usuario"),
														"refTableClass" => "TbUsuario",
														"refColumns" => array("id_usuario")));
														
	public function listar($dados) {
		$where = array();
		if (isset($dados["id_usuario"]) && $dados["id_usuario"]) {
			$where[] = " (id_usuario = " . $dados["id_usuario"] . ") ";
		}
		if (isset($dados["hash"]) && $dados["hash"]) {
			$where[] = " (hash = '" . $dados["hash"] . "') ";
		}
		if (isset($dados["status"]) && $dados["status"]) {
			$where[] = " (status = '" . $dados["status"] . "') ";
		}
		$rg = $this->fetchAll(implode(" and ", $where));
		if (count($rg)) {
			return $rg;
		}
		return false;
	}	
}