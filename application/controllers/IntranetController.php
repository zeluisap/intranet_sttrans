<?php
class IntranetController extends Escola_Controller_Logado {
	
	public function indexAction() {

		$this->removeGrupoValor();

		$usuario = Escola_Acl::getInstance()->getUsuarioLogado();
		$login = $usuario->ultimoLogin();
		$this->view->ultimo_login = false;
		if ($login) {
			$this->view->ultimo_login = $login;
		}
		$this->view->usuario = $usuario;
		$this->view->pf = $usuario->getPessoaFisica();
	}
	
	public function lotacaoAction() {
		$id = $this->_getParam("id");
		$lotacao = TbLotacao::pegaPorId($id);
		if ($lotacao) {
			$sessao = Escola_Session::getInstance();
			$sessao->set_lotacao_principal($lotacao);
		} else {
			$this->_flashMessages("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
		}
		$this->_redirect("intranet/index");
	}
	
	public function pacoteAction() {
		$usuario = TbUsuario::pegaLogado();
		if ($usuario) {
			
		}
	}

}