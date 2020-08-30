<?php
class TbModulo extends Escola_Tabela {
	protected $_name = "modulo";
	protected $_rowClass = "Modulo";
	protected $_dependentTables = array("TbAcao");
	
	public function getPorController($controller) {
            $rg = $this->fetchAll(" controller = '{$controller}' ");
            if (count($rg)) {
                return $rg->current();
            }
            return false;
	}
	
	public function pegaMenus($modulo = null) {
		$id_modulo = " id_modulo_superior is null ";
		if ($modulo) {
			$id_modulo = " id_modulo_superior = $modulo->id_modulo ";
		}
		$sql = $this->select();
		$sql->where($id_modulo);
                $sql->where("status = 'A'");
		//validação do pacote atual
		$tb = new TbPacote();
		$pacote = $tb->pegaAtual();
		if ($pacote && $pacote->ativo()) {
			$db = Zend_Registry::get("db");
			$select = $db->select();
			$select->from(array("mp" => "modulo_pacote"));
			$select->where("mp.id_pacote = {$pacote->getId()}");
			$select->where("mp.id_modulo = modulo.id_modulo");
			$sql->where("exists ({$select})");
		}
		$sql->order("ordem");
		$rg = $this->fetchAll($sql);
		if ($rg->count()) {
			return $rg;
		} 
		return false;
	}
	
	public function geraMenu($modulo = null) {
		$retorno = "";
		$logado = Escola_Acl::getInstance()->getUsuarioLogado();
		if ($logado) {
			$menus = $this->pegaMenus($modulo);
			if ($menus) {
                            $count = 0;
				$retorno .= " <div class='sidebar-nav'>";
				foreach ($menus as $obj) {
                    if ($obj->action) {
                        $action = $obj->action;
                    } else {
                        $acao = $obj->pegaAcaoPrincipal();
                        if ($acao) {
                            $action = $acao->action;
                        } else {
                            $action = "index";
                        }
                    }
					if ($logado->verificaPermissao(array("modulo" => $obj, "action" => $action))) {
						$retorno .= $obj->menu($count);
					}
                                        $count++;
				}
				$retorno .= "</div>";
			}
		}
		return $retorno;
	}
	
	public function listar($dados = array()) {
		$select = $this->select();
        if (isset($dados["filtro_descricao"]) && $dados["filtro_descricao"]) {
            $select->where("descricao like '%{$dados["filtro_descricao"]}%'");
        }
		$select->order("ordem");
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);			
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage(50);
		return $paginator;
	}
	
	public function recuperar() {
        $modulos = array(
            array('descricao' => 'Início','controller' => 'intranet','ordem' => '1','icon' => '','modulo_superior' => NULL),
            array('descricao' => 'Configurações','controller' => '','ordem' => '2','icon' => NULL,'modulo_superior' => NULL),
            array('descricao' => 'Módulos','controller' => 'modulo','ordem' => '4','icon' => 'icon-sitemap','modulo_superior' => 'Configurações'),
            array('descricao' => 'Grupos','controller' => 'grupo','ordem' => '5','icon' => 'icon-group','modulo_superior' => 'Configurações'),
            array('descricao' => 'Usuários','controller' => 'usuario','ordem' => '6','icon' => 'icon-user','modulo_superior' => 'Configurações'),
            array('descricao' => 'Pessoal','controller' => '','ordem' => '7','icon' => '','modulo_superior' => NULL),
            array('descricao' => 'Gestão RH','controller' => '','ordem' => '8','icon' => '','modulo_superior' => NULL),
            array('descricao' => 'Funcionários','controller' => 'funcionario','ordem' => '9','icon' => 'icon-user-md','modulo_superior' => 'Gestão RH'),
            array('descricao' => 'Tabelas Auxiliares','controller' => '','ordem' => '14','icon' => '','modulo_superior' => 'Gestão RH'),
            array('descricao' => 'Setores','controller' => 'setor','ordem' => '15','icon' => 'icon-home','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Cargos','controller' => 'cargo','ordem' => '16','icon' => 'icon-stethoscope','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Funções Gratificadas','controller' => 'funcionariofuncao','ordem' => '17','icon' => 'icon-star-empty','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Log de Operações','controller' => 'log','ordem' => '19','icon' => 'icon-ambulance','modulo_superior' => 'Configurações'),
            array('descricao' => 'Dados Pessoais','controller' => 'pessoal','ordem' => '20','icon' => 'icon-credit-card','modulo_superior' => 'Pessoal'),
            array('descricao' => 'Mensagens','controller' => 'mensagem','ordem' => '21','icon' => 'icon-comments-alt','modulo_superior' => 'Pessoal'),
            array('descricao' => 'Documentos Administrativos','controller' => 'docadm','ordem' => '10','icon' => 'icon-copy','modulo_superior' => 'Gestão RH'),
            array('descricao' => 'Tipos de Documento','controller' => 'documentotipo','ordem' => '30','icon' => 'icon-paste','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Protocolo','controller' => 'documento','ordem' => '11','icon' => 'icon-book','modulo_superior' => 'Gestão RH'),
            array('descricao' => 'Tipo de Ocorrências do Funcionário','controller' => 'funcionarioocorrenciatipo','ordem' => '31','icon' => 'icon-time','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Chamados','controller' => 'chamado','ordem' => '12','icon' => 'icon-ambulance','modulo_superior' => 'Gestão RH'),
            array('descricao' => 'Tipo de Chamado','controller' => 'chamadotipo','ordem' => '32','icon' => 'icon-warning-sign','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Prioridades','controller' => 'prioridade','ordem' => '33','icon' => 'icon-bell','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Configurações','controller' => 'config','ordem' => '18','icon' => 'icon-cogs','modulo_superior' => 'Configurações'),
            array('descricao' => 'Tipos de Relatório','controller' => 'relatoriotipo','ordem' => '34','icon' => 'icon-print','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Relatórios','controller' => 'report','ordem' => '35','icon' => 'icon-print','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Relatórios','controller' => 'relatorio','ordem' => '13','icon' => 'icon-print','modulo_superior' => 'Gestão RH'),
            array('descricao' => 'Pacotes','controller' => 'pacote','ordem' => '3','icon' => 'icon-folder-open-alt','modulo_superior' => 'Configurações'),
            array('descricao' => 'Projetos e Convênios','controller' => '','ordem' => '37','icon' => '','modulo_superior' => NULL),
            array('descricao' => 'Vínculos','controller' => 'vinculo','ordem' => '38','icon' => 'icon-th','modulo_superior' => 'Projetos e Convênios'),
            array('descricao' => 'Tabelas Auxiliares','controller' => '','ordem' => '39','icon' => '','modulo_superior' => 'Projetos e Convênios'),
            array('descricao' => 'Tipos de Vínculo','controller' => 'vinculotipo','ordem' => '40','icon' => 'icon-list-ul','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Bancos','controller' => 'banco','ordem' => '41','icon' => 'icon-money','modulo_superior' => 'Tabelas Auxiliares'),
            array('descricao' => 'Moedas','controller' => 'moeda','ordem' => '42','icon' => 'icon-money','modulo_superior' => 'Tabelas Auxiliares')
        );        
        $items = $this->listar();
		if (!count($items)) {
            foreach ($modulos as $dados) {
                $dados["id_modulo_superior"] = null;
                if ($dados["modulo_superior"]) {
                    $modulo = $this->pegaPorDescricao($dados["modulo_superior"]);
                    if ($modulo) {
                        $dados["id_modulo_superior"] = $modulo->getId();
                    }
                }
                $item = $this->createRow();
                $item->setFromArray($dados);
                $item->save();
            }
		}
	}
	
	public static function pegaUltimaOrdem() {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("modulo"), array("maximo" => "max(ordem)"));
            $rg = $db->query($sql);
            if ($rg && $rg->rowCount()) {
                $obj = $rg->fetch(Zend_Db::FETCH_OBJ);
                return $obj->maximo;
            }
            return 0;
	}
	
	public function pegaPorOrdem($ordem) {
		if ($ordem) {
			$rg = $this->fetchAll("ordem = {$ordem}");
			if ($rg && count($rg)) {
				return $rg->current();
			}
		}
		return false;
	}
	
	public function pegaPorDescricao($descricao) {
		if ($descricao) {
			$rg = $this->fetchAll("descricao = '{$descricao}'");
			if ($rg && count($rg)) {
				return $rg->current();
			}
		}
		return false;
	}
	public function listarTodos() {
		$sql = $this->select();
		$sql->order("descricao");
		$objs = $this->fetchAll($sql);
		if ($objs && count($objs)) {
			return $objs;
		}
		return false;
	}
}