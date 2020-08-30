<?php
class ComentarioController extends Escola_Controller_Logado {

	public function indexAction() {
		$session = Escola_Session::getInstance();		
		$page = $this->_getParam("page");
		$filtros = array("filtro_id_comentario_status", "filtro_nome");
		$dados = $session->atualizaFiltros($filtros);
		//$dados = $this->_request->getParams();
		$dados["pagina_atual"] = $page;
		if (!isset($dados["filtro_id_comentario_status"]) || !$dados["filtro_id_comentario_status"]) {
			$tb = new TbComentarioStatus();
			$cs = $tb->getPorChave("A");
			if ($cs) {
				$dados["filtro_id_comentario_status"] = $cs->getId();
			}
		}
		$this->view->dados = $dados;
		$tb = new TbComentario();
		$this->view->registros = $tb->listarPorPagina($dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("COMENTÁRIOS");
		$button->addFromArray(array("titulo" => "Pesquisar",
									"onclick" => "pesquisar()",
									"img" => "icon-search",
									"params" => array("id" => 0)));
		$button->addFromArray(array("titulo" => "Voltar",
									"controller" => "intranet",
									"action" => "index",
									"img" => "icon-reply",
									"params" => array("id" => 0)));
	}
	
	public function permitirAction() {
		$id = $this->_getParam("id");
		$registro = TbComentario::pegaPorId($id);
		if ($registro) {
			$registro->permitir();
			if ($registro->permitido()) {
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
			}
		} else {
			$this->_flashMessages("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
		}
		$this->_redirect("comentario/index");
	}
	
	public function negarAction() {
		$id = $this->_getParam("id");
		$registro = TbComentario::pegaPorId($id);
		if ($registro) {
			$registro->negar();
			if ($registro->negado()) {
				$this->_flashMessage("OPERAÇÃO EFETUADA COM SUCESSO!", "Messages");
			}
		} else {
			$this->_flashMessages("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
		}
		$this->_redirect("comentario/index");
	}
}