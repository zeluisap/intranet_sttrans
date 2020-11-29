<?php
class TbFuncionario extends Escola_Tabela
{
	protected $_name = "funcionario";
	protected $_rowClass = "Funcionario";
	protected $_dependentTables = array("TbLotacao", "TbFuncionarioOcorrencia", "TbChamado", "TbChamadoOcorrencia", "TbRequerimentoJariResposta");
	protected $_referenceMap = array(
		"FuncionarioSituacao" => array(
			"columns" => array("id_funcionario_situacao"),
			"refTableClass" => "TbFuncionarioSituacao",
			"refColumns" => array("id_funcionario_situacao")
		),
		"FuncionarioTipo" => array(
			"columns" => array("id_funcionario_tipo"),
			"refTableClass" => "TbFuncionarioTipo",
			"refColumns" => array("id_funcionario_tipo")
		),
		"Cargo" => array(
			"columns" => array("id_cargo"),
			"refTableClass" => "TbCargo",
			"refColumns" => array("id_cargo")
		),
		"PessoaFisica" => array(
			"columns" => array("id_pessoa_fisica"),
			"refTableClass" => "TbPessoaFisica",
			"refColumns" => array("id_pessoa_fisica")
		)
	);

	public function getSql($dados = array())
	{
		$sql = $this->select();
		$sql->from(array("u" => "funcionario"));
		$sql->join(array("p" => "pessoa_fisica"), "u.id_pessoa_fisica = p.id_pessoa_fisica", array());
		$sql->join(array("c" => "cargo"), "u.id_cargo = c.id_cargo", array());
		$sql->join(array("fs" => "funcionario_situacao"), "u.id_funcionario_situacao = fs.id_funcionario_situacao", array());
		$sql->join(array("l" => "lotacao"), "u.id_funcionario = l.id_funcionario", array());
		$sql->join(array("lt" => "lotacao_tipo"), "l.id_lotacao_tipo = lt.id_lotacao_tipo", array());
		$sql->join(array("s" => "setor"), "l.id_setor = s.id_setor", array());
		$sql->where("lt.chave = 'N'");
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
		if (isset($dados["filtro_cargo"]) && $dados["filtro_cargo"]) {
			$sql->where("c.descricao like '%" . $dados["filtro_cargo"] . "%'");
		}
		if (isset($dados["filtro_matricula"]) && $dados["filtro_matricula"]) {
			$sql->where("u.matricula = '{$dados["filtro_matricula"]}'");
		}
		if (isset($dados["filtro_setor"]) && $dados["filtro_setor"]) {
			$sql->where("s.descricao like '%" . $dados["filtro_setor"] . "%'");
			$sql->orWhere("s.sigla = '" . $dados["filtro_setor"] . "'");
		}
		if (isset($dados["filtro_situacao"]) && $dados["filtro_situacao"]) {
			$sql->where("fs.chave  = '" . $dados["filtro_situacao"] . "'");
		}
		if (isset($dados["filtro_id_funcionario_situacao"]) && $dados["filtro_id_funcionario_situacao"]) {
			$sql->where("u.id_funcionario_situacao = {$dados["filtro_id_funcionario_situacao"]} ");
		}
		$sql->order("p.nome");
		$sql->order("u.matricula desc");
		return $sql;
	}

	public function getPorPessoaFisica($pf)
	{
		if ($pf && $pf->getId()) {
			$tb = new TbFuncionarioSituacao();
			$fs = $tb->getPorChave("A");
			$rg = $this->fetchAll(" id_pessoa_fisica = {$pf->id_pessoa_fisica} and id_funcionario_situacao = '" . $fs->getId() . "' ");
			if (count($rg)) {
				return $rg->current();
			}
		}
		return false;
	}

	public function getPorUsuario($usuario)
	{
		return $this->getPorPessoaFisica($usuario->pega_pessoa_fisica());
	}

	public function pegaAniversariantes($date = false)
	{
		if ($date) {
			$inicio = new Zend_Date($date);
		} else {
			$inicio = new Zend_Date();
		}
		$week = $inicio->get("ee");
		//$inicio->sub($week, Zend_Date::DAY);
		$inicio->sub(1, Zend_Date::DAY);
		$fim = new Zend_Date($inicio->get("YYYY-MM-dd"));
		//$fim->add(6, Zend_Date::DAY);
		$fim->add(3, Zend_Date::DAY);

		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$tb_fs = new TbFuncionarioSituacao();
		$fs = $tb_fs->getPorChave("A");
		$sql->from(array("f" => "funcionario"), array("f.id_funcionario"));
		$sql->join(array("p" => "pessoa_fisica"), "f.id_pessoa_fisica = p.id_pessoa_fisica", array());
		if ($fs) {
			$sql->where("f.id_funcionario_situacao = " . $fs->getId());
		}
		$where = array();
		while ($inicio <= $fim) {
			$where[] = "(day(p.data_nascimento) = '" . $inicio->get("dd") . "' and month(p.data_nascimento) = '" . $inicio->get("MM") . "')";
			//$sql->orWhere("(day(p.data_nascimento) = '" . $inicio->get("dd") . "' and month(p.data_nascimento) = '" . $inicio->get("MM") . "')");
			$inicio->add(1, Zend_Date::DAY);
		}
		if (count($where)) {
			$sql->where(implode(" or ", $where));
		}
		$sql->order("month(p.data_nascimento)");
		$sql->order("day(p.data_nascimento)");
		$sql->order("p.nome");
		$stmt = $db->query($sql);
		if ($stmt && count($stmt)) {
			$items = array();
			$rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
			foreach ($rg as $obj) {
				$items[] = TbFuncionario::pegaPorId($obj->id_funcionario);
			}
			return $items;
		}
		return false;
	}

	public function getPorMatricula($matricula)
	{
		$rg = $this->fetchAll(" matricula = '{$matricula}' ");
		if ($rg && count($rg)) {
			return $rg->current();
		}
		return false;
	}

	public function pegaLogado()
	{
		$usuario = TbUsuario::pegaLogado();
		if ($usuario) {
			return $this->getPorUsuario($usuario);
		}
		return false;
	}

	public function importar_arquivo($filename, $controller = false)
	{
		$linhas = Escola_Util::carregaArquivoDados($filename);
		if ($linhas && count($linhas)) {
			$db = Zend_Registry::get("db");
			//$db->beginTransaction();
			try {
				$tb = new TbFuncionario();
				foreach ($linhas as $linha) {
					$dados = $linha;
					if (isset($dados["e_mail"])) {
						$dados["email"] = $dados["e_mail"];
					}
					$funcionario = $tb->getPorMatricula($linha["matricula"]);
					if (!$funcionario) {
						$funcionario = $tb->createRow();
						//verifica se existe a pessoa referente ao funcionário
						$tb_pf = new TbPessoaFisica();
						$pf = $tb_pf->getPorCPF(str_pad($dados["cpf"], 11, "0"));
						if (!$pf) {
							$pf = $tb_pf->createRow();
							$tb_uf = new TbUf();
							$uf = $tb_uf->getPorSigla($dados["identidade_uf"]);
							if ($uf) {
								$dados["identidade_id_uf"] = $uf->getId();
							}
							$uf = false;
							if (isset($dados["nascimento_uf"])) {
								$uf = $tb_uf->getPorSigla($dados["nascimento_uf"]);
							} elseif (isset($dados["nascimento_uf_descricao"])) {
								$uf = $tb_uf->getPorDescricao($dados["nascimento_uf_descricao"]);
							}
							if ($uf) {
								$tb_mun = new TbMunicipio();
								$rg = $tb_mun->fetchAll(" id_uf = " . $uf->getId() . " and descricao = '{$dados["municipio_nascimento"]}'");
								if ($rg && $rg->count()) {
									$municipio = $rg->current();
								} else {
									$municipio = $tb_mun->createRow();
									$municipio->setFromArray(array(
										"descricao" => $dados["municipio_nascimento"],
										"id_uf" => $uf->getId()
									));
									$municipio->save();
								}
								$dados["nascimento_id_municipio"] = $municipio->getId();
							} else {
								throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, UF DE NASCIMENTO - " . $dados["nascimento_uf"] . " NÃO ENCONTRADO. MATRÍCULA: " . $dados["matricula"]);
							}
						}
						$pf->setFromArray($dados);
						$id_pf = $pf->save();
						$dados["id_pessoa_fisica"] = $id_pf;
						$tb_cargo = new TbCargo();
						$cargo = $tb_cargo->getPorDescricao($dados["cargo"]);
						if (!$cargo) {
							$tb_ct = new TbCargoTipo();
							$ct = $tb_ct->getPorChave("T");
							if ($ct) {
								$cargo = $tb_cargo->createRow();
								$cargo->setFromArray(array(
									"descricao" => $dados["cargo"],
									"id_cargo_tipo" => $ct->getId()
								));
								$cargo->save();
							}
						}
						$dados["id_cargo"] = $cargo->getId();
					}
					if (isset($dados["vinculo"])) {
						$tb_vinculo = new TbFuncionarioTipo();
						$vinculo = $tb_vinculo->getPorDescricao($dados["vinculo"]);
						if (!$vinculo) {
							$vinculo = $tb_vinculo->createRow();
							$vinculo->setFromArray(array("descricao" => $dados["vinculo"]));
							$vinculo->save();
						}
						$dados["id_funcionario_tipo"] = $vinculo->getId();
					}
					$funcionario->setFromArray($dados);
					$id = $funcionario->save();
					if ($id) {
						$tb_ft = new TbFuncionarioSituacao();
						$ft = $tb_ft->getPorChave("I");
						if ($ft) {
							$sql = "update funcionario
												set id_funcionario_situacao = " . $ft->getId() . "
												where (id_pessoa_fisica = " . $funcionario->id_pessoa_fisica . ")
												and (id_funcionario <> " . $funcionario->getId() . ")";
							$db->query($sql);
						}
						$lotacaos = $funcionario->pegaLotacao();
						if (!$lotacaos) {
							$tb_lt = new TbLotacaoTipo();
							$lt = $tb_lt->getPorChave("N");
							if ($lt) {
								$flag = explode("-", $dados["setor_sigla"]);
								$tb_setor = new TbSetor();
								$setor = $tb_setor->getPorSigla(trim($flag[1]));
								if (!$setor) {
									$tb_setor_nivel = new TbSetorNivel();
									$setor_nivel = $tb_setor_nivel->getPorChave("S");
									if ($setor_nivel) {
										$setor = $tb_setor->createRow();
										$setor->setFromArray(array(
											"codigo" => $flag[0],
											"sigla" => trim($flag[1]),
											"descricao" => $dados["setor_descricao"],
											"id_setor_nivel" => $setor_nivel->getId()
										));
										$setor->save();
									}
								}
								$dados["id_setor"] = $setor->getId();

								$tb_lotacao = new TbLotacao();
								$lotacao = $tb_lotacao->createRow();
								$lotacao->setFromArray(array(
									"id_funcionario" => $id,
									"id_setor" => $setor->getId(),
									"id_lotacao_tipo" => $lt->getId()
								));
								$lotacao->save();
							}
						}
					}
				}
				//$db->commit();
			} catch (Exception $e) {
				//$db->rollBack();
				if ($controller) {
					$controller->_flashMessage($e->getMessage());
				}
			}
		}
	}
}
