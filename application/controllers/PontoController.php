<?php
class PontoController extends Escola_Controller_Logado {
	private $funcionario;
	
	public function indexAction() {
		$setor = false;
		$tb = new TbFuncionario();
		$funcionario = $tb->pegaLogado();
		if ($funcionario) {
			$this->funcionario = $funcionario;
			$lotacao = $funcionario->pegaLotacaoAtual();
			if ($lotacao) {
				$setor = $lotacao->findParentRow("TbSetor");
			}
		}
		if ($setor) {
			$this->view->grade = $this->criaGradeSetor($setor);
			$filtros = array("ano_mes");
			$sessao = Escola_Session::getInstance();
			$this->view->dados = $sessao->atualizaFiltros($filtros);
			if (!$this->view->dados["ano_mes"]) {
				$date = new Zend_Date();
				$this->view->dados["ano_mes"] = $date->get("YYYY_M");
			}
			$button = Escola_Button::getInstance();
			$button->setTitulo("FOLHA DE PONTO");
			$button->addFromArray(array("titulo" => "Imprimir",
										"onclick" => "imprimir()",
										"id" => "bt_imprimir",
										"img" => "icon-print"));
			$button->addFromArray(array("titulo" => "Voltar",
										"controller" => "intranet",
										"action" => "index",
										"img" => "icon-reply",
										"params" => array("id" => 0)));
		}
	}
	
	public function imprimirAction() {
		$dados = $this->_request->getParams();
		if (isset($dados["lista"]) && is_array($dados["lista"]) && count($dados["lista"])) {
			$relatorio = new Escola_Relatorio_Ponto($dados["ano_mes"]);
			$relatorio->set_ids($dados["lista"]);
			$relatorio->imprimir();
		}
	}
	
	public function criaGradeSetor($setor) {
		$lotacaos = $setor->pegaLotacaoAtiva();
		ob_start();
?>
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th colspan="5"><?php echo $setor->toString(); ?></th>
			</tr>
			<tr>
				<th width="20px"><input type="checkbox" name="ck" id="ck" class="ck-marca-todos" rel="lista" /></th>	
				<th>Matrícula</th>
				<th>Nome</th>
				<th>Cargo</th>
				<th>Vínculo</th>
			</tr>
		</thead>
<?php if ($lotacaos && count($lotacaos)) { ?>
		<tbody>
<?php
	foreach ($lotacaos as $lotacao) {
		$funcionario = $lotacao->findParentRow("TbFuncionario");
		$vinculo = "--";
		$ft = $funcionario->findParentRow("TbFuncionarioTipo");
		if ($ft) {
			$vinculo = $ft->toString();
		}
		$checked = "";
		if ($lotacao->id_funcionario == $this->funcionario->getId()) {
			$checked = " checked ";
		}
?>
			<tr>
				<td>
					<input type="checkbox" name="lista[]" class="lista" value="<?php echo $lotacao->id_funcionario; ?>" <?php echo $checked; ?> />
				</td>
				<td>
					<a href="#" class="link_lotacao" id="<?php echo $lotacao->id_funcionario; ?>">
						<?php echo $funcionario->matricula; ?>
					</a>
				</td>
				<td>
					<a href="#" class="link_lotacao" id="<?php echo $lotacao->id_funcionario; ?>">
					<?php echo $funcionario->findParentRow("TbPessoaFisica")->nome; ?>
					</a>
				</td>
				<td>
					<a href="#" class="link_lotacao" id="<?php echo $lotacao->id_funcionario; ?>">
					<?php echo $funcionario->findParentRow("TbCargo")->toString(); ?>
					</a>
				</td>
				<td>
					<a href="#" class="link_lotacao" id="<?php echo $lotacao->id_funcionario; ?>">
					<?php echo $vinculo; ?>
					</a>
				</td>
			</tr>
<?php } ?>
		</tbody>
<?php } ?>
	</table>
<?php 
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}