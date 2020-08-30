<?php
class PortalController extends Escola_Controller_Portal {
    
    public function indexAction() {
        $p_contas = $galerias = $destaques = $documentos = false;
        $noticias = array("principal" => false, "secundarias" => array(), "outras" => array());
        $tb = new TbInfoTipo();
        $it = $tb->getPorChave("N");
        if ($it) {
            $tb = new TbInfoStatus();
            $is = $tb->getPorChave("P");
            if ($is) {
                $tb = new TbInfo();
                $nots = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                              "qtd_por_pagina" => 10,
                                              "filtro_id_info_status" => $is->getId()));
                $this->view->noticias = $nots;
                $infos = $tb->listarPorPagina(array("qtd_por_pagina" => 3,
                                              "filtro_id_info_status" => $is->getId(),
                							  "filtro_id_arquivo" => true)); 
                if ($infos) {
                	$contador = 0;
                	foreach ($infos as $not) {
                		$contador++;
                		if ($contador == 1) {
                			$noticias["principal"] = $not;
                		} elseif (($contador == 2) || ($contador == 3)) {
                			$noticias["secundarias"][] = $not;
                		}
                	}
                }
                $destaques = $tb->listarPorPagina(array("qtd_por_pagina" => 4,
                                              "filtro_id_info_status" => $is->getId(),
                                              "filtro_destaque" => "S"));
                $tb_it = new TbInfoTipo();
                $it = $tb_it->getPorChave("G");
                $galerias = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                              "qtd_por_pagina" => 6,
                                              "filtro_id_info_status" => $is->getId(),
                                              "order" => "rand()"));
            }
        }
        $tb = new TbDocumentoTipoTarget();
        $dtt = $tb->getPorChave("W");
        if ($dtt) {
            $tb = new TbDocumento();
            $documentos = $tb->listar_por_pagina(array("qtd_por_pagina" => 5, "filtro_id_documento_tipo_target" => $dtt->getId()));
        }
        $this->view->infos = $noticias;
        $this->view->destaques = $destaques;
        $this->view->galerias = $galerias;
        $this->view->documentos = $documentos;
    }
    
    public function infosAction() {
        $chavetipo = $this->_request->getParam("chavetipo");
        $noticias = false;
        $tb = new TbInfoTipo();
        $it = $tb->getPorChave($chavetipo);
        if ($it) {
            $this->view->infotipo = $it;
            $tb = new TbInfoStatus();
            $is = $tb->getPorChave("P");
            if ($is) {
                $tb = new TbInfo();
                $noticias = $tb->listarPorPagina(array("filtro_id_info_tipo" => $it->getId(),
                                                       "filtro_id_info_status" => $is->getId(),
                                                       "qtd_por_pagina" => 20));
            }
            $this->view->noticias = $noticias;
        } else {
            $this->_flashMessage("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect("portal");
        }
    }
    
    public function viewAction() {
        $nav = Escola_Navegacao::getInstance();
        
        $this->view->fotos = array();
        $this->view->arquivos = array();
        $this->view->comentarios = array();
        $this->view->foto_principal = false;
        $id = $this->_getParam("id");
        $info = TbInfo::pegaPorId($id);
        if ($info) {
            $this->view->info = $info;
            $it = $info->findParentRow("TbInfoTipo");
            if ($it) {
                $nav->add($it->toString(), Escola_Util::url(array("controller" => $this->_request->getControllerName(), "action" => "viewall", "chave" => $it->chave)));
            }
            $nav->add($info->titulo, $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => $this->_request->getActionName(), "id" => $info->getId() )));
            $comentarios = $this->view->info->pegaTbComentario();
            if ($comentarios && count($comentarios)) {
                $this->view->comentarios = $comentarios;
            }
            if ($info->galeria()) {
                $this->view->fotos[] = $info->findParentRow("TbArquivo");
            } else {
                $this->view->foto_principal = $info->findParentRow("TbArquivo");
            }
            $anexos = $info->pegaAnexos();
            if ($anexos && count($anexos)) {
                foreach ($anexos as $anexo) {
                    $arquivo = $anexo->pegaObjeto();
                    if ($arquivo->eImagem()) {
                        $this->view->fotos[] = $arquivo;
                    } else {
                        $this->view->arquivos[] = $arquivo;
                    }
                }
            }
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, PÁGINA INVÁLIDA!");
            $this->_redirect("portal/index");
        }
    }
    
    public function comentarioAction() {
        $dados = $this->_request->getPost();
        $tb = new TbComentario();
        $registro = $tb->createRow();
        $registro->setFromArray($dados);
        $errors = $registro->getErrors();
        if ($errors) {
            $this->_flashMessage(implode("<br>", $errors));
        } else {
            $this->_flashMessage("COMENTÁRIO RECEBIDO COM SUCESSO!", "Messages");
            $this->_flashMessage("AGUARDE A CONFIRMAÇÃO DO SEU COMENTÁRIO!", "Messages");
            $registro->save();
        }
        $this->_redirect($this->_request->getControllerName() . "/viewinfo/id/" . $dados["id_info"]);        
    }
    
    public function searchAction() {
    	$registros = false;
   		$filtros = array("filtro_busca");
   		$sessao = Escola_Session::getInstance();
   		$dados = $sessao->atualizaFiltros($filtros);
    	$tb = new TbInfoStatus();
    	$is = $tb->getPorChave("P");
    	if ($is) {
    		$dados["filtro_id_info_status"] = $is->getId();
    		$dados["qtd_por_pagina"] = 40;
    		$dados["page"] = $this->_request->getParam("page");
    		$tb = new TbInfo();
    		$registros = $tb->listarPorPagina($dados);
    	}
    	$this->view->registros = $registros;
   		$this->view->dados = $dados;
    }
    
    public function protocoloAction() {
   		$filtros = array("filtro_numero", "filtro_ano");
   		$sessao = Escola_Session::getInstance();
   		$dados = $sessao->atualizaFiltros($filtros);
   		if (!$dados["filtro_ano"]) {
   			$dados["filtro_ano"] = date("Y");
   		}
   		$this->view->dados = $dados;
    }
    
    public function viewprotocoloAction() {
    	try {
    		$registro = $tramites = false;
    		$filtros = array("filtro_numero", "filtro_ano");
    		$sessao = Escola_Session::getInstance();
    		$dados = $sessao->atualizaFiltros($filtros);
    		if ($dados["filtro_numero"] && $dados["filtro_ano"]) {
    			$dbemtu = Zend_Registry::get("dbemtu");
    			$sql = $dbemtu->select();
    			$sql->from(array("p" => "protocolo"));
    			$sql->where(" numprotocolo = " . $dados["filtro_ano"] . Escola_Util::zero($dados["filtro_numero"], 6));
    			$stmt = $dbemtu->query($sql);
    			if ($stmt && $stmt->rowCount()) {
    				$registro = $stmt->fetch(Zend_Db::FETCH_OBJ);
    				$sql = $dbemtu->select();
    				$sql->from(array("t" => "protocolotramite"), array("t.despacho", "t.dtentrada"));
    				$sql->join(array("o" => "orgao"), "t.fkorgao = o.id", array("o.descricao"));
    				$sql->where("t.fkprotocolo = {$registro->id}");
    				$sql->order("dtentrada desc");
    				$stmt = $dbemtu->query($sql);
    				if ($stmt && $stmt->rowCount()) {
    					$tramites = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
    				}
    			} else {
    				$this->_flashMessage("PROCESSO NÃO LOCALIZADO!");
    				$this->_redirect("portal/protocolo");
    			}
    		}
    	} catch (Zend_Db_Adapter_Exception $e) {
    		$this->_flashMessage("HOUVE UMA FALHA AO TENTAR CONECTAR A BASE DE DADOS DO PROTOCOLO!");
    		$this->_flashMessage("TENTE NOVAMENTE MAIS TARDE!");
    		$this->_redirect("portal/protocolo");
    	}
    	$this->view->registro = $registro;
    	$this->view->tramites = $tramites;
	}
    
    public function contatoAction(){
        $nav = Escola_Navegacao::getInstance();
        $nav->add("Contato", Escola_Util::url(array("controller" => $this->_request->getControllerName(), "action" => $this->_request->getActionName())));
        if ($this->_request->isPost()) {
            $dados = $this->_request->getPost();
            $campos = array("nome", "email", "telefone", "mensagem");
            $erros = array();
            foreach ($campos as $campo) {
                if (!isset($dados[$campo]) || !$dados[$campo]) {
                    $erros[] = "Preencha Todos os Campos!";
                    break;
                }
            }
            if (count($erros)) {
                $this->view->actionErrors = $erros;
            } else {
                $smtp = TbSmtp::getSmtp();
                if ($smtp->getId()) {
                    $flag = array();
                    $flag["remetente"] = array("nome" => $dados["nome"], "email" => $dados["email"]);
                    $tb = new TbSistema();
                    $sistema = $tb->pegaSistema();
                    $pj = $sistema->findParentRow("TbPessoaJuridica");
                    if ($pj) {
                        $pessoa = $pj->findParentRow("TbPessoa");
                        if ($pessoa) {
                            //$flag["destinatario"] = array("nome" => $pj->razao_social, "email" => $pessoa->email);
                            $flag["destinatario"] = array("nome" => $pj->razao_social, "email" => "francivan.castro@gmail.com");
                        }
                    }
                    ob_start();
?>
<table style=" margin: auto; padding:20px; width: 500px; border:1px solid #c1c1c1; font-size:12px; color:#333333;" >
<tbody>
    <tr>
        <td></td>    
        <td></td>  
    </tr>	
    <tr> 
        <td>                 
            <p>
                <strong>Nome:</strong> <?php echo $dados["nome"]; ?>
                <br><br>
                <strong>Email:</strong> <?php echo $dados["email"]; ?>
                <br><br>
                <strong>Telefone:</strong> <?php echo $dados["telefone"]; ?>
                <br><br>
                <strong>Mensagem:</strong><br><br>
                <?php echo $dados["mensagem"]; ?>
                <br><br>
            </p>
      		    
            <p>FUNPEA</p>  
        </td> 
    </tr>
</tbody>
</table>
<?php

                    $html = ob_get_contents();
                    ob_end_clean();
                    ob_start();
?>
[FALE CONOSCO]\n\n
==========================================================\n
Nome: <?php echo $dados["remetente"]["nome"]; ?>\n
Email: <?php echo $dados["remetente"]["email"]; ?>\n
Telefone: <?php echo $dados["telefone"]; ?>\n
Mensagem: <?php echo $mensagem; ?>\n
==========================================================\n
FUNPEA
<?php

                    $text = ob_get_contents();
                    ob_end_clean();
                    $flag["titulo"] = $sistema->descricao . " - FALE CONOSCO";
                    $flag["conteudo"] = array();
                    $flag["conteudo"]["html"] = $html;
                    $flag["conteudo"]["text"] = $text;
                    $erro = $smtp->sendMail($flag);
                    if ($erro) {
                        $this->view->actionErrors[] = $erro;
                    } else {
                        $this->view->actionMessages[] = "Mensagem Enviada com Sucesso! Aguarde Contato!";
                    }
                } else {
                    $this->view->actionErrors[] = "Nenhuma Configuração de E-mail Informada!";
                }
            }
        }
    }

    public function viewallAction(){
        $session = Escola_Session::getInstance();
        $filtro_busca = $session->filtro_busca;
        if ($this->_request->isPost()) {
            $filtro_busca = $this->_request->getPost("filtro_busca");
            $session->filtro_busca = $filtro_busca;
        }
        $nav = Escola_Navegacao::getInstance();
        $chave = $this->_request->getParam("chave");
        if ($chave) {
            $tb = new TbInfoTipo();
            $it = $tb->getPorChave($chave);
            if ($it) {
                $nav->add($it->toString(), Escola_Util::url(array("controller" => $this->_request->getControllerName(), "action" => $this->_request->getActionName(), "chave" => $it->chave)));
                $tb = new TbInfo();
                $infos = $tb->listarPorPagina(array("pagina_atual" => $this->getParam("page"), 
                                                    "qtd_por_pagina" => 20,
                                                    "filtro_id_info_tipo" => $it->getId(),
                                                    "filtro_busca" => $filtro_busca));
            } else {
                $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
                $this->_redirect($this->_request->getControllerName() . "/index");
            }
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
        $this->view->filtro_busca = $filtro_busca;
        $this->view->infos = $infos;
    }
    public function documentosAction(){
        $nav = Escola_Navegacao::getInstance();
        $nav->add("Documentos", Escola_Util::url(array("controller" => $this->_request->getControllerName(), "action" => $this->_request->getActionName())));
        $session = Escola_Session::getInstance();
        $filtro_resumo = $session->filtro_resumo;
        if($this->_request->isPost()){
            $filtro_resumo = $this->_request->getPost("filtro_resumo");
        }
		$page = $this->_getParam("page");
        $tb = new TbDocumentoTipoTarget();
        $dtt = $tb->getPorChave("W");
        if ($dtt) {
            $tb = new TbDocumento();
            $registros = $tb->listar_por_pagina(array("qtd_por_pagina" => 20,
                                                      "pagina_atual" => $page,
                                                      "filtro_id_documento_tipo_target" => $dtt->getId(),
                                                      "filtro_resumo" => $filtro_resumo));
            if (!$registros) {
                $this->view->actionErrors[] = "Nenhum Documento Disponível!";
            }
            $this->view->filtro_resumo = $filtro_resumo;
            $this->view->registros = $registros;
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect("intranet/index");
        }
    }
    
    public function addcomentarioAction() {
        if ($this->_request->isPost()) {
            $erros = array();
            $id_info = $this->_request->getPost("id_info");
            if (!$id_info) {
                $erros[] = "Nenhuma Informação Definida!";
            }
            $nome = $this->_request->getPost("nome");
            if (!$nome) {
                $erros[] = "Campo Nome Obrigatório!";
            }
            $email = $this->_request->getPost("email");
            if (!$email) {
                $erros[] = "Campo E-mail Obrigatório!";
            }
            $comentario = $this->_request->getPost("comentario");
            if (!$comentario) {
                $erros[] = "Campo Comentário Obrigatório!";
            }
            if (!$erros) {
                $tb = new TbComentario();
                $com = $tb->createRow();
                $com->setFromArray(array("id_info" => $id_info,
                                         "nome" => $nome,
                                         "email" => $email,
                                         "comentario" => $comentario));
                $errors = $com->getErrors();
                if (!$errors) {
                    $id = $com->save();
                    if ($id) {
                        $this->addMensagem("COMENTÁRIO RECEBIDO COM SUCESSO, AGUARDE CONTATO!");
                    } else {
                        $this->addErro("FALHA AO EXECUTAR OPERAÇÃO, TENTE MAIS TARDE!");
                    }
                    $this->_redirect($this->_request->getControllerName() . "/view/id/{$id_info}");
                } else {
                    foreach ($erros as $erro) {
                        $this->addErro($erro);
                    }
                    $this->_redirect($this->_request->getControllerName() . "/view/id/{$id_info}");
                }
            } else {
                foreach ($erros as $erro) {
                    $this->addErro($erro);
                }
                $action = "index";
                if ($id_info) {
                    $action = "view/id/{$id_info}";
                }
                $this->_redirect($this->_request->getControllerName() . "/" . $action);
            }
        } else {
            $this->addErro("FALHA AO EXECUTAR OPERÇÃO, DADOS INVÁLIDOS!");
            $this->_redirect($this->_request->getControllerName() . "/index");
        }
    }
    
    public function diretoriaAction(){
        
    }
    public function conselhoAction(){
        
    }
    public function conselhofiscalAction(){
        
    }
    public function sociosAction(){
        
    }
    public function equipeAction(){
        
    }
    public function index2Action(){
        $this->_helper->layout()->setLayout("site_funpea");
    }
}