<?php
class Funcionario extends Escola_Entidade implements Escola_IAlerta {
    protected $_pessoa_fisica;
    
	public function init() {
		parent::init();
		$this->_pessoa_fisica = $this->getPessoaFisica();
        if (!$this->id_funcionario_situacao) {
            $tb = new TbFuncionarioSituacao();
            $fc = $tb->getPorChave("A");
            if ($fc) {
                $this->id_funcionario_situacao = $fc->getId();
            }
        }
	}
    
    public function set_pessoa_fisica($pf) {
        $this->_pessoa_fisica = $pf;
    }
    
    public function pega_pessoa_fisica() {
        return $this->_pessoa_fisica;
    }
    
	public function setFromArray(array $dados) {
        $maiuscula = new Zend_Filter_StringToUpper();
		if (isset($dados["id_pessoa_fisica"]) && $dados["id_pessoa_fisica"]) {
			$tb = new TbPessoaFisica();
			$this->_pessoa_fisica = $tb->getPorId($dados["id_pessoa_fisica"]);
		}
		$this->_pessoa_fisica->setFromArray($dados);
		if (isset($dados["matricula"])) {
			$dados["matricula"] = $maiuscula->filter($dados["matricula"]);
		}
        if (isset($dados["data_ingresso"])) {
            $data = new Zend_Date($dados["data_ingresso"]);
            $dados["data_ingresso"] = $data->get("Y-MM-dd");
        }
		parent::setFromArray($dados);
	}    
    
	public function getPessoaFisica() {
		$pessoa = $this->findParentRow("TbPessoaFisica");
		if ($pessoa) {
			return $pessoa;
		}
		if ($this->_pessoa_fisica) {
			return $this->_pessoa_fisica;
		}
		$tb = new TbPessoaFisica();
		return $tb->createRow();;
	}
    
    public function getUsuario() {
        $tb = new TbUsuario();
        $usuarios = $tb->getPorPessoaFisica($this->pega_pessoa_fisica());
        if ($usuarios) {
            return $usuarios[0];
        }
        return false;
    }
	
	public function save() {
            $this->id_pessoa_fisica = $this->_pessoa_fisica->save();
            $id = parent::save();
            if ($id) {
                $usuarios = TbUsuario::getPorPessoaFisica($this->_pessoa_fisica);
                if (!$usuarios) {
                    $tb = new TbUsuario();
                    $usuario = $tb->createRow();
                    $usuario->setFromArray(array("id_pessoa_fisica" => $this->_pessoa_fisica->getId()));
                    $usuario->save();
                }
                $tb = new TbGrupo();
                $grupo = $tb->getPorDescricao("FUNCIONÁRIOS");
                if ($grupo) {
                    $usuario = $this->getUsuario();
                    if ($usuario) {
                        $usuario->addGrupo($grupo);
                    }
                }
            }
            return $id;
	}
	
	public function getErrors($flag = true) {
		$msgs = array();
		$err = $this->_pessoa_fisica->getErrors($flag);
		if ($err) {
			$msgs = $err;
		}
		if (empty($this->matricula)) {
			$msgs[] = "CAMPO MATRÍCULA OBRIGATÓRIO!";
		}
		if (empty($this->id_cargo)) {
			$msgs[] = "CAMPO CARGO OBRIGATÓRIO!";
		}
		if (empty($this->id_funcionario_tipo)) {
			$msgs[] = "CAMPO TIPO DE VÍNCULO OBRIGATÓRIO!";
		}
		if (empty($this->id_funcionario_situacao)) {
			$msgs[] = "CAMPO SITUAÇÃO OBRIGATÓRIO!";
		}
        $objs = $this->getTable()->fetchAll("matricula = '{$this->matricula}' && id_funcionario <> " . $this->getId());
        if ($objs && count($objs)) {
            $msgs[] = "MATRÍCULA JÁ CADASTRADA PARA OUTRO FUNCIONÁRIO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function mostrarDataIngresso() {
        $data = new Zend_Date($this->data_ingresso);
        return $data->toString("dd/MM/Y");
    }
    
    public function pegaLotacao($lotacao_tipo = "") {
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("l" => "lotacao"), array("id_lotacao"));
            $sql->join(array("lt" => "lotacao_tipo"), "l.id_lotacao_tipo = lt.id_lotacao_tipo", array());
            $sql->where("l.id_funcionario = " . $this->getId());
            if ($lotacao_tipo) {
                $sql->where("lt.chave = '{$lotacao_tipo}'");
            }
            $sql->order("lt.chave");
            $stmt = $db->query($sql);
            if (count($stmt)) {
                $items = array();
                $rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
                $tb = new TbLotacao();
                foreach ($rg as $obj) {
                    $items[] = $tb->getPorId($obj->id_lotacao);
                }
                return $items;
            }
        }
        return false;
    }
    
    public function pegaLotacaoPrincipal() {
        $lotacaos = $this->pegaLotacao("N");
        if ($lotacaos) {
            return $lotacaos[0];
        }
        return false;
    }
    
    public function delete() {
        $lotacaos = $this->pegaLotacao();
        if ($lotacaos) {
            foreach ($lotacaos as $lotacao) {
                $lotacao->delete();
            }
        }
        parent::delete();
    }
    
    public function toString() {
        $txt = array();
        $txt[] = $this->matricula;
        $pf = $this->pega_pessoa_fisica();
        if ($pf) {
            $txt[] = $pf->nome;
        }
        $cargo = $this->findParentRow("TbCargo");
        if ($cargo) {
            $txt[] = $cargo->toString();
        }
        if (count($txt)) {
            return implode(" - ", $txt);
        }
        return "";
    }
    
    public function mostrarFoto() {
        ob_start();
?>
        <a href="#" rel="<?php echo $this->getId(); ?>" class="janela_funcionario">
            <?php echo $this->pega_pessoa_fisica()->mostrarFoto(); ?>
        </a>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function chefe() {
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("lotacao"), array("count(id_lotacao) as quantidade"));
            $sql->where("id_funcionario = " . $this->getId());
            $sql->where("chefe = 'S'");
            $stmt = $db->query();
            $rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
            if ($rg && count($rg)) {
                return $rg[0]->quantidade;
            }
        }
        return false;
    }
    
    public function pegaArquivoRef() {
        if ($this->getId()) {
            $tb = new TbArquivoRef();
            $sql = $tb->select();
            $sql->where("tipo = 'F'");
            $sql->where("chave = " . $this->getId());
            $rg = $tb->fetchAll($sql);
            if ($rg && count($rg)) {
                return $rg;
            }
        }
        return false;
    }
    
    public function pegaLotacaoAtiva() {
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("l" => "lotacao"), array("id_lotacao"));
            $sql->join(array("lt" => "lotacao_tipo"), "l.id_lotacao_tipo = lt.id_lotacao_tipo", array());
            $sql->where("l.id_funcionario = " . $this->getId());
			$sql->where(" (lt.chave = 'N') or ((data_inicial <= '" . date("Y-m-d") . "' and data_final >= '" . date("Y-m-d") . "')) ");
            $sql->order("lt.chave");
            $stmt = $db->query($sql);
            if (count($stmt)) {
                $items = array();
                $rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
                $tb = new TbLotacao();
                foreach ($rg as $obj) {
                    $items[] = $tb->getPorId($obj->id_lotacao);
                }
                return $items;
            }
        }
        return false;
    }
	
	public function pegaLotacaoAtual() {
		$lotacaos = $this->pegaLotacaoAtiva();
		if (count($lotacaos) == 1) {
			return $lotacaos[0];
		} elseif (count($lotacaos) > 1) {
			$sessao = Escola_Session::getInstance();
			if (isset($sessao->id_lotacao_atual) && $sessao->id_lotacao_atual) {
				$lotacao = TbLotacao::pegaPorId($sessao->id_lotacao_atual);
				if ($this->getId() == $lotacao->findParentRow("TbFuncionario")->getId()) {
					return $lotacao;
				}
			}
            $lotacao = $this->pegaLotacaoPrincipal();
            if ($lotacao) {
                $sessao = Escola_Session::getInstance();
                $sessao->set_lotacao_principal($lotacao);
                return $lotacao;
            }
		}
		return false;
	}
	
	public function pegaDocumentoRef($target = "") {
		$dtt = false;
		if ($target) {
			$tb = new TbDocumentoTipoTarget();
			$dtt = $tb->getPorChave($target);
		}
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("d" => "documento"), array("d.id_documento", "dr.id_documento_ref"));
		$sql->join(array("dr" => "documento_ref"), "d.id_documento = dr.id_documento");
		$sql->join(array("dt" => "documento_tipo"), "d.id_documento_tipo = dt.id_documento_tipo");
		$sql->where("dr.tipo = 'F'");
		$sql->where("dr.chave = " . $this->getId());
		$sql->order("d.data_criacao");
		$sql->order("d.hora_criacao");
		$stmt = $db->query($sql);
		if ($stmt && $stmt->rowCount()) {
			$rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
			$items = array();
			foreach ($rg as $obj) {
				$items[] = TbDocumentoRef::pegaPorId($obj->id_documento_ref);
			}
			return $items;
		}
		return false;
	}
	
	public function pegaOcorrencia() {
		if ($this->getId()) {
			$tb = new TbFuncionarioOcorrencia();
			$sql = $tb->select();
			$sql->where("id_funcionario = " . $this->getId());
			$sql->order("data_inicio");
			$rg = $tb->fetchAll($sql);
			if ($rg && count($rg)) {
				return $rg;
			}
		}
		return false;
	}

	public function pegaOcorrenciaPorData($dt) {
		$data_atual = $dt->get("YYYY-MM-dd");
		if ($this->getId()) {
			$tb = new TbFuncionarioOcorrencia();
			$sql = $tb->select();
			$sql->where("id_funcionario = " . $this->getId());
			$sql->where("data_inicio <= '{$data_atual}' and data_final >= '{$data_atual}'");
			$sql->order("data_inicio");
			$rg = $tb->fetchAll($sql);
			if ($rg && count($rg)) {
				return $rg->current();
			}
		}
		return false;
	}
	
	public function pegaFeriasAtiva() {
		if ($this->getId()) {
			$tb = new TbFuncionarioOcorrenciaTipo();
			$fot = $tb->getPorChave("F");
			if ($fot) {
				$tb = new TbFuncionarioOcorrencia();
				$sql = $tb->select();
				$sql->where("id_funcionario = " . $this->getId());
				$sql->where("id_funcionario_ocorrencia_tipo = " . $fot->getId());
				$sql->where("data_inicio <= '" . date("Y-m-d") . "'");
				$sql->where("data_final >= '" . date("Y-m-d") . "'");
				$sql->order("data_inicio");
				$rg = $tb->fetchAll($sql);
				if ($rg && count($rg)) {
					return $rg->current();
				}
			}
		}
		return false;
	}
	
	public function ativo() {
		$fs = $this->findParentRow("TbFuncionarioSituacao");
		if ($fs) {
			return $fs->ativo();
		}
		return false;
	}
	
	public function mostrarSituacao() {
		$fs = $this->findParentRow("TbFuncionarioSituacao");
		if ($fs) {
			$status = $fs->toString();
			if ($fs->ativo()) {
				$ferias = $this->pegaFeriasAtiva();
				if ($ferias) {
					if ($ferias->data_final == date("Y-m-d")) {
						$data_ferias = "HOJE";
					} else {
						$data_ferias =  Escola_Util::formatData($ferias->data_final);
					}
					$status .= " - DE FÉRIAS ATÉ {$data_ferias}";
				}
			}
			return $status;
		}
		return "";
	}
	
	public function pega_alertas() {
		//ALERTA DE MENSAGEM
		$tb = new TbMensagem();
		$status = $tb->buscarStatus($this);
		$msg = "<a href=";
		$msg .= Escola_Util::url(array("controller" => "mensagem", "action" => "entrada", "id" => $this->getId())); 
		$msg .= ">Você possui {$status["total"]} mensagen(s)";
		if ($status["nao_lidas"]) { 
		  $msg .= "<strong>, {$status["nao_lidas"]} não lida(s)</strong>";
		}
		$msg .= ".</a>";
		$alertas = array();
		$alerta = new Escola_Alerta_Item();
		$alerta->set_titulo("Mensagens");
		$alerta->set_mensagem($msg);
		$alertas[] = $alerta;
		//ALERTA PROTOCOLO
		$docs = TbDocumento::pegaReceber($this);
		if ($docs) {
			$tb = new TbDocumentoStatus();
			$ds = $tb->getPorChave("E");
			if ($ds) {
				$alerta = new Escola_Alerta_Item();
				$alerta->set_titulo("Alertas - Protocolo");
				$alerta->set_mensagem("<strong>Atenção!!!</strong> Você possui <strong>" . count($docs) . " documento(s)</strong> aguardando recebimento no sistema, para receber os documentos, <a href='" . Escola_Util::url(array("controller" => "documento", "action" => "filtro", "page" => "1", "filtro_opcao" => "R")) . "'>CLIQUE AQUI.</a>");
				$alertas[] = $alerta;
			}
		}
		$atrasos = TbDocumento::pegaAtraso($this);
		if ($atrasos) {
			$tb = new TbDocumentoStatus();
			$ds = $tb->getPorChave("E");
			if ($ds) {
				$alerta = new Escola_Alerta_Item();
				$alerta->set_titulo("Alertas - Protocolo");
				$alerta->set_mensagem("<strong>Atenção!!!</strong> Você possui <strong>" . count($atrasos) . " documento(s)</strong> com trâmite em atraso no sistema, para tramitar os documentos, <a href='" . Escola_Util::url(array("controller" => "documento", "action" => "filtro", "page" => "1", "filtro_opcao" => "S", "filtro_id_documento_status" => $ds->getId())) . "'>CLIQUE AQUI.</a>");
				$alertas[] = $alerta;
			}
		}
		//ALERTAS CHAMADOS
		$lotacao = $this->pegaLotacaoAtual();
		if ($lotacao) {
			$chamados = TbChamado::pegaPendentes($this);
			if ($chamados) {
				$alerta = new Escola_Alerta_Item();
				$alerta->set_titulo("Alertas - Chamados");
				$alerta->set_mensagem("<strong>Atenção!!!</strong> Você possui <strong>" . count($chamados). " chamado(s)</strong> pendentes para atendimento pelo seu setor, para atendê-los, <a href='" . Escola_Util::url(array("controller" => "chamado", "action" => "filtro", "page" => "1", "filtro_tipo" => "cx_p")) . "'>CLIQUE AQUI.</a>");
				$alertas[] = $alerta;
			}  
		}
        //ALERTA PROJETOS - VIGENCIA
        $tb = new TbVinculo();
        $sql = $tb->getSql(array("filtro_id_pessoa_fisica" => $this->id_pessoa_fisica));
        $date_final = new Zend_Date();
        $date_final->add("90", Zend_Date::DAY); //NOVENTA DIAS
        $sql->where("data_final < '{$date_final->toString("yyyy-MM-dd")}'");
        $objs = $tb->fetchAll($sql);
        if ($objs && count($objs)) {
            $alerta = new Escola_Alerta_Item();
            $alerta->set_titulo("Alertas - Vínculo - Término de Vigência");
            $mensagem = array("<ul>");
            foreach ($objs as $obj) {
                $obj_df = $obj->pega_data_final();
                $data_final = new DateTime($obj_df->data_final);
                $data_hoje = new DateTime();
                $diff = $data_hoje->diff($data_final);
                $data_final = new Zend_Date($obj_df->data_final);
                $date_hoje = new Zend_Date();
                if ($data_final < $date_hoje) {
                    $mensagem_data = "Está com prazo de vigência <strong>VENCIDO</strong>";
                } elseif ($diff->days < 90) {
                    $mensagem_data = "Está com prazo de vigência de Apenas <strong>{$diff->days}</strong> dias";
                }
                $mensagem[] = "<li>Projeto: <strong><a href='" . Escola_Util::url(array("controller" => "vinculo", "action" => "filtro", "page" => "1", "filtro_codigo" => $obj->codigo, "filtro_ano" => $obj->ano)) . "'>" . $obj->toString() . "</a></strong> {$mensagem_data}.</li>";
            }
            $mensagem[] = "</ul>";
            $alerta->set_mensagem(implode("<br>", $mensagem));
            $alertas[] = $alerta;
        }
        //ALERTA PROJETOS - LOTE
        $tb = new TbVinculoLoteStatus();
        $vls = $tb->getPorChave("AG");
        if ($vls) {
            $tb = new TbVinculoLote();
            $objs = $tb->listar(array("id_vinculo_lote_status" => $vls->getId(),
                                      "id_pessoa_fisica_coordenador" => $this->id_pessoa_fisica));
            if ($objs) {
                $alerta = new Escola_Alerta_Item();
                $alerta->set_titulo("Alertas - Lote de Pagamento");
                $alerta->set_mensagem("<strong>Atenção!!!</strong> Você possui <strong>" . count($objs). " Lotes(s)</strong> Aguardando Aprovação, para aprová-los, <a href='" . Escola_Util::url(array("controller" => "lote", "action" => "filtro", "page" => "1", "id_vinculo_lote_status" => $vls->getId())) . "'>CLIQUE AQUI.</a>");
                $alertas[] = $alerta;
            }
        }
		return $alertas;
	}
}