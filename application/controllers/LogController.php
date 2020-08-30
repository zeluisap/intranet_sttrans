<?php
class LogController extends Escola_Controller_Logado {
	
	public function indexAction() {
		$session = Escola_Session::getInstance();
		$filtros = array("filtro_operacao", "filtro_id", "filtro_tabela", "filtro_cpf", "filtro_nome", "page", "filtro_data_inicio", "filtro_data_final");
		$tb = new TbLog();
		$this->view->dados = $session->atualizaFiltros($filtros);
		$this->view->dados["pagina_atual"] = $this->view->dados["page"];
		$this->view->registros = $tb->listarPagina($this->view->dados);
		$button = Escola_Button::getInstance();
		$button->setTitulo("LOG DE OPERAÇÕES");
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

	public function viewAction() {
		$id = $this->_request->getParam("id");
		if ($id) {
			$registro = TbLog::pegaPorId($id);
			if ($registro) {
				$this->view->registro = $registro;
				$this->view->campos = $registro->pegaCampos();
				$button = Escola_Button::getInstance();
				$button->setTitulo("VISUALIZAR LOG");
				$button->addAction("Voltar", $this->_request->getControllerName(), "index", "icon-reply");
			} else {
				$this->_flashMessage("INFORMAÇÃO RECEBIDA INVÁLIDA!");
				$this->_redirect($this->_request->getControllerName() . "/index");
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect($this->_request->getControllerName() . "/index");
		}
	}
}