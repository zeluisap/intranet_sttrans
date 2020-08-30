<?php
class Escola_Controller_Logado extends Escola_Controller_Default {
	
	public function preDispatch() {
		$user = $this->_helper->acl->logado();
		if (!$user) {
			$this->_redirect("auth/login");
		}
		$tb = new TbPacote();
		$pacote = $tb->pegaAtual();
		if (!$pacote) {
			$pacotes = $tb->buscarPacotes($user);
			if ($pacotes) {
				if (count($pacotes) > 1) {
					$this->_redirect("auth/pacote");
				} elseif (count($pacotes) == 1) {
					$pacote = $pacotes[0];
					$session = Escola_Session::getInstance();
					$session->default_id_pacote = $pacote->getId();
				}
			} else {
				$this->_flashMessage("Falha ao Executar o Login, Nenhum Pacote disponível para o usuário!");
				$this->_redirect("intranet/logout");
			} 
		}
		$grupos = $user->pegaTbGrupo();
		$allowed = false;
		if ($grupos) {
			$tb = new TbModulo();
			$modulo = $tb->getPorController($this->view->originalController);
			if ($modulo) {
				$tb = new TbAcao();
				$acao = $tb->getPorAction($modulo, $this->view->originalAction);
				if ($acao) {
					$acl = Escola_Acl::getInstance();
					foreach ($grupos as $grupo) {
						if ($acl->isAllowed($grupo->id_grupo, $modulo->id_modulo, $acao->id_acao)) {
							$allowed = true;
						}
					}
					if (!$allowed) {
						throw new Exception("USUÁRIO NÃO POSSUI PERMISSÃO PARA EXECUTAR ESTA TAREFA, CHAME O ADMINISTRADOR!");
					}
				}
			}
		}
		parent::preDispatch();
	}
}