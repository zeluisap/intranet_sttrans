<?php 
class ContrachequeController extends Escola_Controller_Logado {

    public function indexAction() {
        $session = Escola_Session::getInstance();
		$filtros = array("funcionarios", "ano", "mes", "tipofolha");
		$dados = $session->atualizaFiltros($filtros);
		$funcionarios = $dados["funcionarios"];
		$usuario = TbUsuario::pegaLogado();
		$pf = $usuario->findParentRow("TbPessoaFisica");
		$sql = "select b.nome funcao_nome, a.funcionarios, a.nome, a.funcao
				from funcionarios a, funcao b
				where (a.funcao = b.funcao) ";
		if ($funcionarios) {
			$sql .= " and (a.funcionarios = {$funcionarios}) ";
		} else {
			$sql .= " and (a.cpf = '{$pf->cpf}') ";
		}
		$sql .= " order by a.FUNCIONARIOS";
		$funcionarios = Escola_Util::consulta_ibase($sql);
		if (!$funcionarios) {
			$this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, NENHUM FUNCIONÁRIO LOCALIZADO!");
			$this->_redirect("intranet");
		}
		$sql = "select * from tipofolha";
		$tipofolhas = Escola_Util::consulta_ibase($sql);
		if (!$dados["ano"]) {
			$dados["ano"] = date("Y");
			if (!$dados["mes"]) {
				$dados["mes"] = date("m");
			}
		}
		$this->view->funcionarios = $funcionarios;
		$this->view->tipofolhas = $tipofolhas;
		$this->view->dados = $dados;
		$button = Escola_Button::getInstance();
		$button->setTitulo("EMISSÃO DE CONTRA-CHEQUE");
		$button->addScript("Gerar", "salvarFormulario('formulario')", "icon-money");
		$button->addFromArray(array("titulo" => "Voltar",
											"controller" => "intranet",
											"action" => "index",
											"img" => "icon-reply"));
	}
	
	public function gerarAction() {
		$msg = array();
		$filtros = array("funcionarios", "ano", "mes", "tipofolha");
		$session = Escola_Session::getInstance();
		$dados = $session->atualizaFiltros($filtros);		
		if (!isset($dados["funcionarios"]) || !$dados["funcionarios"]) {
                    $msg[] = "NENHUM FUNCIONÁRIO INFORMADO!";
		}
		if (!isset($dados["ano"]) || !$dados["ano"]) {
                    $msg[] = "INFORME O ANO!";
		}
		if (!isset($dados["mes"]) || !$dados["mes"]) {
                    $msg[] = "INFORME O MÊS!";
		}
		if (!isset($dados["tipofolha"]) || !$dados["tipofolha"]) {
                    $msg[] = "INFORME O TIPO DA FOLHA!";
		}
		$sql = "select * 
				from folha
				where (funcionarios = {$dados["funcionarios"]})
				and (ano = {$dados["ano"]}) 
				and (data like '{$dados["ano"]}-{$dados["mes"]}%')
				and (tipofolha = {$dados["tipofolha"]}) "; 
		$folhas = Escola_Util::consulta_ibase($sql);
		if (!$folhas) {
			$msg[] = "NENHUM CONTRACHEQUE DISPONÍVEL!";
		}
		if (count($msg)) {
			foreach ($msg as $m) {
				$this->_flashMessage($m);
			}
			$this->_redirect("contracheque");
		}
		$folha = $folhas[0];
		$this->view->folha = $folha;
		$this->view->ano = $dados["ano"];
		$this->view->mes = $dados["mes"];
		$sql = "select a.setores, c.nome setor_nome, b.nome nome_funcao, a.funcionarios, a.nome, a.cpf, a.dataadmissao, a.cbo, a.numerorg
				from funcionarios a, funcao b, setores c
				where (a.funcao = b.funcao)
				and (a.setores = c.setores)
				and (funcionarios = {$dados["funcionarios"]})";
		$funcionario = false;
		$funcionarios = Escola_Util::consulta_ibase($sql);
		if ($funcionarios) {
			$this->view->funcionario = $funcionarios[0];
			$sql = "select a.numero conta_numero, a.digito conta_digito, b.bancos, b.numero agencia_numero, b.DIGITO agencia_digito 
					from CONTASFUNCIONARIOS a, bancosagencias b
					where a.BANCOSAGENCIAS = b.BANCOSAGENCIAS
					and a.FUNCIONARIOS = {$this->view->funcionario->FUNCIONARIOS}"; 
			$conta = false;
			$contas = Escola_Util::consulta_ibase($sql);
			if ($contas) {
				$conta = $contas[0];
				$this->view->conta = $conta;
			}
			$sql = "select b.TIPOEVENTOS, b.nome evento_nome, a.*
					from ITEMFOLHA a, eventos b
					where (a.eventos = b.eventos)
					and (a.ano = {$folha->ANO})
					and (a.folha = {$folha->FOLHA})
					order by b.TIPOEVENTOS";
			$this->view->items = Escola_Util::consulta_ibase($sql);
		}
		$button = Escola_Button::getInstance();
		$button->setTitulo("EMISSÃO DE CONTRA-CHEQUE");
		$button->addFromArray(array("titulo" => "Imprimir",
											"controller" => $this->_request->getControllerName(),
											"action" => "imprimir",
											"img" => "icon-print",
											"params" => array("folha" => $folha->FOLHA)));
		$button->addFromArray(array("titulo" => "Voltar",
											"controller" => $this->_request->getControllerName(),
											"action" => "index",
											"img" => "icon-reply"));
	}
	
	public function imprimirAction() {
		$dados = $this->_request->getParams();
		$relatorio = new Escola_Relatorio_Contracheque($dados["folha"]);
		$relatorio->imprimir();
	}
}