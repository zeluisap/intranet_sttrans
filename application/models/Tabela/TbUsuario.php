<?php
class TbUsuario extends Escola_Tabela {
	protected $_name = "usuario";
	protected $_rowClass = "Usuario";
	protected $_dependentTables = array("TbUsuarioSenha", "TbUsuario", "TbTransporteVeiculoBaixa", "TbCredencialOcorrencia", "TbUsuario");
	protected $_referenceMap = array("UsuarioSituacao" => array("columns" => array("id_usuario_situacao"),
															    "refTableClass" => "TbUsuarioSituacao",
															    "refColumns" => array("id_usuario_situacao")),
									 "PessoaFisica" => array("columns" => array("id_pessoa_fisica"),
															 "refTableClass" => "TbPessoaFisica",
															 "refColumns" => array("id_pessoa_fisica")));
	
	public function getPorCPF($cpf) {
		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_Digits());
		$cpf = $filter->filter($cpf);
		$tb_pf = new TbPessoaFisica();
		$pessoas = $tb_pf->fetchAll("cpf = '{$cpf}'");
		if ($pessoas->count()) {
			$tb_usuario = new TbUsuario();
			$usuarios = $tb_usuario->fetchAll("id_pessoa_fisica = " . $pessoas->current()->id_pessoa_fisica);
			if ($usuarios->count()) {
				return $usuarios->current();
			}
		}
		return false;
	}
	
	public function createRow() {
		$row = parent::createRow();
		$tb = new TbUsuarioSituacao();
		$us = $tb->getPorChave("A");
		if ($us) {
			$row->id_usuario_situacao = $us->id_usuario_situacao;
		}
		return $row;
	}
	
	public Static function getPorPessoaFisica($pf) {
		$classe = __CLASS__;
		$tb = new $classe;
		if ($pf && $pf->getId()) {
			$rg = $tb->fetchAll("id_pessoa_fisica = " . $pf->getId());
			if ($rg && count($rg)) {
				return $rg;
			}
		}
		return false;
	}
	
	public function listar($dados = array()) {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("u" => "usuario"), array("id_usuario"));
		$sql->join(array("p" => "pessoa_fisica"), "u.id_pessoa_fisica = p.id_pessoa_fisica", array());
        $sql->join(array("us" => "usuario_situacao"), "u.id_usuario_situacao = us.id_usuario_situacao", array());
		if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
			$filter = new Zend_Filter_Digits();
			$dados["filtro_cpf"] = $filter->filter($dados["filtro_cpf"]);
			if ($dados["filtro_cpf"]) {
				$sql->where("p.cpf = '" . $dados["filtro_cpf"] . "'");
			}
		}
		if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
			$sql->where("p.nome like '%" . $dados["filtro_nome"] . "%'");
		}
		if (isset($dados["filtro_situacao"]) && $dados["filtro_situacao"]) {
            $sql->where("us.chave = '{$dados["filtro_situacao"]}'");
		}
		$sql->order("p.nome");
		// $adapter = new Zend_Paginator_Adapter_DbTableSelect($sql);
		$adapter = new Zend_Paginator_Adapter_DbSelect($sql);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
        $qtd_por_pagina = 50;
		if (isset($dados["qtd_por_pagina"]) && $dados["qtd_por_pagina"]) {
			$qtd_por_pagina = $dados["qtd_por_pagina"];
		}
		$paginator->setItemCountPerPage($qtd_por_pagina);
		return $paginator;
	}
	
	public static function pegaLogado() {
		return Escola_Acl::getInstance()->getUsuarioLogado();
	}
}