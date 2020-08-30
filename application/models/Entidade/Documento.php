<?php
class Documento extends Escola_Entidade {
	
    protected $_arquivo;
    
    public function pegaDocumentoStatus() {
        $obj = $this->findParentRow("TbDocumentoStatus");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }
    

    private function inicializa() {
        if ($this->getId()) {
            return;
        }

        $this->data_criacao = date("Y-m-d");
        $this->hora_criacao = date("H:i:s");
        $this->data_setor_atual = date("Y-m-d");

        $tb = new TbDocumentoStatus();
        $ds = $tb->getPorChave("E");
        if ($ds) {
            $this->id_documento_status = $ds->getId();
        }

        $tb = new TbSetor();
        $instituicao = $tb->pegaInstituicao();
        if ($instituicao) {
            $this->id_setor_procedencia = $instituicao->getId();
        }

        $usuario = TbUsuario::pegaLogado();
        if ($usuario) {
            $tb = new TbFuncionario();
            $funcionario = $tb->getPorUsuario($usuario);
            if ($funcionario) {
                $this->id_funcionario = $funcionario->getId();
                $lotacao = $funcionario->pegaLotacaoAtual();
                if ($lotacao) {
                    $this->id_setor = $lotacao->id_setor;
                    $this->id_setor_atual = $lotacao->id_setor;
                    $this->id_interessado = $lotacao->id_setor;
                    $this->tipo_interessado = "S";
                }
            }
        }

        $tb = new TbPrioridade();
        $p = $tb->getPorChave("N");
        if ($p) {
            $this->id_prioridade = $p->getId();
        }

    }
	
    public function init() {
        $this->inicializa();
        $this->_arquivo = $this->pega_arquivo();
    }
	
	public function pega_arquivo() {
        $id = $this->getId();
        if (!$id) {
            $tb = new TbArquivo();
            return $tb->createRow();
		}
        
        $tb = new TbDocumentoRef();
        $rg = $tb->fetchAll(" tipo = 'arquivo' and id_documento = " . $id);
        if ($rg && $rg->count()) {
            $this->_arquivo = TbArquivo::pegaPorId($rg->current()->chave);
            return $this->_arquivo;
        }

        $tb = new TbArquivo();
        return $tb->createRow();

	}
	
    public function setFromArray(array $dados) {

		if (isset($dados["data_criacao"])) {
			$dados["data_criacao"] = Escola_Util::montaData($dados["data_criacao"]);
        }
        
		if (isset($dados["arquivo"]) && $dados["arquivo"]["size"]) {
			$this->_arquivo->setFromArray(array("legenda" => "ARQUIVO REFERENTE A DOCUMENTO",
												"arquivo" => $dados["arquivo"]));
        }

        if ($cadastro_ano = Escola_Util::valorOuNulo($dados, "cadastro_ano")) {
            $this->ano = $cadastro_ano;
            unset($dados["ano"]);
        }

        parent::setFromArray($dados);

        if (!$this->ano) {
            $this->ano = '0';
        }

        if ($this->numero) {
            $this->numero = Escola_Util::zero($this->numero, 3);
        }
    }
    
    public function processo() {
    	$ds = $this->findParentRow("TbDocumentoTipo");
    	if ($ds) {
    		return $ds->processo();
    	}
    	return false;
    }
	
	public function pegaProximoNumero() {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("d" => "documento"), array("maximo" => "max(numero)"));
		if (!$this->processo()) {
			$sql->where("id_setor = " . $this->id_setor);
		}
		$sql->where("id_documento_tipo = " . $this->id_documento_tipo);
		$sql->where("ano = " . $this->ano);
		$stmt = $db->query($sql);
		if ($stmt && $stmt->rowCount()) {
			$row = $stmt->fetch(Zend_Db::FETCH_OBJ);
			$numero = $row->maximo + 1;
		} else {
			$numero = 1;
		}		
		return Escola_Util::zero($numero, 3);
	}
	
	public function save() {
		$flag = $this->getId();
		if (!$flag && $this->possui_numero()) {
			if (!$this->ano) {
				$this->ano = date("Y");
			}
			if (!$this->numero) {
				$this->numero = $this->pegaProximoNumero();
			}
		}
		if (!$this->id_prioridade) {
			$tb = new TbPrioridade();
			$p = $tb->getPorChave("N");
			if ($p) {
				$this->id_prioridade = $p->getId();
			}
		}
		$id = parent::save();
		if ($this->_arquivo->existe()) {
            $this->_arquivo->legenda = $this->resumo;
			$id_arquivo = $this->_arquivo->save();
			if ($id_arquivo) {
                $this->addArquivo($this->_arquivo);
				// $tb = new TbDocumentoRef();
				// $dr = $tb->createRow();
				// $dr->setFromArray(array("id_documento" => $this->getId(),
				// 						"tipo" => "A",
				// 						"chave" => $id_arquivo));
				// if (!$dr->getErrors()) {
				// 	$dr->save();
				// }
			}
		}
		if (!$flag) {
			$tb = new TbMovimentacaoTipo();
			$mt = $tb->getPorChave("I");
			if ($mt) {
				$tb = new TbMovimentacao();
				$mov = $tb->createRow();
				$mov->setFromArray(array("id_movimentacao_tipo" => $mt->getId(),
										 "id_documento" => $id,
										 "id_funcionario" => $this->id_funcionario,
										 "id_setor" => $this->id_setor));
				$mov->save();
			}
		}
		return $id;
	}
    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_documento_tipo) {
			$msgs[] = "CAMPO TIPO DE DOCUMENTO OBRIGATÓRIO!";
		}
		$dt = $this->findParentRow("TbDocumentoTipo");
		if ($dt) {
			$dtt = $dt->findParentRow("TbDocumentoTipoTarget");
			if ($dtt) {
				if ($this->getId() && $dtt->normal()) {
					if (!trim($this->numero)) {
						$msgs[] = "CAMPO NÚMERO OBRIGATÓRIO!";
					}
					if (!$this->ano) {
						$msgs[] = "CAMPO ANO OBRIGATÓRIO!";
					}
				}
				$dm = $this->findParentRow("TbDocumentoModo");
				if ($dm) {
					$prioridade = $this->findParentRow("TbPrioridade");
					if ($dtt->normal() && $dm->normal() && !$prioridade) {
						$msgs[] = "CAMPO PRIORIDADE É OBRIGATÓRIO PARA O MODO DE DOCUMENTO NORMAL!";
					}
				}
			}
		}
		if ($this->eAdministrativo() || $this->pessoal()) {
			if (!$this->_arquivo->existe()) {
				$msgs[] = "NENHUM ARQUIVO ANEXO ENVIADO!";
			}
        }
        
        if ($this->eAdministrativo()) {
            if ( $this->possui_numero()) {
                if (!$this->numero) {
                    $msgs[] = "CAMPO NÚMERO OBRIGATÓRIO!";
                }
                if (!$this->ano) {
                    $msgs[] = "CAMPO ANO OBRIGATÓRIO!";
                }
            }
            if (!trim($this->localizacao_fisica)) {
                $msgs[] = "CAMPO LOCALIZAÇÃO FÍSICA OBRIGATÓRIO PARA DOCUMENTOS ADMINISTRATIVOS!";
            }
        } 

		if (!$this->pessoal() && !trim($this->resumo)) {
			$msgs[] = "CAMPO RESUMO OBRIGATÓRIO!";
		}
		if (!$this->id_documento_status) {
			$msgs[] = "CAMPO STATUS DE DOCUMENTO OBRIGATÓRIO!";
		}
		if (!$this->id_documento_modo) {
			$msgs[] = "CAMPO MODO DO DOCUMENTO OBRIGATÓRIO!";
		}
		if ($this->possui_numero()) {
			$tb = $this->getTable();
			$sql = $tb->select();
			$sql->where("id_setor = " . $this->id_setor);
			$sql->where("id_documento_tipo = '" . $this->id_documento_tipo . "'");
			$sql->where("ano = '" . $this->ano . "'");
			$sql->where("numero = '{$this->numero}'");
			$sql->where("id_documento <> '" . $this->getId() . "'");
			$rg = $tb->fetchAll($sql);
			if ($rg && count($rg)) {
				$msgs[] = "DOCUMENTO JÁ CADASTRADO!";
			}
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
	
	public function eAdministrativo() {
		$dt = $this->findParentRow("TbDocumentoTipo");
		if ($dt) {
			$dtt = $dt->findParentRow("TbDocumentoTipoTarget");
			if ($dtt) {
				return $dtt->eAdministrativo();
			}
		}
		return false;
	}
	
	public function pessoal() {
		$dt = $this->findParentRow("TbDocumentoTipo");
		if ($dt) {
			$dtt = $dt->findParentRow("TbDocumentoTipoTarget");
			if ($dtt) {
				return $dtt->pessoal();
			}
		}
		return false;
	}
	
	public function mostrarNumero() {
		$dt = $this->findParentRow("TbDocumentoTipo");
		if (!$dt) {
            return "--";
        }
        
        $dtt = $dt->findParentRow("TbDocumentoTipoTarget");
        if (!($dtt && $this->possui_numero())) {
            return "--";
        }

        
        if (!$this->numero) {
            return "--";
        }
        
        $numero = [$this->numero];
        if ($this->ano) {
            $numero[] = $this->ano;
        }

        return implode("/", $numero);
	}
	
    public function delete() {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $e) {
            $trans = false;
        }
        try {
            $drs = $this->pegaDocumentoRef();
            if ($drs) {
                foreach ($drs as $dr) {
                    $dr->delete();
                }
            }
            if ($this->_arquivo && $this->_arquivo->getId()) {
                $this->_arquivo->delete();
            }
            if ($this->getId()) {
                $tb = new TbDocumentoRef();
                $rg = $tb->fetchAll(" id_documento = " . $this->getId());
                if ($rg && count($rg)) {
                    foreach ($rg as $obj) {
                        $obj->delete();
                    }
                }
            }
            $movs = $this->pegaMovimentacao();
            if ($movs) {
                foreach ($movs as $mov) {
                    $mov->delete();
                }
            }
            $rjs = $this->pegaRequerimentoJari();
            if ($rjs) {
                foreach ($rjs as $rj) {
                    $rj->delete();
                }
            }
            $id = parent::delete();
            if ($trans) {
                $db->commit();
            }
            return $id;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            return false;
        }
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $rjp = $this->pegaRequerimentoJariPendente();
        if ($rjp) {
            $msgs[] = "REQUERIMENTO PENDENTE ENCONTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }    
	
	public function pegaFuncionario() {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("dr" => "documento_ref"), array("f.id_funcionario"));
		$sql->from(array("d" => "documento"));
		$sql->from(array("f" => "funcionario"));
		$sql->from(array("pf" => "pessoa_fisica"));
		$sql->where("dr.id_documento = d.id_documento");
		$sql->where("dr.tipo = 'F'");
		$sql->where("dr.chave = f.id_funcionario");
		$sql->where("f.id_pessoa_fisica = pf.id_pessoa_fisica");
		$sql->where("dr.id_documento = " . $this->getId());
		$sql->order("pf.nome");
		$stmt = $db->query($sql);
		if ($stmt->rowCount()) {
			$items = array();
			while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
				$items[] = TbFuncionario::pegaPorId($obj->id_funcionario);
			}
			return $items;
		}
		return false;
	}
	
	public function toString() {
		return $this->findParentRow("TbDocumentoTipo")->toString() . " - " . $this->mostrarNumero() . " - " . $this->mostrarInteressado();
	}
	
	// public function addFuncionario($funcionario) {
	// 	if ($this->getId()) {
	// 		$tb = new TbDocumentoRef();
	// 		$dr = $tb->createRow();
	// 		$dr->setfromArray(array("tipo" => "F",
	// 								"chave" => $funcionario->getId(),
	// 								"id_documento" => $this->getId()));
	// 		if (!$dr->getErrors()) {
	// 			return $dr->save();
	// 		}
	// 	}
	// 	return false;
	// }
	
	// public function addArquivo($arquivo) {
	// 	if ($this->getId()) {
	// 		$tb = new TbDocumentoRef();
	// 		$dr = $tb->createRow();
	// 		$dr->setfromArray(array("tipo" => "A",
	// 								"chave" => $arquivo->getId(),
	// 								"id_documento" => $this->getId()));
	// 		if (!$dr->getErrors()) {
	// 			return $dr->save();
	// 		}
	// 	}
	// 	return false;
	// }
	
	public function excluirFuncionario($funcionario) {
		if ($this->getId()) {
			$tb = new TbDocumentoRef();
			$rg = $tb->fetchAll("id_documento = " . $this->getId() . " and tipo = 'F' and chave = " . $funcionario->getId());
			if ($rg && count($rg)) {
				foreach ($rg as $obj) {
					$obj->delete();
				}
			}
		}
	}
	
	public function pegaDocumentoRef() {
		$tb = new TbDocumentoRef();
		$rg = $tb->fetchAll("id_documento = " . $this->getId());
		if ($rg && count($rg)) {
			return $rg;
		}
		return false;
	}
	
	public function pegaProcedencia() {
		$proc = TbSetor::pegaPorId($this->id_setor_procedencia);
		if ($proc) {
			return $proc;
		}
		return false;
	}
	
	public function mostrarProcedencia() {
		$proc = $this->pegaProcedencia();
		if ($proc) {
			return $proc->toString();
		}
		return "";
	}
        
	public function pegaInteressado() {
            $obj = false;
            switch ($this->tipo_interessado) {
                case "P": $obj = TbPessoaFisica::pegaPorId($this->id_interessado); break;
                case "F": $obj = TbFuncionario::pegaPorId($this->id_interessado); break;
                case "S": $obj = TbSetor::pegaPorId($this->id_interessado); break;
                case "J": $obj = TbPessoaJuridica::pegaPorId($this->id_interessado); break;
            }
            if ($obj) {
                return $obj;
            }
            return false;
	}
        
        public function mostrarInteressado() {
            $obj = $this->pegaInteressado();
            if ($obj) {
                return $obj->toString();
            }
            return "";
        }
	
	public function pegaMovimentacaoPendente() {
		$ds = $this->findParentRow("TbDocumentoStatus");
		if ($ds->possui_principal()) {
			$doc = $this->pegaDocumentoPrincipal();
			if ($doc) {
				return $doc->pegaMovimentacaoPendente();
			}
		} else {
			$tb = new TbMovimentacaoTipo();
			$mt = $tb->getPorChave("E");
			if ($mt) {
				$tb = new TbMovimentacao();
				$sql = $tb->select();
				$sql->where("id_documento = " . $this->getId());
				$sql->where("id_movimentacao_tipo = " . $mt->getId());
				$sql->where("id_movimentacao_recebe = 0 or id_movimentacao_recebe is null ");
				$sql->order("id_movimentacao desc");
				$rg = $tb->fetchAll($sql);
				if ($rg && count($rg)) {
					return $rg->current();
				}
			}
		}
		return false;
	}
	
	public function pegaDocumentoPrincipal() {
		if ($this->getId()) {
			$tb = new TbDocumentoRef();
			$sql = $tb->select();
			$sql->where("chave = " . $this->getId());
			$sql->where("tipo in ('O', 'D')");
			$drs = $tb->fetchAll($sql);
			if ($drs && count($drs)) {
				return $drs->current()->findParentRow("TbDocumento");
			}
		}
		return false;
	}
	
	public function habilitaReceber($funcionario) {
		if ($funcionario) {
			$lotacao = $funcionario->pegaLotacaoAtual();
			$modo = $this->findParentRow("TbDocumentoModo");
			if ($modo->normal()) {
				$ds = $this->findParentRow("TbDocumentoStatus");
				if ($ds->aguardando()) {
					$mov = $this->pegaMovimentacaoPendente();
					if ($mov) {
						if ($mov->destinoSetor()) {
							if ($lotacao && ($mov->id_destino == $lotacao->id_setor)) {
								return true;
							}
						} elseif ($mov->destinoFuncionario() && ($mov->id_destino == $funcionario->getId())) {
							return true;
						}
					} 
/*				} elseif ($ds->possui_principal()) {
					$doc = $this->pegaDocumentoPrincipal();
					if ($doc) {
						return $doc->habilitaReceber($funcionario);
					} */
				}
			} elseif ($modo->circular()) {
				$tb = new TbMovimentacaoTipo();
				$mt = $tb->getPorChave("R");
				$tb = new TbMovimentacao();
				$sql = $tb->select();
				$sql->where("id_documento = " . $this->getId());
				$sql->where("id_movimentacao_tipo = " . $mt->getId());
				$sql->where("id_setor = " . $lotacao->id_setor);
				$rg = $tb->fetchAll($sql);
				if (!$rg || !count($rg)) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function habilitaEncaminhar($funcionario) {
		if ($funcionario) {
			if ($this->findParentRow("TbDocumentoModo")->normal() && $this->findParentRow("TbDocumentoStatus")->em_tramite()) {
				$lotacao = $funcionario->pegaLotacaoAtual();
				if ($lotacao && ($this->id_setor_atual == $lotacao->id_setor)) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function habilitaArquivar($funcionario) {
		return $this->habilitaEncaminhar($funcionario);
	}
	
	public function habilitaCancelarArquivar($funcionario) {
		if ($funcionario) {
			if ($this->findParentRow("TbDocumentoStatus")->arquivado()) {
				$lotacao = $funcionario->pegaLotacaoAtual();
				if ($lotacao && ($this->id_setor_atual == $lotacao->id_setor)) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function habilitaAlterar($funcionario) {
		if ($funcionario) {
			$dt = $this->findParentRow("TbDocumentoTipo");
			if (!$dt->processo() && $this->findParentRow("TbDocumentoStatus")->em_tramite()) {
				$lotacao = $funcionario->pegaLotacaoAtual();
				if ($lotacao && ($this->id_setor == $lotacao->id_setor) && ($this->id_setor_atual == $lotacao->id_setor)) {
					$movs = $this->pegaMovimentacao();
					if (!$movs || (count($movs) <= 1)) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	public function pegaMovimentacao() {
		if ($this->getId()) {
			$tb = new TbMovimentacao();
			$sql = $tb->select();
			$sql->where("id_documento = " . $this->getId());
			$sql->order("data_movimentacao");
			$sql->order("hora_movimentacao");
			$rg = $tb->fetchAll($sql);
			if ($rg && count($rg)) {
				return $rg;
			}
		}
		return false;
	}
	
	public function encaminhar($dados) {
		$dados["id_documento"] = $this->getId();
		$tb = new TbMovimentacao();
		$mov = $tb->createRow();
		$mov->setFromArray($dados);
		$errors = $mov->getErrors();
		if ($errors) {
			return false;
		}
		$id = $mov->save();
		if (!$id) {
			return false;
		}
		$tb = new TbDocumentoStatus();
		$ds = $tb->getPorChave("R");
		$this->id_documento_status = $ds->getId();
		$this->save();
		return true;
	}
	
	public function pegaTbDocumento() {
		if ($this->getId()) {
			$tb = new TbDocumentoRef();
			$sql = $tb->select();
			$sql->from(array("dr" => "documento_ref"));
			$sql->join(array("d" => "documento"), "dr.id_documento = d.id_documento", array());
			$sql->where("dr.id_documento = " . $this->getId());
			$sql->where("dr.tipo in ('O', 'D')");
			$sql->order("d.data_criacao");
			$sql->order("d.hora_criacao");
			$drs = $tb->fetchAll($sql);
			if ($drs && count($drs)) {
				return $drs;
			}
		}
		return false;
	}
	
	public function pegaAnexos() {
		if ($this->getId()) {
			$tb = new TbDocumentoRef();
			$sql = $tb->select();
			$sql->from(array("dr" => "documento_ref"));
			$sql->join(array("d" => "documento"), "dr.id_documento = d.id_documento", array());
			$sql->where("dr.id_documento = " . $this->getId());
			$sql->where("dr.tipo in ('A')");
			$sql->order("d.data_criacao");
			$sql->order("d.hora_criacao");
			$drs = $tb->fetchAll($sql);
			if ($drs && count($drs)) {
				return $drs;
			}
		}
		return false;
	}
	
	public function receber($funcionario) {
        if ($this->habilitaReceber($funcionario)) {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            try {
				$pendente = $this->pegaMovimentacaoPendente();
				$modo = $this->findParentRow("TbDocumentoModo");
				if ($modo->circular() || $pendente) {
					$tb = new TbMovimentacaoTipo();
					$mt = $tb->getPorChave("R");
					if ($mt) {
						$lotacao = $funcionario->pegaLotacaoAtual();
						$dados = array("id_movimentacao_tipo" => $mt->getId(),
									   "id_documento" => $this->getId(),
									   "id_funcionario" => $funcionario->getId(),
									   "id_setor" => $lotacao->id_setor);
						$tb = new TbMovimentacao();
						$mov = $tb->createRow();
						$mov->setFromArray($dados);
						$id = $mov->save();
						if ($id) {
							$tb = new TbDocumentoStatus();
							$ms = $tb->getPorChave("E");
							if ($ms) {
								$ds = $this->findParentRow("TbDocumentoStatus");
								if (!$ds->processo() && !$ds->vinculado()) {
									$this->id_documento_status = $ms->getId();
								}
								if (!$ds->possui_principal() && $pendente) {
									$pendente->id_movimentacao_recebe = $id;
									$pendente->save();
								}
								$this->id_setor_atual = $lotacao->id_setor;
								$this->data_setor_atual = date("Y-m-d");
								$this->save();
								$anexos = $this->pegaTbDocumento();
								if ($anexos) {
									foreach ($anexos as $anexo) {
										$doc = $anexo->pegaObjeto();
										if ($doc) {
											$doc->movimentacao_original(array("doc_original" => $this, "funcionario" => $funcionario, "lotacao" => $lotacao));
										}
									}
								}
                                $db->commit();
								return true;
							}
						}
					}
				}
            } catch (Exception $e) {
                die($e->getMessage());
                $db->rollBack();
			}
		}
		return false;
	}
    
    public function movimentacao_original($dados = array()) {
        if ((isset($dados["doc_original"]) && $dados["doc_original"]) && (isset($dados["funcionario"]) && $dados["funcionario"])) {
            $doc = $dados["doc_original"];
            $funcionario = $dados["funcionario"];
            $tb = new TbMovimentacaoTipo();
            $mt = $tb->getPorChave("MO");
            $lotacao = false;
            if (isset($dados["lotacao"]) && $dados["lotacao"]) {
                $lotacao = $dados["lotacao"];
            }
            if ($mt) {
                $dados = array("id_movimentacao_tipo" => $mt->getId(),
                                "id_documento" => $this->getId(),
                                "id_funcionario" => $funcionario->getId(),
                                "id_setor" => $lotacao->id_setor);
                $tb = new TbMovimentacao();
                $mov = $tb->createRow();
                $mov->setFromArray($dados);
                $mov->save();
                $this->id_setor_atual = $doc->id_setor_atual;
                $this->data_setor_atual = date("Y-m-d");
                $this->save();
            }
        }
    }
	
	public function pegaSetorAtual() {
		if ($this->id_setor_atual) {
			$setor = TbSetor::pegaPorId($this->id_setor_atual);
			if ($setor) {
				return $setor;
			}
		}
		return false;
	}	
	
	public function arquivar($dados = array()) {
            $trans = true;
            $db = Zend_Registry::get("db");
            try {
                $db->beginTransaction();
            } catch (Exception $ex) {
                $trans = false;
            }
            try {
                if ($this->arquivado()) {
                    throw new Exception("FALHA AO EXECUTAR ARQUIVAMENTO, DOCUMENTO JÁ ARQUIVADO!");
                }
                $tb_mt = new TbMovimentacaoTipo();
                $mt = $tb_mt->getPorChave("A");
                if ($mt) {
                    $dados["id_movimentacao_tipo"] = $mt->getId();
                }
                if (!$dados["id_funcionario"]) {
                    $tb_f = new TbFuncionario();
                    $funcionario = $tb_f->pegaLogado();
                    if ($funcionario) {
                        $dados["id_funcionario"] = $funcionario->getId();
                    }
                }
                $dados["id_documento"] = $this->getId();
                $tb = new TbMovimentacao();
                $mov = $tb->createRow();
                $mov->setFromArray($dados);
                $errors = $mov->getErrors();
                if ($errors) {
                    throw new Exception(implode("<br>", $errors));
                }
                $id = $mov->save();
                if (!$id) {
                    throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DOCUMENTO NÃO ARQUIVADO!");
                }
                $tb = new TbDocumentoStatus();
                $ds = $tb->getPorChave("A");
                if ($ds) {
                    $this->id_documento_status = $ds->getId();
                }
                $this->save();
                if ($trans) {
                    $db->commit();
                }
                return true;
            } catch (Exception $ex) {
                if ($trans) {
                    $db->rollBack();
                }
                throw $ex;
            }
	}
	
	public function cancelar_arquivar($dados = array()) {
            $in_transaction = true;
            $db = Zend_Registry::get("db");
            try {
                $db->beginTransaction();
            } catch (Exception $ex) {
                $in_transaction = false;
            }
            try {
                $tb_mt = new TbMovimentacaoTipo();
                $mt = $tb_mt->getPorChave("C");
                if ($mt) {
                    $dados["id_movimentacao_tipo"] = $mt->getId();
                }
                if (!$dados["id_funcionario"]) {
                    $tb_f = new TbFuncionario();
                    $funcionario = $tb_f->pegaLogado();
                    if ($funcionario) {
                        $dados["id_funcionario"] = $funcionario->getId();
                    }
                }
                if (!isset($dados["id_setor"])) {
                    $setor = $this->pegaSetorAtual();
                    if ($setor) {
                        $dados["id_setor"] = $setor->getId();
                    }
                }
                $dados["id_documento"] = $this->getId();
                $tb = new TbMovimentacao();
                $mov = $tb->createRow();
                $mov->setFromArray($dados);
                $errors = $mov->getErrors();
                if ($errors) {
                    throw new Exception(implode("<br>", $errors));
                }
                $id = $mov->save();
                if (!$id) {
                    throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, CANCELAMENTO DE ARQUIVAMENTO FALHOU!");
                }
                $tb = new TbDocumentoStatus();
                $ds = $tb->getPorChave("E");
                $this->id_documento_status = $ds->getId();
                $this->save();
                if ($in_transaction) {
                    $db->commit();
                }
                return true;
            } catch (Exception $ex) {
                if ($in_transaction) {
                    $db->rollBack();
                }
                throw $ex;
            }
	}
	
	public function possui_numero() {
		$dt = $this->findParentRow("TbDocumentoTipo");
		if ($dt) {
			$dtt = $dt->findParentRow("TbDocumentoTipoTarget");
			if ($dtt && $dtt->normal()) {
				return true;
			}
			return $dt->possui_numero();
		}
		return false;
	}
	
	public function habilitaTornarProcesso($funcionario) {
		if ($funcionario) {
			if ($this->findParentRow("TbDocumentoStatus")->em_tramite()) {
				$dt = $this->findParentRow("TbDocumentoTipo");
				$dm = $this->findParentRow("TbDocumentoModo");
				if ($dt && !$dt->processo() && $dm->normal()) {
					$lotacao = $funcionario->pegaLotacaoAtual();
					if ($lotacao && ($this->id_setor_atual == $lotacao->id_setor)) {
						$setor = $lotacao->findParentRow("TbSetor");
						if ($setor && $setor->protocolo()) {
							return true;						
						}
					}
				}
			}
		}
		return false;
	}
	
	public function tornar_processo($dados) {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            $dados["id_documento"] = $this->getId();
            $tb = new TbMovimentacaoTipo();
            $mt = $tb->getPorChave("T");
            if ($mt) {
                $dados["id_movimentacao_tipo"] = $mt->getId();
            }
            $tb = new TbMovimentacao();
            $mov = $tb->createRow();
            $mov->setFromArray($dados);
            $errors = $mov->getErrors();
            if ($errors) {
                return false;
            }
            $id = $mov->save();
            if (!$id) {
                return false;
            }
            $tb = new TbDocumentoStatus();
            $ds = $tb->getPorChave("P");
            if ($ds) {
                $flag = $this->toArray();
                unset($flag["id_documento"]);
                unset($flag["data_criacao"]);
                unset($flag["hora_criacao"]);
                unset($flag["numero"]);
                unset($flag["ano"]);
                $flag["ano"] = $dados["ano"];
                $flag["numero"] = $dados["numero"];
                $this->id_documento_status = $ds->getId();
                $this->save();
                $tb = new TbDocumentoTipo();
                $dt = $tb->getPorChave("P");
                if ($dt) {
                    $flag["id_documento_tipo"] = $dt->getId();
                    $tb = new TbDocumentoStatus();
                    $ds = $tb->getPorChave("E");
                    if ($ds) {
                        $flag["id_documento_status"] = $ds->getId();
                    }
                    $tb = new TbDocumento();
                    $doc = $tb->createRow();
                    $doc->setFromArray($flag);
                    $id = $doc->save();
                    $tb = new TbDocumentoRef();
                    $dr = $tb->createRow();
                    $dr->setFromArray(array("id_documento" => $doc->getId(),
                                            "tipo" => "O",
                                            "chave" => $this->getId()));
                    $dr->save();
                    $db->commit();
                    return $doc;
                }
            }
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
		return false;
	}
	
	public function pegaDocumentoOriginal() {
		if ($this->getId()) {
			$tb = new TbDocumentoRef();
			$sql = $tb->select();
			$sql->where("id_documento = " . $this->getId());
			$sql->where("tipo = 'O'");
			$drs = $tb->fetchAll($sql);
			if ($drs && count($drs)) {
				return $drs->current()->pegaObjeto();
			}
		}
		return false;
	}
	
	public function pegaProcesso() {
		if ($this->getId()) {
			$tb = new TbDocumentoRef();
			$sql = $tb->select();
			$sql->where("chave = " . $this->getId());
			$sql->where("tipo = 'O'");
			$drs = $tb->fetchAll($sql);
			if ($drs && count($drs)) {
				return $drs->current()->findParentRow("TbDocumento");
			}
		}
		return false;
	}
	
	/*
	 * Retorna a data em que o documento deu entrada no setor atual.
	 */
	public function pegaDataEntrada() {
		$data = false;
		//verifica se a data atual é válida
		$filter = new Zend_Validate_Date();
		if ($filter->isValid($this->data_setor_atual)) {
			$data = $this->data_setor_atual;
		} else {
			//caso não seja válida, procura a data da última movimentação
			$data = $this->data_criacao;
			$movs = $this->pegaMovimentacao();
			if ($movs) {
				for ($i = count($movs) - 1; $i >= 0; $i--) {
					$mt = $movs[$i]->findParentRow("TbMovimentacaoTipo");
					if ($mt->criacao() || $mt->recebimento()) {
						$data = $movs[$i]->data_movimentacao;
					}
				}
			}
		}
		if (!$this->data_setor_atual && $data) {
			$this->data_setor_atual = $data;
			$this->save();
		}
		return $data;
	}
	
	/*
	 * Calcula o tempo que um documento está no setor atual
	 */
	public function pegaTempoSetor() {
		$str_data = $this->pegaDataEntrada();
		$data = new DateTime($str_data);
		$agora = new DateTime("now");
		$interval = $agora->diff($data);
		return $interval->days;
	}
	
	public function mostrarTempoSetor() {
		$texto = "NENHUM DIA";
		$tempo = $this->pegaTempoSetor();
		if ($tempo == 1) {
			$texto = "UM DIA";
		} elseif ($tempo) {
			$texto = $tempo . " DIAS";
		} 
		if ($this->fora_do_prazo()) {
			$texto .= " - PRAZO EXTRAPOLADO";
		}
		return $texto;
	}
	
	/*
	 * Verifica se o documento é CIRCULAR
	 */
	public function circular() {
		$dm = $this->findParentRow("TbDocumentoModo");
		if ($dm) {
			return $dm->circular();
		}
		return false;
	}
	
	/*
	 * Verifica se o documento está com o status EM TRAMITE
	 */
	public function em_tramite() {
		$ds = $this->findParentRow("TbDocumentoStatus");
		if ($ds) {
			return $ds->em_tramite();
		}
		return false;
	}
	
	/*
	 * Verifica se o Documento ultrapassou o período para estada no setor atual
	 */
	public function fora_do_prazo() {
		if (!$this->circular() && $this->em_tramite()) {
			$prioridade = $this->findParentRow("TbPrioridade");
			if ($prioridade) {
				$tempo = $this->pegaTempoSetor();
				if ($tempo > $prioridade->tolerancia) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function possui($documento) {
		$anexos = $this->pegaTbDocumento();
		if ($anexos) {
			foreach ($anexos as $anexo) {
				if ($anexo->chave == $documento->getId()) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function vinculado() {
		$ds = $this->findParentRow("TbDocumentoStatus");
		if ($ds) {
			return ($ds->processo() || $ds->vinculado());
		}		
		return false;
	}
	
	public function addDocumento($anexo) {
		$tb = new TbDocumentoStatus();
		$ds = $tb->getPorChave("V");
		if ($ds) {
			$tb = new TbMovimentacaoTipo();
			$mt = $tb->getPorChave("V");
			if ($mt) {
				$tb = new TbFuncionario();
				$funcionario = $tb->pegaLogado();
				if ($funcionario) {
					$lotacao = $funcionario->pegaLotacaoAtual();
					if ($lotacao) {
						$dados = array("id_movimentacao_tipo" => $mt->getId(),
									   "id_documento" => $anexo->getId(),
									   "id_funcionario" => $funcionario->getId(),
									   "id_setor" => $lotacao->id_setor);
						$tb = new TbMovimentacao();
						$mov = $tb->createRow();
						$mov->setFromArray($dados);
						$id = $mov->save();
					}
				}
			}
			$anexo->id_documento_status = $ds->getId();
			$anexo->save();
			$tb = new TbDocumentoRef();
			$dr = $tb->createRow();
			$dr->setFromArray(array("id_documento" => $this->getId(),
									"tipo" => "D",
									"chave" => $anexo->getId()));
			$errors = $dr->getErrors();
			if (!$errors) {
				$dr->save();
				$anexos = $anexo->pegaTbDocumento();
				if ($anexos) {
					foreach ($anexos as $ane) {
						$doc = $ane->pegaObjeto();
						$this->addDocumento($doc);
					}
				}
			}
			return true;
		}
		return false;
	}	
	
	// public function addTransporte($transporte) {
	// 	if (!$this->getId()) {
    //         return false;
	// 	}
    //     $tb = new TbDocumentoRef();
    //     $dr = $tb->createRow();
    //     $dr->setfromArray(array("tipo" => "T",
    //                             "chave" => $transporte->getId(),
    //                             "id_documento" => $this->getId()));
    //     if ($dr->getErrors()) {
    //         return false;
    //     }
    //     return $dr->save();
	// }
	
	// public function addBolsista($bolsista) {
	// 	if ($this->getId()) {
    //                 $tb = new TbDocumentoRef();
    //                 $dr = $tb->createRow();
    //                 $dr->setfromArray(array("tipo" => "B",
    //                                         "chave" => $bolsista->getId(),
    //                                         "id_documento" => $this->getId()));
    //                 if (!$dr->getErrors()) {
    //                     return $dr->save();
    //                 }
	// 	}
	// 	return false;
	// }
        
    public function toForm(Zend_View_Abstract $view, $funcionario) {
        
        if (!$this->id_documento_tipo) {
            return "";
        }
        
        $lotacao = $setor = false;
        if ($funcionario) {
            $lotacao = $funcionario->pegaLotacaoAtual();
            if ($lotacao) {
                $setor = $lotacao->findParentRow("TbSetor");
            }
        }
        
        ob_start();
?>
<script type="text/javascript">
var ajax_obj = false;
$(document).ready(
	function() {
		$("#formulario").submit(
			function() {
				if ($("#operacao").val() == "destino") {
					procurarDestino();
					return false;
				} else if ($("#operacao").val() == "listar_procedencia") {
					listarProcedencia();
					return false;
				} else if ($("#operacao").val() == "procedencia_novo") {
					salvarProcedencia();
					return false;
				} else if ($("#operacao").val() == "listar_interessado") {
					listarInteressado();
					return false;
				} else if ($("#operacao").val() == "interessado_novo") {
					salvarInteressado();
					return false;
				}
				return true;
			}
		);
        $("#link_procedencia").click(function(event) {
            event.preventDefault();
            $("#janela_procedencia_busca").modal("show");
        });
        $("#janela_procedencia_busca, #janela_interessado_busca").css( { "width": "900px", "margin-left": "-450px" } ).modal("hide");
        $("#janela_procedencia_busca").on("show", function() {
            $("#operacao").val("listar_procedencia");
            $("#jan_pagina").val("");
            listarProcedencia();
        });
        $("#janela_procedencia_busca").on("hide", function() {
            $("#operacao").val("");
        });
        $("#janela_procedencia_busca").on("shown", function() {
            $("#jan_procedencia_criterio").focus();
        });
        $("#janela_procedencia_busca").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $("#jan_pagina").val("");
                listarProcedencia();
            }
        });
        $("#bt_procedencia_limpar").click(function() {
            $("#jan_pagina, #jan_procedencia_criterio").val("");
            listarProcedencia();
        });
        $("#janela_procedencia_novo, #janela_interessado_novo").css( { "width": "800px", "margin-left": "-400px" } ).modal("hide");
        $("#janela_procedencia_novo").on("show", function() {
            $("#janela_procedencia_novo .field").val("");
            $("#operacao").val("procedencia_novo");
            $(".procedencia_erro").hide();
        });
        $("#janela_procedencia_novo").on("shown", function() {
            $("#janela_procedencia_novo .field").first().focus();
        });
        $("#janela_procedencia_novo").on("hide", function() {
            $("#operacao").val("");
        });
        $("#bt_procedencia_novo").click(function() {
            $("#janela_procedencia_busca").modal("hide");
            $("#janela_procedencia_novo").modal("show");
        });
        $("#janela_procedencia_novo").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                salvarProcedencia();
            }
        });
        $("#link_interessado").click(function(event) {
            event.preventDefault();
            $(".option_funcionario").show();
<?php
$tb = new TbSetor();
$pms = $tb->pegaInstituicao();
if ($pms) { ?>
            if ($("#id_setor_procedencia").val() != '<?php echo $pms->getId(); ?>') {
                $(".option_funcionario").hide();
            }
<?php } ?>            
            $("#janela_interessado_busca").modal("show");
        });
        $("#janela_interessado_busca").on("show", function() {
            $("#operacao").val("listar_interessado");
            $("#jan_tipo_interessado, #jan_pagina").val("");
            listarInteressado();
        });
        $("#janela_interessado_busca").on("hide", function() {
            $("#operacao").val("");
        });
        $("#janela_interessado_busca").on("shown", function() {
            $("#jan_interessado_criterio").focus();
        });
        $("#janela_interessado_busca").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $("#jan_pagina").val("");
                listarInteressado();
            }
        });
        $("#janela_interessado_novo").on("show", function() {
            $("#janela_interessado_novo .field").val("");
            $("#operacao").val("interessado_novo");
        });
        $("#janela_interessado_novo").on("hide", function() {
            $("#operacao").val("");
        });
        $("#janela_interessado_novo").on("shown", function() {
            switch ($("#jan_tipo_interessado").val()) {
                case "P": $("#interessado_cpf").focus(); break;
                case "J": $("#interessado_cnpj").focus(); break;
                case "S": $("#interessado_sigla").focus(); break;
            }
        });
        $("#bt_interessado_limpar").click(function() {
            $("#jan_pagina, .field_pessoa .filtro, .field_funcionario .filtro, .field_setor .filtro").val("");
            listarInteressado();
        });
        $("#jan_tipo_interessado").change(function() {
            $("#tipo_interessado").val($(this).val());
            listarInteressado();
        });
        $("#bt_interessado_novo").click(function() {
            $(".interessado_erro").hide();
            $("#janela_interessado_busca").modal("hide");
            $("#janela_interessado_novo").modal("show");
        });
        $("#janela_interessado_novo").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                salvarInteressado();
            }
        });
        $("#ck_processo").change(function() {
            $("#processo").hide();
            if ($(this).attr("checked")) {
                $("#processo").show();
            }
        });

        $(".arquivo").live("change", function() {
            var flag = true;
            $(".arquivo").each(function(idc, dom) {
                if ($(this).val().length <= 0) {
                    flag = false;
                    return false;
                }
            });
            if (flag) {
                var ultimo = $(".linha_arquivo").last();
                var clone = ultimo.clone();
                var quant = $(".linha_arquivo").length;
                clone.addClass("clone");
                clone.find(".arquivo, .legenda").val("");
                clone.find(".arquivo").attr( { "id": "arquivo_" + (quant + 1), "name": "arquivo_" + (quant + 1) });
                clone.find(".legenda").attr( { "id": "legenda_" + (quant + 1), "name": "legenda_" + (quant + 1) });
                ultimo.after(clone);
            }
        });
        $("#btn_limpar_arquivo").click(function() {
            $(".clone").remove();
            $(".arquivo").val("");
        });
    }
);

function salvarProcedencia() {
    $(".procedencia_erro").hide();
    if (ajax_obj) {
        ajax_obj.abort();
    }
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/setor/salvar/format/json/",
        "type" : "POST",
        "data" : { "sigla": $("#procedencia_sigla").val(),
                   "descricao" : $("#procedencia_descricao").val() },
        "success" : function(result) {
            if (result.erro) {
                $(".procedencia_erro .mensagem_erro").html(result.erro);
                $(".procedencia_erro").show();
                $("#janela_procedencia_novo .field").first().focus();
            } else {
                $("#id_setor_procedencia").val(result.id);
                $("#show_procedencia").val(result.procedencia);
                $("#janela_procedencia_novo").modal("hide");
            }
        }
    });    
}

function listarProcedencia() {
    var procedencias = [];
    $(".corpo_procedencia tr, .procedencia_paginacao").remove();
    if (ajax_obj) {
        ajax_obj.abort();
    }
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/setor/instituicaoporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_criterio": $("#jan_procedencia_criterio").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    procedencias[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    tr.appendTo($(".corpo_procedencia"));
                    $('<td><a href="#" id="' + i + '" class="link_procedencia">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_procedencia">' + item.sigla + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_procedencia">' + item.descricao + '</a></td>').appendTo(tr);
                }
                $(".link_procedencia").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_procedencia").val(procedencias[$(this).attr("id")].sigla + " - " + procedencias[$(this).attr("id")].descricao);
                        $("#id_setor_procedencia").val(procedencias[$(this).attr("id")].id);
                        $("#janela_procedencia_busca").modal("hide");
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="procedencia_paginacao">' + html + '</div>').appendTo($("#janela_procedencia_busca .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($(".corpo_procedencia"));
            }
        }
    });    
}

function listarInteressado() {
    $(".field_funcionario, .field_pessoa, .field_setor, #lista_interessado_resposta, .interessado_btn, .dl-procedencia, #bt_interessado_novo").hide();
    $(".corpo_interessado tr, .interessado_paginacao, #lista_interessado_resposta thead tr").remove();
    if ($("#jan_tipo_interessado").val().length) {
        $("#lista_interessado_resposta, .interessado_btn").show();
        switch ($("#jan_tipo_interessado").val()) {
            case "P": 
                atualizaPessoa();
                break;
            case "J": 
                atualizaPessoaJuridica();
                break;
            case "F": 
                atualizaFuncionario();
                break;
            case "S": 
                atualizaSetor();
                break;
        }
    }
}

function atualizaPessoa() {
    var interessados = [];
    $(".field_pessoa, #bt_interessado_novo").show(); 
    $('<tr><th width="50px">ID</th><th width="100px">C.P.F.</th><th>Nome</th></tr>').appendTo($("#lista_interessado_resposta thead"));
    if (ajax_obj) {
        ajax_obj.abort();
    }    
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_cpf": $("#filtro_cpf").val(),
                   "filtro_nome": $("#filtro_nome").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    interessados[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    tr.appendTo($(".corpo_interessado"));
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.cpf + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.nome + '</a></td>').appendTo(tr);
                }
                $(".link_interessado").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_interessado").val(interessados[$(this).attr("id")].cpf + " - " + interessados[$(this).attr("id")].nome);
                        $("#id_interessado").val(interessados[$(this).attr("id")].id);
                        $("#janela_interessado_busca").modal("hide");
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="interessado_paginacao">' + html + '</div>').appendTo($("#janela_interessado_busca .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($(".corpo_interessado"));
            }
        }
    });
}

function atualizaPessoaJuridica() {
    var interessados = [];
    $(".field_pessoa_juridica, #bt_interessado_novo").show(); 
    $('<tr><th width="50px">ID</th><th width="100px">C.N.P.J.</th><th>Nome Fantasia</th></tr>').appendTo($("#lista_interessado_resposta thead"));
    if (ajax_obj) {
        ajax_obj.abort();
    }    
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/pjlistarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_cnpj": $("#filtro_cnpj").val(),
                   "filtro_nome": $("#filtro_nome").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    interessados[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    tr.appendTo($(".corpo_interessado"));
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.cnpj + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.nome_fantasia + '</a></td>').appendTo(tr);
                }
                $(".link_interessado").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_interessado").val(interessados[$(this).attr("id")].cnpj + " - " + interessados[$(this).attr("id")].nome_fantasia);
                        $("#id_interessado").val(interessados[$(this).attr("id")].id);
                        $("#janela_interessado_busca").modal("hide");
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="interessado_paginacao">' + html + '</div>').appendTo($("#janela_interessado_busca .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($(".corpo_interessado"));
            }
        }
    });
}

function atualizaFuncionario() {
    $(".dd-procedencia").text($("#show_procedencia").val());
    $(".dl-procedencia").show();
    $(".field_funcionario, .field_pessoa").show(); 
    var interessados = [];
    $(".field_pessoa, .field_funcionario").show(); 
    $('<tr><th width="50px">ID</th><th width="100px">Matrícula</th><th>Nome</th><th>Cargo</th><th>Setor</th></tr>').appendTo($("#lista_interessado_resposta thead"));
    if (ajax_obj) {
        ajax_obj.abort();
    }    
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/funcionario/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_cpf": $("#filtro_cpf").val(),
                   "filtro_nome": $("#filtro_nome").val(),
                   "filtro_matricula": $("#filtro_matricula").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    interessados[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    tr.appendTo($(".corpo_interessado"));
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.matricula + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.nome + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.cargo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.setor + '</a></td>').appendTo(tr);
                }
                $(".link_interessado").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_interessado").val(interessados[$(this).attr("id")].matricula + " - " + interessados[$(this).attr("id")].nome);
                        $("#id_interessado").val(interessados[$(this).attr("id")].id);
                        $("#janela_interessado_busca").modal("hide");
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="interessado_paginacao">' + html + '</div>').appendTo($("#janela_interessado_busca .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($(".corpo_interessado"));
            }
        }
    });
}

function atualizaSetor() {
    $(".dd-procedencia").text($("#show_procedencia").val());
    $(".dl-procedencia").show();
<?php if ($pms) { ?>
    if ($("#id_setor_procedencia").val() != '<?php echo $pms->getId(); ?>') {
        $("#bt_interessado_novo").show();
    }
<?php } ?>
    var interessados = [];
    $(".field_setor").show(); 
    $('<tr><th width="50px">ID</th><th width="100px">Sigla</th><th>Descrição</th></tr>').appendTo($("#lista_interessado_resposta thead"));
    if (ajax_obj) {
        ajax_obj.abort();
    }
    var dados_filtro = { "filtro_criterio": $("#jan_interessado_criterio").val(),
                         "pagina_atual": $("#jan_pagina").val(),
                         "qtd_por_pagina": 20 };
<?php if ($pms) { ?>
    if ($("#id_setor_procedencia").val() == '<?php echo $pms->getId(); ?>') {
<?php
$tb = new TbSetorTipo();
$st = $tb->getPorChave("I");
if ($st) { ?>
        dados_filtro.filtro_id_setor_tipo = "<?php echo $st->getId(); ?>";
<?php } ?>        
    } else {
        dados_filtro.filtro_id_setor_superior = $("#id_setor_procedencia").val();
    }
<?php } ?>           
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/setor/listarporpagina/format/json/",
        "type" : "POST",
        "data" : dados_filtro,
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    interessados[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    tr.appendTo($(".corpo_interessado"));
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.sigla + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_interessado">' + item.descricao + '</a></td>').appendTo(tr);
                }
                $(".link_interessado").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_interessado").val(interessados[$(this).attr("id")].sigla + " - " + interessados[$(this).attr("id")].descricao);
                        $("#id_interessado").val(interessados[$(this).attr("id")].id);
                        $("#janela_interessado_busca").modal("hide");
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="interessado_paginacao">' + html + '</div>').appendTo($("#janela_interessado_busca .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($(".corpo_interessado"));
            }
        }
    });
}

function setPage(page) {
    $("#jan_pagina").val(page);
    $("#formulario").submit();
}

function salvarInteressado() {
    $(".interessado_erro").hide();
    if (ajax_obj) {
        ajax_obj.abort();
    }
    switch ($("#jan_tipo_interessado").val()) {  
        case "P": 
            var url = "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/salvar/format/json/"; 
            var dados = { "cpf": $("#interessado_cpf").val(), "nome" : $("#interessado_nome").val() };
            break;
        case "J": 
            var url = "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/pjsalvar/format/json/"; 
            var dados = { "cnpj": $("#interessado_cnpj").val(), "sigla" : $("#interessado_sigla").val(), "razao_social" : $("#interessado_nome").val(), "nome_fantasia" : $("#interessado_nome").val() };
            break;
        case "S": 
            var url = "<?php echo Escola_Util::getBaseUrl(); ?>/setor/salvar/format/json/"; 
            var dados = { "sigla": $("#interessado_sigla").val(), "descricao" : $("#interessado_descricao").val(), "id_setor_superior" : $("#id_setor_procedencia").val(), "setor_nivel": "D" };
            break;        
    }
    ajax_obj = $.ajax({
        "url" : url,
        "type" : "POST",
        "data" : dados,
        "success" : function(result) {
            if (result.erro) {
                $(".interessado_erro .mensagem_erro").html(result.erro);
                $(".interessado_erro").show();
                $("#janela_interessado_novo .field").first().focus();
            } else {
                $("#tipo_interessado").val($("#jan_tipo_interessado").val());
                $("#id_interessado").val(result.id);
                $("#show_interessado").val(result.descricao);
                $("#janela_interessado_novo").modal("hide");
            }
        }
    });
}
</script>
        <div class="well">
            <fieldset>
<?php
        $ctrl = new Escola_Form_Element_Select_Table("id_documento_modo");
        $ctrl->setPkName("id_documento_modo");
        $ctrl->setModel("TbDocumentoModo");
        $ctrl->setValue($this->id_documento_modo);
        $ctrl->setLabel("Modo: ");
        echo $ctrl->render($view);

        $ctrl = new Escola_Form_Element_Select_Table("id_prioridade");
        $ctrl->setPkName("id_prioridade");
        $ctrl->setModel("TbPrioridade");
        $ctrl->setValue($this->id_prioridade);
        $ctrl->setLabel("Prioridade: ");
        echo $ctrl->render($view);
?>
                    <div class="control-group">
                        <label for="numero" class="control-label">Número:</label>
                        <div class="controls">
                            <input type="text" name="numero" id="numero" maxlength="50" value="<?php echo $this->numero; ?>" class="span2" /> /
                            <input type="text" name="ano" id="ano" size="4" maxlength="4" value="<?php echo $this->ano; ?>" class="span1" />
                <?php if (!$this->getId()) { ?>
                            Para gerar um número automático, basta deixar esse campo em branco.
                <?php } ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="resumo" class="control-label">Resumo:</label>
                        <div class="controls">
                            <textarea name="resumo" id="resumo" rows="5" class="span5"><?php echo $this->resumo; ?></textarea>
                        </div>
                    </div>
<?php if ($lotacao && $setor->protocolo()) { ?>
<div id="janela_procedencia_busca" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Localizar Procedência</h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <div class="control-group">
                <label for="jan_procedencia_criterio" class="control-label">Procurar Por:</label>
                <div class="controls">
                    <input type="text" name="jan_procedencia_criterio" id="jan_procedencia_criterio" value="" class="span5" />
                </div>
            </div>
        </div>
        <table id="lista_procedencia_resposta" class="table table-striped table-bordered">
            <thead>
                <th width="50px">ID</th>
                <th width="160px">Sigla</th>
                <th>Descrição</th>
            </thead>
            <tbody class="corpo_procedencia"></tbody>
        </table>        
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Limpar Filtro" id="bt_procedencia_limpar" class="btn" />
        <input type="button" value="Nova Procedência" class="btn" id="bt_procedencia_novo" />
        <input type="submit" value="Procurar" class="btn btn-primary" />
    </div>
</div>

<div id="janela_procedencia_novo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Nova Procedência</h3>
    </div>
    <div class="modal-body">
        <div class="alert procedencia_erro">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <div class="mensagem_erro"></div>
        </div>        
        <fieldset>
            <div class="control-group">
                <label for="procedencia_sigla" class="control-label">Sigla:</label>
                <div class="controls">
                    <input type="text" name="procedencia_sigla" id="procedencia_sigla" maxlength="50" class="field span5" />
                </div>
            </div>
            <div class="control-group">
                <label for="procedencia_descricao" class="control-label">Descrição:</label>
                <div class="controls">
                    <input type="text" name="procedencia_descricao" id="procedencia_descricao" maxlength="100" class="field span10" />
                </div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="submit" value="SALVAR" class="btn btn-primary" />
    </div>
</div>
<!-- fim janela_procedencia -->

<div id="janela_interessado_busca" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Localizar Interessado</h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <dl class="dl-horizontal dl-procedencia hide">
                <dt>Procedência:</dt>
                <dd class="dd-procedencia"></dd>
            </dl>
            <div class="control-group">
                <label for="jan_tipo_interessado" class="control-label">Tipo:</label>
                <div class="controls">
                    <select name="jan_tipo_interessado" id="jan_tipo_interessado">
                        <option value="">==> SELECIONE <==</option>
                        <option value="P">PESSOA FÍSICA</option>
                        <option value="J">PESSOA JURÍDICA</option>
                        <option value="F" class="option_funcionario">FUNCIONÁRIO</option>
                        <option value="S">SETOR</option>
                    </select>
                </div>
            </div>
            <div class="control-group field_funcionario">
                <label for="filtro_matricula" class="control-label">Matrícula:</label>
                <div class="controls">
                    <input type="text" name="filtro_matricula" id="filtro_matricula" value="" class="filtro span4" />
                </div>
            </div>
            <div class="control-group field_funcionario field_pessoa">
                <label for="filtro_cpf" class="control-label">C.P.F.:</label>
                <div class="controls">
                    <input type="text" name="filtro_cpf" id="filtro_cpf" value="" class="filtro cpf span4" />
                </div>
            </div>
            <div class="control-group field_funcionario field_pessoa_juridica">
                <label for="filtro_cnpj" class="control-label">C.N.P.J.:</label>
                <div class="controls">
                    <input type="text" name="filtro_cnpj" id="filtro_cnpj" value="" class="filtro cnpj span4" />
                </div>
            </div>
            <div class="control-group field_funcionario field_pessoa field_pessoa_juridica">
                <label for="filtro_nome" class="control-label">Nome:</label>
                <div class="controls">
                    <input type="text" name="filtro_nome" id="filtro_nome" value="" class="filtro span10" />
                </div>
            </div>
            <div class="control-group field_setor">
                <label for="jan_interessado_criterio" class="control-label">Procurar Por:</label>
                <div class="controls">
                    <input type="text" name="jan_interessado_criterio" id="jan_interessado_criterio" value="" class="filtro span10" />
                </div>
            </div>
        </div>
        <table id="lista_interessado_resposta" class="table table-striped table-bordered">
            <thead>
                <th width="50px">ID</th>
                <th width="160px">Sigla</th>
                <th>Descrição</th>
            </thead>
            <tbody class="corpo_interessado"></tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Novo Interessado" class="btn filtro_busca" id="bt_interessado_novo" />
        <input type="button" value="Limpar Filtro" id="bt_interessado_limpar" class="btn filtro_busca interessado_btn" />
        <input type="submit" value="Procurar" class="btn btn-primary filtro_busca interessado_btn" />
    </div>
</div>
<!-- janela interessado novo cadastro -->
<div id="janela_interessado_novo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Novo Interessado</h3>
    </div>
    <div class="modal-body">
        <div class="alert interessado_erro">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <div class="mensagem_erro"></div>
        </div>        
        <fieldset>
            <div class="control-group field_pessoa_juridica">
                <label for="interessado_cnpj" class="control-label">C.N.P.J.:</label>
                <div class="controls">
                    <input type="text" name="interessado_cnpj" id="interessado_cnpj" value="" class="field cnpj span4" />
                </div>
            </div>            
            <div class="control-group field_setor field_pessoa_juridica">
                <label for="interessado_sigla" class="control-label">Sigla:</label>
                <div class="controls">
                    <input type="text" name="interessado_sigla" id="interessado_sigla" maxlength="50" class="field span4" />
                </div>
            </div>
            <div class="control-group field_setor">
                <label for="interessado_descricao" class="control-label">Descrição:</label>
                <div class="controls">
                    <input type="text" name="interessado_descricao" id="interessado_descricao" maxlength="100" class="field span9" />
                </div>
            </div>
            <div class="control-group field_pessoa">
                <label for="interessado_cpf" class="control-label">C.P.F.:</label>
                <div class="controls">
                    <input type="text" name="interessado_cpf" id="interessado_cpf" value="" class="field cpf span4" />
                </div>
            </div>
            <div class="control-group field_pessoa field_pessoa_juridica">
                <label for="interessado_nome" class="control-label">Nome:</label>
                <div class="controls">
                    <input type="text" name="interessado_nome" id="interessado_nome" maxlength="100" class="field span9" />
                </div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="submit" value="SALVAR" class="btn btn-primary" />
    </div>
</div>
<!-- fim da janela interessado -->
<?php } ?>
                    <input type="hidden" name="id_setor_procedencia" id="id_setor_procedencia" value="<?php echo $this->id_setor_procedencia; ?>" />
                    <div class="control-group">
                        <label for="procedencia" class="control-label">Procedência:</label>
                        <div class="controls">
                            <div class="input-append">
                                <input type="text" name="show_procedencia" id="show_procedencia" class="input-xxlarge" disabled value="<?php echo $this->mostrarProcedencia(); ?>" />
<?php if ($lotacao && $setor->protocolo()) { ?>
                                <div class="add-on">
                                    <a href="#" id="link_procedencia" title="Selecionar Procedência">
                                        <i class="icon-search icon-large"></i>
                                    </a>
                                </div>
<?php } ?>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="tipo_interessado" id="tipo_interessado" value="<?php echo $this->tipo_interessado; ?>" />
                    <input type="hidden" name="id_interessado" id="id_interessado" value="<?php echo $this->id_interessado; ?>" />
                    <div class="control-group">
                        <label for="interessado" class="control-label">Interessado:</label>
                        <div class="controls">
                        <div class="input-append">
                            <input type="text" name="show_interessado" id="show_interessado" class="input-xxlarge" disabled value="<?php echo $this->mostrarInteressado(); ?>" />
            <?php if ($lotacao && $setor->protocolo()) { ?>
                            <div class="add-on">
                                <a href="#" id="link_interessado">
                                    <i class="icon-search icon-large"></i>
                                </a>
                            </div>
            <?php } ?>
                        </div>
                        </div>
                    </div>
<?php if (!$this->getId() && $lotacao && $setor->protocolo()) { ?>                    
                    <div class="control-group">
                        <label for="ck_processo" class="control-label">Tornar Processo?</label>
                        <div class="controls">
                            <input type="checkbox" name="ck_processo" id="ck_processo" />
                        </div>
                    </div>
<?php } ?>
                </fieldset>
            </div>
            <?php if (!$this->getId()) { ?>
<?php if ($lotacao && $setor->protocolo()) { ?>
                <div class="well well-small" id="processo" style="display:none">
                    <fieldset>
                        <legend>Tornar Processo</legend>
                        <div class="control-group">
                            <label for="processo_numero" class="control-label">Número / Ano:</label>
                            <div class="controls">
                                <input type="text" name="processo_numero" id="processo_numero" class="span2" /> / <input type="text" name="processo_ano" id="processo_ano" class="span1" />Para gerar um número de Processo automático, basta deixar esse campo em branco.
                            </div>
                        </div>
                    </fieldset>
                </div>
<?php } ?>
<?php echo $this->janela_destino(); ?>
                <div class="well">
                    <fieldset class="fieldset_arquivo">
                        <legend>INSERIR ARQUIVO</legend>
                        <div class="text-right">
                            <input type="button" value="Limpar" class="btn" id="btn_limpar_arquivo" />
                        </div>
                        <div class="linha_arquivo">
                            <div class="control-group">
                                <label for="legenda" class="control-label">Legenda:</label>
                                <div class="controls">
                                    <input type="text" name="legenda_1" id="legenda_1" class="span5 legenda" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="arquivo" class="control-label">Arquivo:</label>
                                <div class="controls">
                                    <input type="file" name="arquivo_1" id="arquivo_1" class="arquivo" />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="well">
                    <fieldset class="encaminhamento">
                        <legend>PRIMEIRO ENCAMINHAMENTO: </legend>
                        <input type="hidden" name="tipo_destino" id="tipo_destino" value="" />
                        <input type="hidden" name="id_destino" id="id_destino" value="" />
                        <div class="control-group">
                            <label for="destino" class="control-label">Destino:</label>
                            <div class="controls">
                                <div class="input-append">
                                    <input type="text" name="show_destino" id="show_destino" disabled class="input-xxlarge" />
                                    <div class="add-on">
                                        <a href="#" id="link_destino">
                                            <i class="icon-search icon-large"></i>
                                        </a>
                                    </div>
                                </div>
                                </div>
                            </div>
                    </fieldset>
                </div>
<?php } ?>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function janela_destino() {
        ob_start();
?>
<script type="text/javascript">
$(document).ready(
	function() {
        //DESTINO
        $("#link_destino").click(
            function(event) {
                event.preventDefault();
                $("#jan_pagina").val("");
                $("#janela_destino").modal("show");
            }
        );
        $("#jan_tipo_destino").change(
            function() {
                $("#jan_pagina").val("");
                procurarDestino();
            }
        );
        $(".bt_destino_cancelar").click(
            function() {
                fecharDestino();
            }
        );
        $("#bt_destino_limpar").click(
            function() {
                $("#janela_destino .filtro").val("");
                procurarDestino();
            }
        );
        $("#janela_destino").css( { "width": "800px", "margin-left": "-400px" } ).modal("hide");
        $("#janela_destino").on("shown", function() {
            $("#jan_tipo_destino").val($("#tipo_destino").val());
            $("#operacao").val("destino");
            $(".link_destino, #janela_destino .filtro").val("");
            procurarDestino();            
        });
        $("#janela_destino").on("hide", function() {
            $("#operacao").val("");
        });
        $("#janela_destino").on("keypress", function(event) {
            if (event.which == 13) {
                event.preventDefault(); 
                $("#formulario").submit();
            }
        });
	}
);

function procurarDestino() {
    var ctrls = $("#destino_busca .filtro_busca");
    var obj = $("#jan_tipo_destino");
    ctrls.hide();
    if (obj.val().length) {
        ctrls.show();
    }
    $("#destino_resposta, .linha_filtro").hide();
    switch (obj.val()) {
        case "F":
            $("#destino_busca .filtro_funcionario, #destino_busca .filtro_pessoa").show();
            buscarDestinoFuncionario();
            break;
        case "S":
            $("#destino_busca .filtro_setor").show();
            buscarDestinoSetor();
            break;
    }
}

function fecharDestino() {
    $("#janela_destino").modal("hide");
}

function buscarDestinoSetor() {
    $("#destino_resposta tr").remove();
    $("#destino_resposta").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/setor/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_criterio": $("#jan_destino_criterio").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "filtro_id_setor_procedencia": $("#id_setor_procedencia").val(),
                   "setor_tipo" : "I",
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            destinos = [];
            $('<tr><th width="50px">ID</th><th width="160px">Sigla</th><th>Descrição</th></tr>').appendTo($("#destino_resposta .head_destino"));
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    destinos[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#destino_resposta"));
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.sigla + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.descricao + '</a></td>').appendTo(tr);
                    tr.appendTo($(".corpo_destino"));
                }
                $(".link_destino").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_destino").val(destinos[$(this).attr("id")].sigla + " - " + destinos[$(this).attr("id")].descricao);
                        $("#tipo_destino").val($("#jan_tipo_destino").val());
                        $("#id_destino").val(destinos[$(this).attr("id")].id);
                        fecharDestino();
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<tr class="linha_resultado"><td align="center" colspan="3">' + html + '</td></tr>').appendTo($("#destino_resposta"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#destino_resposta"));
            }
            $("#janela_destino .filtro").first().focus();
            $("#destino_resposta").show();
            $("#mask2").height($(document).height());
        }
    });
}

function buscarDestinoFuncionario() {
    $("#destino_resposta tr").remove();
    $("#destino_resposta").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/funcionario/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_cpf": $("#destino_filtro_cpf").val(),
                   "filtro_matricula": $("#destino_filtro_matricula").val(), 
                   "filtro_nome": $("#destino_filtro_nome").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "filtro_id_setor_procedencia": $("#id_setor_procedencia").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            destinos = [];
            $('<tr><th width="50px">ID</th><th>Matrícula</th><th>Nome</th><th>Cargo</th><th>Setor</th></tr>').appendTo($("#destino_resposta .head_destino"));
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    destinos[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#destino_resposta"));
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.matricula + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.nome + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.cargo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.setor + '</a></td>').appendTo(tr);
                    tr.appendTo($(".corpo_destino"));
                }
                $(".link_destino").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_destino").val(destinos[$(this).attr("id")].matricula + " - " + destinos[$(this).attr("id")].nome);
                        $("#tipo_destino").val($("#jan_tipo_destino").val());
                        $("#id_destino").val(destinos[$(this).attr("id")].id);
                        fecharDestino();
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<tr class="linha_resultado"><td align="center" colspan="5">' + html + '</td></tr>').appendTo($("#destino_resposta"));
            } else {
                $("<tr class='linha_resultado'><td colspan='5' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#destino_resposta"));
            }
            $("#janela_destino .filtro").first().focus();
            $("#destino_resposta").show();
            $("#mask2").height($(document).height());
        }
    });
}
</script>

<div id="janela_destino" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Localizar Destino</h3>
    </div>
    <div class="modal-body">
        <div class="well well-small" id="destino_busca">
            <fieldset>
                <div class="control-group">
                    <label for="jan_tipo_destino" class="control-label">Tipo:</label>
                    <div class="controls">
                        <select name="jan_tipo_destino" id="jan_tipo_destino">
                            <option value="">==> SELECIONE <==</option>
                            <option value="F">FUNCIONÁRIO</option>
                            <option value="S">SETOR</option>
                        </select>
                    </div>
                </div>
                <div class="control-group filtro_busca filtro_funcionario linha_filtro">
                    <label for="destino_filtro_matricula" class="control-label">Matrícula:</label>
                    <div class="controls">
                        <input type="text" name="filtro_matricula" id="destino_filtro_matricula" value="" class="filtro" />
                    </div>
                </div>
                <div class="control-group filtro_busca filtro_pessoa linha_filtro">
                    <label for="destino_filtro_cpf" class="control-label">C.P.F.:</label>
                    <div class="controls">
                        <input type="text" name="filtro_cpf" id="destino_filtro_cpf" value="" class="filtro cpf" />
                    </div>
                </div>
                <div class="control-group filtro_busca filtro_pessoa linha_filtro">
                    <label for="destino_filtro_nome" class="control-label">Nome:</label>
                    <div class="controls">
                        <input type="text" name="filtro_nome" id="destino_filtro_nome" value="" size="60" class="filtro" />
                    </div>
                </div>
                <div class="control-group filtro_busca filtro_setor linha_filtro">
                    <label for="jan_destino_criterio" class="control-label">Nome:</label>
                    <div class="controls">
                        <input type="text" name="jan_destino_criterio" id="jan_destino_criterio" value="" size="60" class="filtro" />
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="destino_resposta" class="table table-striped table-bordered" style="display:none">
            <thead class="head_destino"></thead>
            <tbody class="corpo_destino"></tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
		<input type="button" value="Limpar Filtro" id="bt_destino_limpar" class="filtro_busca btn" />
        <input type="submit" value="Procurar" class="btn btn-primary filtro_busca" />
    </div>
</div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function pegaRequerimentoJari() {
        if ($this->getId()) {
            $tb = new TbRequerimentoJari();
            $rjs = $tb->listar(array("id_documento" => $this->getId()));
            if ($rjs && count($rjs)) {
                $rj = $rjs->current();
                return $rj;
            }
        }
        return false;
    }
    
    public function pegaRequerimentoJariPendente() {
        if ($this->getId()) {
            $tb = new TbRequerimentoJariStatus();
            $rjs = $tb->getPorChave("AR");
            if ($rjs) {
                $tb = new TbRequerimentoJari();
                $rjs = $tb->listar(array("id_documento" => $this->getId(), "id_requerimento_jari_status" => $rjs->getId()));
                if ($rjs && count($rjs)) {
                    $rj = $rjs->current();
                    return $rj;
                }
            }
        }
        return false;
    }
    
    public function pegaAutoInfracaoNotificacao() {
        $rj = $this->pegaRequerimentoJari();
        if ($rj) {
            $ain = $rj->findParentRow("TbAutoInfracaoNotificacao");
            if ($ain && $ain->getId()) {
                return $ain;
            }
        }
        return false;
    }
    
    public function view(Zend_View_Abstract $view) {
        ob_start();
?>
            <div class="well">
                <fieldset>
                    <div class="page-header">
                        <h4>Documento</h4>
                    </div>
                    <dl class="dl-horizontal">
                        <dt>ID:</dt>
                        <dd><?php echo $this->getId(); ?></dd>
                    </dl>
                    <dl class="dl-horizontal">
                        <dt>Tipo:</dt>
                        <dd>
                        <?php echo $this->findParentRow("TbDocumentoTipo")->descricao; ?>
            <?php if ($this->findParentRow("TbDocumentoModo")->circular()) { ?>
                        - CIRCULAR
            <?php } ?>
                        </dd>
                    </dl>
            <?php 
            $processo = $this->pegaDocumentoPrincipal();
            if ($processo) { 
            ?>
                    <dl class="dl-horizontal">
                        <dt>Documento Principal:</dt>
                        <dd>
                            <a href="<?php echo Escola_Util::url(array("controller" => "documento", "action" => "view", "id" => $processo->getId()), null, true); ?>">
                                <?php echo $processo->toString(); ?>
                            </a>
                        </dd>
                    </dl>
            <?php } ?>
            <?php 
            $doc = $this->pegaDocumentoOriginal();
            if ($doc) { 
            ?>
                    <dl class="dl-horizontal">
                        <dt>Documento Original:</dt>
                        <dd>
                            <a href="<?php echo Escola_Util::url(array("action" => "view", "id" => $doc->getId(), "flag" => "true")); ?>">
                                <?php echo $doc->toString(); ?>
                            </a>
                        </dd>
                    </dl>
            <?php } ?>
                    <dl class="dl-horizontal">
                        <dt>Número:</dt>
                        <dd><?php echo $this->mostrarNumero(); ?></dd>
                    </dl>
            <?php 
            $prioridade = $this->findParentRow("TbPrioridade");
            if ($prioridade) { 
            ?>
                    <dl class="dl-horizontal">
                        <dt>Prioridade:</dt>
                        <dd><?php echo $prioridade->toString(); ?></dd>
                    </dl>
            <?php } ?>
                    <dl class="dl-horizontal">
                        <dt>Data / Hora:</dt>
                        <dd><?php echo Escola_Util::formatData($this->data_criacao); ?> <?php echo $this->hora_criacao; ?></dd>
                    </dl>
                    <dl class="dl-horizontal">
                        <dt>Criado Por:</dt>
                        <dd><?php echo $this->findParentRow("TbFuncionario")->toString(); ?> - <?php echo $this->findParentRow("TbSetor")->toString(); ?></dd>
                    </dl>
                    <dl class="dl-horizontal">
                        <dt>Procedência:</dt>
                        <dd><?php echo $this->mostrarProcedencia(); ?></dd>
                    </dl>
                    <dl class="dl-horizontal">
                        <dt>Interessado:</dt>
                        <dd><?php echo $this->mostrarInteressado(); ?></dd>
                    </dl>
            <?php
            $atual = $this->pegaSetorAtual();
            if ($atual) {
            ?>
                    <dl class="dl-horizontal">
                        <dt>Setor Atual:</dt>
                        <dd><?php echo $atual->toString(); ?></dd>
                    </dl>
            <?php } ?>
                    <dl class="dl-horizontal">
                        <dt>Resumo:</dt>
                        <dd><?php echo $this->resumo; ?></dd>
                    </dl>
                    <dl class="dl-horizontal">
                        <dt>Tempo no Setor:</dt>
                        <dd><?php echo $this->mostrarTempoSetor(); ?></dd>
                    </dl>
                    <dl class="dl-horizontal">
                        <dt>Situação:</dt>
                        <dd><?php echo $this->findParentRow("TbDocumentoStatus")->toString(); ?></dd>
                    </dl>
                </fieldset>
            </div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function arquivado() {
        $ds = $this->pegaDocumentoStatus();
        if ($ds) {
            return $ds->arquivado();
        }
        return false;
    }

    public function __call($method, array $args) {
        if ($result = $this->trataAdd($method, $args)) {
            return $result;
        }
    }

    public function trataAdd($method, $args) {

        if (!Escola_Util::startsWith($method, "add")) {
            return null;
        }

        $entidade = strtolower(substr($method, 3));
        $id = $this->getId();
    	if (!$id) {
            return false;
        }

        if (!isset($args[0])) {
            return false;
        }
        
        $obj = $args[0];
        $obj_pk = "id_" . $entidade;

        $obj_id = $obj->$obj_pk;
        if (Escola_Util::vazio($obj_id)) {
            return false;
        }

        // verifica já cadastrado
        $tb = new TbDocumentoRef();
        $objs = $tb->listar([
            "tipo" => $entidade,
            "chave" => $obj_id,
            "id_documento" => $id
        ]);

        if (!Escola_Util::vazio($objs)) {
            return $objs[0]->id_documento_ref;
        }

        $dr = $tb->createRow();
        $dr->setfromArray(array("tipo" => $entidade,
                                "chave" => $obj->getId(),
                                "id_documento" => $id));
        
        $errors = $dr->getErrors();

        if (!Escola_Util::vazio($errors)) {
            throw new Escola_Exception_List($errors);
        }

        return $dr->save();
    }    
}