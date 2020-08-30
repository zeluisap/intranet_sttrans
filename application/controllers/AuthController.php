<?php
class AuthController extends Escola_Controller_Default
{
	public function indexAction()
	{
		$this->_forward("login");
	}

	public function loginAction()
	{
		$this->view->login_cpf = $this->_request->getParam("login_cpf");
	}

	public function identifyAction()
	{
		if (!$this->getRequest()->isPost()) {
			$this->_redirect("/auth/login");
		}

		try {

			$formData = $this->getRequest()->getPost();

			if (empty($formData["login_cpf"]) || empty($formData["login_senha"])) {
				throw new Escola_Exception("CPF ou Senha em branco.");
			}

			$tb_usuario = new TbUsuario();
			$usuarios = $tb_usuario->getPorCPF($formData["login_cpf"]);
			if (!$usuarios) {
				throw new Escola_Exception("Usuário não localizado.");
			}

			if (!$usuarios->ativo()) {
				throw new Escola_Exception("USUÁRIO INATIVO!");
			}

			$tb = new TbPacote();
			$pacotes = $tb->buscarPacotes($usuarios);
			if (!$pacotes) {
				throw new Escola_Exception("Falha ao Executar Login, Nenhum pacote definido para o usuário!");
			}

			$formData["id_usuario"] = $usuarios->id_usuario;
			$authAdapter = $this->_getAuthAdapter($formData);
			$auth = Zend_Auth::getInstance();
			$result = $auth->authenticate($authAdapter);

			if (!$result->isValid()) {
				throw new Escola_Exception("Falha ao efetuar login. Usuário ou Senha inválida!");
			}

			$data = $authAdapter->getResultRowObject(null, 'senha');
			$auth->getStorage()->write($data);
			$tb_log = new TbLog();
			$tb_log->registraLogin();
			$this->_redirect("intranet");
			return;
		} catch (Exception $ex) {
			$this->_flashMessage($ex->getMessage());
			$this->_redirect("auth/login");
		}
	}

	protected function _getAuthAdapter($formData)
	{
		$dbAdapter = Zend_Registry::get("db");
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$authAdapter->setTableName("usuario");
		$authAdapter->setIdentityColumn("id_usuario");
		$authAdapter->setCredentialColumn("senha");
		//$authAdapter->setCredentialTreatment("SHA1()");
		$senha = $formData["login_senha"];
		$authAdapter->setIdentity($formData["id_usuario"]);
		$authAdapter->setCredential(md5($senha));
		return $authAdapter;
	}

	public function logoutAction()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$sessao = Escola_Session::getInstance();
		$sessao->unsetAll();
		$this->_redirect("intranet");
	}

	public function sistemaAction()
	{
		$this->view->request = $this->_request;
		if ($this->_request->isPost()) {
			$db = Zend_Registry::get("db");
			$db->beginTransaction();
			$dados = $this->_request->getPost();
			$erros = array();
			if (!$dados["sistema_sigla"]) {
				$erros[] = "CAMPO SIGLA DO SISTEMA OBRIGATÓRIO!";
			}
			if (!$dados["sistema_descricao"]) {
				$erros[] = "CAMPO DESCRIÇÃO DO SISTEMA OBRIGATÓRIO!";
			}
			if (!$dados["sistema_email"]) {
				$erros[] = "CAMPO E-MAIL DO SISTEMA OBRIGATÓRIO!";
			}
			if (!$dados["sistema_versao"]) {
				$erros[] = "CAMPO VERSÃO DO SISTEMA OBRIGATÓRIO!";
			}
			if (!$dados["cliente_sigla"]) {
				$erros[] = "CAMPO SIGLA DO CLIENTE OBRIGATÓRIO!";
			}
			if (!$dados["cliente_razao_social"]) {
				$erros[] = "CAMPO NOME DO CLIENTE OBRIGATÓRIO!";
			}
			if (!$dados["cliente_email"]) {
				$erros[] = "CAMPO E-MAIL DO CLIENTE OBRIGATÓRIO!";
			}
			$val = new Escola_Validate_Cpf();
			if (!$val->isValid($dados["administrador_cpf"])) {
				$erros[] = "CAMPO CPF DO ADMINISTRADOR INVÁLIDO!";
			}
			if (!$dados["administrador_nome"]) {
				$erros[] = "CAMPO NOME DO ADMINISTRADOR INVÁLIDO!";
			}
			if (!$dados["administrador_email"]) {
				$erros[] = "CAMPO E-MAIL DO ADMINISTRADOR INVÁLIDO!";
			}
			if (!$dados["administrador_senha"]) {
				$erros[] = "CAMPO SENHA DO ADMINISTRADOR INVÁLIDO!";
			}
			if ($dados["administrador_senha"] != $dados["administrador_senha_confirmar"]) {
				$erros[] = "SENHAS NÃO COINCIDEM!";
			}
			if (count($erros)) {
				$this->view->actionErrors[] = implode("<br>", $erros);
			} else {
				$tb_sistema = new TbSistema();
				$tb_pf = new TbPessoaJuridica();
				$tb_pessoa = new TbPessoa();
				$sistema = $tb_sistema->pegaSistema();
				if (!$sistema) {
					$sistema = $tb_sistema->createRow();
					$pessoa = $tb_pessoa->createRow();
					$pf = $tb_pf->createRow();
				}
				$tb_tpf = new TbPessoaTipo();
				$pt = $tb_tpf->getPorChave("PJ");
				$dados_pessoa = array(
					"id_pessoa_tipo" => $pt->id_pessoa_tipo,
					"email" => $dados["cliente_email"]
				);
				$pessoa->setFromArray($dados_pessoa);
				$msg = $pessoa->getErrors();
				if (!$msg) {
					$id_pessoa = $pessoa->save();
					if ($id_pessoa) {
						$dados_pj = array(
							"id_pessoa" => $id_pessoa,
							"sigla" => $dados["cliente_sigla"],
							"razao_social" => $dados["cliente_razao_social"],
							"nome_fantasia" => $dados["cliente_razao_social"]
						);
						$pjs = new TbPessoaJuridica();
						$pj = $pjs->createRow();
						$pj->setFromArray($dados_pj);
						$id_pj = $pj->save();
						$dados_sistema = array(
							"sigla" => $dados["sistema_sigla"],
							"descricao" => $dados["sistema_descricao"],
							"email" => $dados["sistema_email"],
							"versao" => $dados["sistema_versao"],
							"id_pessoa_juridica" => $id_pj
						);
						$sistema->setFromArray($dados_sistema);
						$msg = $sistema->getErrors();
						if (!$msg) {
							$sistema->save();
							/*
							 primeiro administrador do sistema
							*/
							$tb_tpf = new TbPessoaTipo();
							$pt = $tb_tpf->getPorChave("PF");
							$dados_pessoa = array(
								"id_pessoa_tipo" => $pt->getId(),
								"email" => $dados["administrador_email"]
							);
							$pessoa = $tb_pessoa->createRow();
							$pessoa->setFromArray($dados_pessoa);
							$erros = $pessoa->getErrors();
							if (!$erros) {
								$id_pessoa = $pessoa->save();
								if ($id_pessoa) {
									$tb_ec = new TbEstadoCivil();
									$ec = $tb_ec->getPorDescricao("SOLTEIRO(A)");
									$tb_municipio = new TbMunicipio();
									$municipios = $tb_municipio->listar(array("descricao" => "MACAPA"));
									if ($municipios) {
										$municipio = $municipios[0];
									} else {
										$tb_uf = new TbUf();
										$uf = $tb_uf->getPorSigla("AP");
										$municipio = $tb_municipio->createRow();
										$municipio->setFromArray(array(
											"descricao" => "MACAPA",
											"id_uf" => $uf->getId()
										));
										$municipio->save();
									}
									$pfs = new TbPessoaFisica();
									$pf = $pfs->createRow();
									$flag = array(
										"id_pessoa" => $id_pessoa,
										"cpf" => $dados["administrador_cpf"],
										"nome" => $dados["administrador_nome"],
										"email" => $dados["administrador_email"],
										"data_nascimento" => $dados["administrador_data_nascimento"],
										"identidade_numero" => $dados["administrador_identidade_numero"],
										"identidade_orgao_expedidor" => $dados["administrador_identidade_orgao_expedidor"],
										"identidade_id_uf" => $dados["administrador_identidade_id_uf"],
										"nascimento_id_municipio" => $municipio->getId()
									);
									if ($ec) {
										$flag["id_estado_civil"] = $ec->getId();
									}
									$pf->setFromArray($flag);

									$erros = $pf->getErrors();
									if (!$erros) {
										$id_pf = $pf->save();
										if ($id_pf) {
											$tb = new TbUsuarioSituacao();
											$us = $tb->getPorChave("A");
											if ($us->getId()) {
												$tb = new TbUsuario();
												$usuario = $tb->createRow();
												$usuario->setFromArray(array(
													"id_pessoa_fisica" => $id_pf,
													"id_usuario_situacao" => $us->getId(),
													"senha" => $dados["administrador_senha"]
												));
												$erros = $usuario->getErrors();
												if (!$erros) {
													$usuario->save();
													$tb = new TbGrupo();
													$grupo = $tb->pegaAdministrador();
													if ($grupo->getId()) {
														$usuario->addGrupo($grupo);
													}
													$db->commit();
													$this->_redirect("index");
												} else {
													$this->view->actionErrors[] = implode("<br>", $erros);
												}
											}
										}
									} else {
										$this->view->actionErrors[] = implode("<br>", $erros);
									}
								}
							} else {
								$this->view->actionErrors[] = implode("<br>", $erros);
							}
						} else {
							$this->view->actionErrors[] = implode("<br>", $msg);
						}
					} else {
						$this->view->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, ENTRE EM CONTATO COM O SUPORTE!");
					}
				} else {
					$this->view->actionErrors[] = implode("<br>", $msg);
				}
			}
			$db->rollBack();
		}
		$this->view->request_adm = $this->_request->getPost("administrador");
		$button = Escola_Button::getInstance();
		$button->setTitulo("CONFIGURAÇÕES DO SISTEMA");
		$button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
		$button->addAction("Cancelar", "index", "index", "icon-remove-circle");
	}

	public function novoAction()
	{
		if ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$cpf_val = new Escola_Validate_Cpf();
			if ($cpf_val->isValid($dados["cpf"])) {
				$tb_usuario = new TbUsuario();
				$usuario = $tb_usuario->getPorCpf($dados["cpf"]);
				if (!$usuario) {
					$session = new Zend_Session_Namespace();
					$session->cpf = $dados["cpf"];
					$this->_redirect("auth/register");
				} else {
					$this->view->actionErrors[] = "USUÁRIO JÁ CADASTRADO!";
				}
			} else {
				$this->view->actionErrors[] = "CPF INVÁLIDO!";
			}
		}
	}

	public function registerAction()
	{
		$flag = true;
		$session = new Zend_Session_Namespace();
		if ($session->cpf) {
			$this->view->cpf = $session->cpf;
			//unset($session->cpf);
			if ($this->_request->isPost()) {
				$db = Zend_Registry::get("db");
				$db->beginTransaction();
				$dados = $this->_request->getPost();
				if (!isset($dados["nascimento_id_municipio"]) || !$dados["nascimento_id_municipio"]) {
					if (!isset($dados["id_uf"]) || !$dados["id_uf"]) {
						if (isset($dados["dinamic_id_uf"]) && $dados["dinamic_id_uf"]) {
							$dados["id_uf"] = $dados["dinamic_id_uf"];
						} elseif (isset($dados["dinamic_uf"]) && $dados["dinamic_uf"]) {
							$tb = new TbUf();
							$uf = $tb->getPorDescricao($dados["dinamic_uf"]);
							if (!$uf) {
								$uf = $tb->createRow();
								$uf->setFromArray(array(
									"descricao" => $dados["dinamic_uf"],
									"id_pais" => $dados["id_pais"]
								));
								$dados["id_uf"] = $uf->save();
							}
						}
					}
					if (isset($dados["id_uf"]) && $dados["id_uf"]) {
						if (isset($dados["municipio"]) && $dados["municipio"]) {
							$tb = new TbMunicipio();
							$where = " (descricao = '" . $dados["municipio"] . "')  ";
							$municipios = $tb->fetchAll($where);
							if ($municipios->count()) {
								$dados["nascimento_id_municipio"] = $municipios->current()->id_municipio;
							} else {
								$municipio = $tb->createRow();
								$municipio->setFromArray(array(
									"descricao" => $dados["municipio"],
									"id_uf" => $dados["id_uf"]
								));
								$dados["nascimento_id_municipio"] = $municipio->save();
							}
						}
					}
				}
				$tb = new TbUsuario();
				$usuario = $tb->createRow();
				$usuario->setFromArray($dados);
				$msgs = $usuario->getErrors();
				if ($msgs) {
					$db->rollBack();
					$this->view->actionErrors[] = implode("<br>", $msgs);
				} else {
					$flag = false;
					$usuario->save();
					$filter = new Zend_Filter_Digits();
					if (isset($dados["telefone_fixo"]) && $filter->filter($dados["telefone_fixo"])) {
						$tb = new TbTelefoneTipo();
						$tt = $tb->getPorChave("F");
						if ($tt) {
							$pessoa = $usuario->getPessoaFisica()->getPessoa();
							if ($pessoa) {
								$tb = new TbTelefone();
								$fixo = $tb->createRow();
								$fixo->id_telefone_tipo = $tt->id_telefone_tipo;
								$fixo->setFormatado($dados["telefone_fixo"]);
								if (!$fixo->getErrors()) {
									$fixo->save();
								}
								$pessoa->addTelefone($fixo);
							}
						}
					}
					if (isset($dados["telefone_celular"]) && $filter->filter($dados["telefone_celular"])) {
						$tb = new TbTelefoneTipo();
						$tt = $tb->getPorChave("C");
						if ($tt) {
							$pessoa = $usuario->getPessoaFisica()->getPessoa();
							if ($pessoa) {
								$tb = new TbTelefone();
								$celular = $tb->createRow();
								$celular->id_telefone_tipo = $tt->id_telefone_tipo;
								$celular->setFormatado($dados["telefone_celular"]);
								$celular->save();
								$pessoa->addTelefone($celular);
							}
						}
					}
					/* 
					$endereco = $pessoa->getEndereco();
					$endereco->setFromArray($dados);
					if (!$endereco->getErrors()) {
						$endereco->save();
						$pessoa->addEndereco($endereco);
					} 
					*/
					$db->commit();
					$this->_flashMessage("REGISTRO SALVO COM SUCESSO!", "Messages");
					$this->_redirect("intranet/index");
				}
			}
			if ($flag) {
				$tb_ec = new TbEstadoCivil();
				$this->view->ecs = $tb_ec->fetchAll();
				$tb_paises = new TbPais();
				$this->view->paises = $tb_paises->listar();
				$button = Escola_Button::getInstance();
				$button->setTitulo("NOVO USUÁRIO");
				$button->addScript("Salvar", "salvarFormulario('formulario')", "disk.png");
				$button->addAction("Cancelar", "index", "index", "delete.png");
				$tb = new TbEndereco();
				$this->view->endereco = $tb->createRow();
				$this->view->endereco->setFromArray($this->_request->getPost());
			}
		} else {
			$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA!");
			$this->_redirect("auth/novo");
		}
	}

	public function recuperasenhaAction()
	{
		if ($this->_request->isPost()) {
			$tb = new TbUsuario();
			$usuario = $tb->getPorCPF($this->_request->getPost("cpf"));
			if ($usuario) {
				if ($usuario->ativo()) {
					$pf = $usuario->getPessoaFisica();
					$email = $pf->getPessoa()->email;
					if ($email) {
						$tb = new TbSistema();
						$sistema = $tb->pegaSistema();
						if ($sistema) {
							$pj = $sistema->findParentRow("TbPessoaJuridica");
							if ($pj) {
								$pessoa = $pj->findParentRow("TbPessoa");
								if ($pessoa) {
									$db = Zend_Registry::get("db");
									$db->beginTransaction();
									try {
										$link = $usuario->geraLinkRecuperaSenha();
										$db->commit();
										ob_start();
?>
										<style type="text/css">
											div {
												font-family: Verdana, Arial;
												font-size: 14px;
											}

											h4 {
												text-align: center;
											}

											p.equipe {
												font-size: 20px;
											}
										</style>
										<div>
											<h4><?php echo $pj->sigla; ?> - <?php echo $pj->razao_social; ?></h4>
											<h4><?php echo $sistema; ?></h4>
											<p>Prezado <strong><?php echo $pf->nome; ?></strong>, </p>
											<p>Através deste, o(a) <strong><?php echo $pj->sigla; ?></strong> vem atender sua solicitação de recadastramento de senha.</p>
											<p>Se você não fez nenhuma requisição de recadastramento de senha, simplesmente despreze este e-mail.</p>
											<p>Para efetuar agora mesmo seu recadastramento, clique no link abaixo:</p>
											<p><a href="<?php echo $link; ?>"><?php echo $link; ?></a></p>
											<p>Obrigado, </p>
											<p class="equipe">Equipe <?php echo $pj->sigla; ?></p>
										</div>
<?php
										$body = ob_get_contents();
										ob_end_clean();
										$smtp = TbSmtp::getSmtp();
										if ($smtp && $smtp->host) {
											$array = array();
											$array["titulo"] = $sistema->sigla . " - RECUPERAÇÃO DE SENHA";
											$array["conteudo"]["html"] = $body;
											$array["remetente"]["nome"] = "FUNPEA";
											$array["remetente"]["email"] = "atec@funpea.org.br";
											$array["destinatario"]["nome"] = $pf->nome;
											$array["destinatario"]["email"] = $email;
											$erro = $smtp->sendMail($array);
											if (!$erro) {
												$this->_flashMessage("UM E-MAIL FOI ENVIADO PARA: [ {$email} ] COM AS ORIENTAÇÕES PARA MUDANÇA DE SENHA.", "Messages");
												$this->_flashMessage("CASO ESTE NÃO SEJA SEU E-MAIL, ENTRE EM CONTATO CONOSCO ATRAVÉS DO E-MAIL: " . $pessoa->email, "Messages");
												$this->_redirect("intranet");
											} else {
												$this->view->actionErrors[] = "HOUVE UMA FALHA NO ENVIO DA SOLICITAÇÃO POR E-MAIL, <a href='" . $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "recuperasenhaoffline", "cpf" => $this->_request->getPost("cpf"))) . "'>CLIQUE AQUI</a> PARA USAR A VERSÃO OFFLINE!";
												/*
                                                $this->view->actionErrors[] = "HOUVE UMA FALHA NO ENVIO DA SOLICITAÇÃO, TENTE NOVAMENTE MAIS TARDE!";
                                                $this->view->actionErrors[] = $erro;
                                                 */
											}
										} else {
											$this->view->actionErrors[] = "HOUVE UMA FALHA NO ENVIO DA SOLICITAÇÃO POR E-MAIL, <a href='" . $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "recuperasenhaoffline", "cpf" => $this->_request->getPost("cpf"))) . "'>CLIQUE AQUI</a> PARA USAR A VERSÃO OFFLINE!";
										}
									} catch (Exception $e) {
										$db->rollBack();
										$this->view->actionErrors[] = "HOUVE UMA FALHA NO ENVIO DA SOLICITAÇÃO POR E-MAIL, <a href='" . $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "recuperasenhaoffline", "cpf" => $this->_request->getPost("cpf"))) . "'>CLIQUE AQUI</a> PARA USAR A VERSÃO OFFLINE!";
									}
								} else {
									$this->view->actionErrors[] = "HOUVE UMA FALHA NO ENVIO DA SOLICITAÇÃO, TENTE NOVAMENTE MAIS TARDE!";
								}
							} else {
								$this->view->actionErrors[] = "HOUVE UMA FALHA NO ENVIO DA SOLICITAÇÃO, TENTE NOVAMENTE MAIS TARDE!";
							}
						} else {
							$this->view->actionErrors[] = "HOUVE UMA FALHA NO ENVIO DA SOLICITAÇÃO, TENTE NOVAMENTE MAIS TARDE!";
						}
					} else {
						$this->view->actionErrors[] = "USUÁRIO NÃO POSSUI UM E-MAIL VÁLIDO!.";
					}
				} else {
					$this->view->actionErrors[] = "USUÁRIO INATIVO!";
				}
			} else {
				$this->view->actionErrors[] = "USUÁRIO NÃO LOCALIZADO!";
			}
		}
	}

	public function recoveryAction()
	{
		if ($this->_request->isGet()) {
			if ($id = $this->_request->get("hash")) {
				$dados = array(
					"hash" => $id,
					"status" => "P"
				);
				$tb = new TbUsuarioSenha();
				$rg = $tb->listar($dados);
				if ($rg) {
					$row = $rg->current();
				} else {
					$this->_flashMessage("ID DE SOLICITAÇÃO NÃO LOCALIZADO!");
					$this->_redirect("intranet");
				}
			} else {
				$this->_flashMessage("NENHUMA INFORMAÇÃO RECEBIDA");
				$this->_redirect("intranet");
			}
		} elseif ($this->_request->isPost()) {
			$dados = $this->_request->getPost();
			$tb = new TbUsuarioSenha();
			$rg = $tb->find($dados["id_usuario_senha"]);
			$row = $rg->current();
			$usuario = $row->findParentRow("TbUsuario");
			if (!isset($dados["senha"]) || !$dados["senha"]) {
				$this->view->actionErrors[] = "DADOS RECEBIDOS SÃO INVÁLIDOS!";
			} elseif ($dados["senha"] != $dados["senha_confirmar"]) {
				$this->view->actionErrors[] = "SENHAS NÃO COINCIDEM, TENTE NOVAMENTE!";
			} else {
				if (count($rg)) {
					try {
						$db = Zend_Registry::get("db");
						$db->beginTransaction();
						$usuario->setFromArray(array("senha" => $dados["senha"]));
						$usuario->save();
						$row->setFromArray(array("status" => "C"));
						$row->save();
						$db->commit();
						$this->_flashMessage("SENHA ALTERADA COM SUCESSO!", "Messages");
						$this->_redirect("intranet");
					} catch (Exception $e) {
						$db->rollBack();
					}
				}
			}
		}
		if (isset($row)) {
			$this->view->usuario_senha = $row;
			$usuario = $row->findParentRow("TbUsuario");
			$this->view->usuario = $usuario;
			/*
			$button = Escola_Button::getInstance();
			$button->setTitulo("MUDANÇA DE SENHA");
			$button->addScript("Salvar", "salvarFormulario('formulario')", "disk.png");
			$button->addAction("Cancelar", "intranet", "index", "delete.png");
			*/
		}
	}

	public function recuperasenhaofflineAction()
	{
		if ($this->_request->isPost()) {
			$flag = true;
			$campos = array("cpf", "nome", "email", "data_nascimento", "identidade_numero");
			foreach ($campos as $campo) {
				if (!$this->_request->getPost($campo)) {
					$flag = false;
					break;
				}
			}
			if ($flag) {
				$flag = false;
				$tb = new TbPessoaFisica();
				$pfs = $tb->listar(array(
					"filtro_cpf" => $this->_request->getPost("cpf"),
					"filtro_nome" => $this->_request->getPost("nome")
				));
				if ($pfs) {
					$pf = $pfs->current();
					$usuarios = TbUsuario::getPorPessoaFisica($pf);
					if ($usuarios) {
						$usuario = $usuarios->current();
						$id_usuario = $usuario->getId();
						$data = new Zend_Date($this->_request->getPost("data_nascimento"));
						$filter = new Zend_Filter_Digits();
						$maiuscula = new Zend_Filter_StringToUpper();
						if ((strtolower($pf->pega_pessoa()->email) == strtolower($this->_request->getPost("email"))) &&
							($pf->data_nascimento == $data->get("Y-MM-dd")) &&
							($filter->filter($pf->identidade_numero) == $filter->filter($this->_request->getPost("identidade_numero")))
						) {
							$rs = $usuario->criaUsuarioSenha();
							if ($rs) {
								$flag = true;
							}
						}
					}
				}
				if ($flag) {
					$this->_redirect("auth/recovery/hash/" . $rs->hash);
				} else {
					$this->view->actionErrors[] = "FALHA AO EXECUTAR OPERAÇÃO, NENHUM USUÁRIO LOCALIZADO!";
				}
			} else {
				$this->view->actionErrors[] = "FALHA AO EXECUTAR OPERAÇÃO, TODOS OS CAMPOS DEVEM SER PREENCHIDOS!";
			}
		}
	}

	public function pacoteAction()
	{
		$usuario = TbUsuario::pegaLogado();
		if ($usuario) {
			$tb = new TbPacote();
			$pacotes = $tb->buscarPacotes($usuario);
			if ($pacotes) {
				$id_pacote = $this->_request->getParams("id_pacote");
				if ($id_pacote) {
					$pacote = $tb->pegaPorId($id_pacote);
					if ($pacote) {
						$session = Escola_Session::getInstance();
						$session->default_id_pacote = $pacote->getId();
						$this->_flashMessage("Pacote Modificado para: <strong>" . $pacote->descricao . "</strong>", "Messages");
						$this->_redirect("intranet/index");
					}
				}
				$this->view->pacotes = $pacotes;
			} else {
				$this->view->actionErrors[] = "Falha ao executar operação, Nenhum pacote disponível!";
				$this->_redirect("auth/logout");
			}
		} else {
			$this->view->actionErrors[] = "Falha ao executar operação, Nenhum usuário logado!";
			$this->_redirect("auth/logout");
		}
	}
}
