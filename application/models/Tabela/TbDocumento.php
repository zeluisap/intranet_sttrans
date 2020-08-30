<?php
class TbDocumento extends Escola_Tabela {
	protected $_name = "documento";
	protected $_rowClass = "Documento";
	protected $_dependentTables = array("TbDocumentoRef", "TbMovimentacao", "TbRequerimentoJari");
	protected $_referenceMap = array("DocumentoModo" => array("columns" => array("id_documento_modo"),
															 "refTableClass" => "TbDocumentoModo",
															 "refColumns" => array("id_documento_modo")),
									 "DocumentoStatus" => array("columns" => array("id_documento_status"),
															 "refTableClass" => "TbDocumentoStatus",
															 "refColumns" => array("id_documento_status")),
									 "DocumentoTipo" => array("columns" => array("id_documento_tipo"),
															 "refTableClass" => "TbDocumentoTipo",
															 "refColumns" => array("id_documento_tipo")),
									 "Setor" => array("columns" => array("id_setor"),
															 "refTableClass" => "TbSetor",
															 "refColumns" => array("id_setor")),
									 "Funcionario" => array("columns" => array("id_funcionario"),
															 "refTableClass" => "TbFuncionario",
															 "refColumns" => array("id_funcionario")),
									 "Prioridade" => array("columns" => array("id_prioridade"),
															 "refTableClass" => "TbPrioridade",
															 "refColumns" => array("id_prioridade")));    
	
    public function getSql($dados = array()) {
        return $this->geraSQL($dados);
    }
    
	private function geraSQL($dados = array()) {
		$db = Zend_Registry::get("db");		
		$sql = $this->select();
		$sql->from(array("d" => "documento"));
		$sql->join(array("dt" => "documento_tipo"), "d.id_documento_tipo = dt.id_documento_tipo", array());
		if (isset($dados["filtro_opcao"]) && $dados["filtro_opcao"]) {
			if (isset($dados["funcionario"]) && $dados["funcionario"]) {
				$funcionario = $dados["funcionario"];
			} else {
				$tb = new TbFuncionario();
				$funcionario = $tb->pegaLogado();
			}
                        if (!$funcionario) {
                            throw new Exception("Falha ao Executar Operação! Funcionário não Localizado!");
                        }
			$lotacao = $funcionario->pegaLotacaoAtual();			
			switch ($dados["filtro_opcao"]) {
				case "R":
					$tb = new TbDocumentoStatus();
					$ds = $tb->getPorChave("R");
					if ($ds) {
						$dados["filtro_id_documento_status"] = $ds->getId();
					}
					$tb = new TbMovimentacaoTipo();
					$mt = $tb->getPorChave("E");
					$msql = $db->select();
					$msql->from(array("m" => "movimentacao", array("id_movimentacao")));
					$msql->where("id_movimentacao_tipo = " . $mt->getId());
					$msql->where("id_documento = d.id_documento");
					$msql->where("(tipo_destino = 'S' and id_destino = {$lotacao->id_setor}) or (tipo_destino = 'F' and id_destino = " . $funcionario->getId() . ")");
					$msql->where("id_movimentacao_recebe = 0");
					$sql->where(" exists (" . $msql . ") ");
					break;
				case "S":
					$sql->where("id_setor_atual = " . $lotacao->id_setor);
					break;
				case "P":
					$sql->where("id_setor = " . $lotacao->id_setor);
					break;
				case "V":
					if (isset($dados["filtro_id_documento"]) && $dados["filtro_id_documento"]) {
						$doc = TbDocumento::pegaPorId($dados["filtro_id_documento"]);
						if ($doc) {
							$sql->where("id_documento <> " . $doc->getId());
							$setor = $doc->pegaSetorAtual();
							$sql->where("id_setor_atual = " . $setor->getId());
						}
					}
					break;
			}
		}
		if (isset($dados["filtro_id_documento_status"]) && $dados["filtro_id_documento_status"]) {
			$sql->where(" d.id_documento_status = {$dados["filtro_id_documento_status"]} ");
		}
		if (isset($dados["filtro_id_documento_tipo"]) && $dados["filtro_id_documento_tipo"]) {
			$sql->where(" d.id_documento_tipo = {$dados["filtro_id_documento_tipo"]} ");
		}
		if (isset($dados["filtro_id_documento_tipo_target"]) && $dados["filtro_id_documento_tipo_target"]) {
			$sql->where(" dt.id_documento_tipo_target = {$dados["filtro_id_documento_tipo_target"]} ");
		}
		if (isset($dados["filtro_numero"]) && $dados["filtro_numero"]) {
			$sql->where(" d.numero = '{$dados["filtro_numero"]}' ");
		}
		if (isset($dados["filtro_ano"]) && $dados["filtro_ano"]) {
			$sql->where(" d.ano = '{$dados["filtro_ano"]}' ");
		}
		if (isset($dados["filtro_resumo"]) && $dados["filtro_resumo"]) {
			$sql->where(" d.resumo like '%{$dados["filtro_resumo"]}%' ");
		}
		if (isset($dados["filtro_data_inicial"]) && $dados["filtro_data_inicial"]) {
            $dados["filtro_data_inicial"] = Escola_Util::montaData($dados["filtro_data_inicial"]);
			$sql->where(" d.data_criacao >= '{$dados["filtro_data_inicial"]}' ");
		}
		if (isset($dados["filtro_data_final"]) && $dados["filtro_data_final"]) {
            $dados["filtro_data_final"] = Escola_Util::montaData($dados["filtro_data_final"]);
			$sql->where(" d.data_criacao <= '{$dados["filtro_data_final"]}' ");
		}
        if (isset($dados["filtro_interessado"]) && $dados["filtro_interessado"]) {
            //interessado pessoa física
            $sqlpf = $db->select();
            $sqlpf->from(array("doc" => "documento"), array("doc.id_documento"));
            $sqlpf->join(array("pf" => "pessoa_fisica"), "doc.id_interessado = pf.id_pessoa_fisica", array());
            $sqlpf->where("doc.tipo_interessado = 'P'");
            $sqlpf->where("pf.nome like '%{$dados["filtro_interessado"]}%'");
            
            //interessado funcionario
            $sqlf = $db->select();
            $sqlf->from(array("doc" => "documento"), array("doc.id_documento"));
            $sqlf->join(array("func" => "funcionario"), "doc.id_interessado = func.id_funcionario", array());
            $sqlf->join(array("pf" => "pessoa_fisica"), "func.id_pessoa_fisica = pf.id_pessoa_fisica", array());
            $sqlf->where("doc.tipo_interessado = 'F'");
            $sqlf->where("pf.nome like '%{$dados["filtro_interessado"]}%'");
            
            //interessado setor
            $sqls = $db->select();
            $sqls->from(array("doc" => "documento"), array("doc.id_documento"));
            $sqls->join(array("set" => "setor"), "doc.id_interessado = set.id_setor", array());
            $sqls->where("doc.tipo_interessado = 'S'");
            $sqls->where("set.sigla = '{$dados["filtro_interessado"]}' or set.descricao like '%{$dados["filtro_interessado"]}%'");
            
            $sql->where("d.id_documento in ({$sqlpf}) or d.id_documento in ({$sqlf}) or d.id_documento in ({$sqls})");
        }
        if (isset($dados["filtro_setor"]) && $dados["filtro_setor"]) {
            $sqli = $db->select();
            $sqli->from(array("doc1" => "documento"), array("doc1.id_documento"));
            $sqli->join(array("set1" => "setor"), "doc1.id_setor = set1.id_setor", array());
            $sqli->where("set1.sigla like '%{$dados["filtro_setor"]}%' or set1.descricao like '%{$dados["filtro_setor"]}%'");
            $sql->where("d.id_documento in ({$sqli})");
        }
        if (isset($dados["filtro_funcionario"]) && $dados["filtro_funcionario"]) {
            $sqli = $db->select();
            $sqli->from(array("doc2" => "documento"), array("doc2.id_documento"));
            $sqli->join(array("f1" => "funcionario"), "doc2.id_funcionario = f1.id_funcionario", array());
            $sqli->join(array("pf1" => "pessoa_fisica"), "f1.id_pessoa_fisica = pf1.id_pessoa_fisica", array());
            $sqli->where("pf1.nome like '%{$dados["filtro_funcionario"]}%'");
            $sql->where("d.id_documento in ({$sqli})");
        }
		$sql->order("d.data_criacao desc");
		$sql->order("d.hora_criacao desc");
                
		return $sql;
	}
	
	public function listarPorPagina($dados = array()) {
		$qtd_por_pagina = 50;
		if (isset($dados["qtd_por_pagina"]) && $dados["qtd_por_pagina"]) {
			$qtd_por_pagina = $dados["qtd_por_pagina"];
		}
		$sql = $this->geraSQL($dados);
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($sql);
		// $adapter = new Zend_Paginator_Adapter_DbSelect($sql);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage($qtd_por_pagina);
		return $paginator;
	}
	
	public function listar($dados = array()) {
            $sql = $this->geraSQL($dados);
            $rg = $this->fetchAll($sql);
            if ($rg && count($rg)) {
                return $rg;
            }
            return false;
	}
	
	public static function pegaAtraso($funcionario) {
		if ($funcionario) {
			$tb = new TbDocumentoStatus();
			$ds = $tb->getPorChave("E");
			$tb = new TbDocumentoTipoTarget();
			$dtt = $tb->getPorChave("D");
			if ($ds && $dtt) {
				$lotacao = $funcionario->pegaLotacaoAtual();
				if ($lotacao) {
					$tb = new TbDocumento();
					$sql = $tb->select();
					$sql->from(array("d" => "documento"), array("id_documento"));
					$sql->join(array("dt" => "documento_tipo"), "d.id_documento_tipo = dt.id_documento_tipo", array());
					$sql->join(array("p" => "prioridade"), "d.id_prioridade = p.id_prioridade", array());
					$sql->where("d.id_documento_status = " . $ds->getId());
					$sql->where("d.id_setor_atual = " . $lotacao->id_setor);
					$sql->where("dt.id_documento_tipo_target = " . $dtt->getId());
					$sql->where("datediff(CURRENT_DATE, d.data_setor_atual) > p.tolerancia");
					$rg = $tb->fetchAll($sql);
					if ($rg && count($rg)) {
						return $rg;
					}
				}
				/*
				$dados = array("funcionario" => $funcionario,
							   "qtd_por_pagina" => 1000,
							   "filtro_id_documento_status" => $ds->getId(),
							   "filtro_opcao" => "S"); //documentos que estão no setor
				$tb = new TbDocumento();
				$documentos = $tb->listarPorPagina($dados);
				if ($documentos) {
					$atrasos = array();
					foreach ($documentos as $doc) {
						$doc = TbDocumento::pegaPorId($doc["id_documento"]);
						if ($doc->fora_do_prazo()) {
							$atrasos[] = $doc;
						}
					}
					if ($atrasos) {
						return $atrasos;
					}
				}
				*/
			}
		}
		return false;
	}
    
    public function pegaReceber($funcionario) {
		if ($funcionario) {
			$tb = new TbDocumentoStatus();
			$ds = $tb->getPorChave("R");
			$tb = new TbDocumentoTipoTarget();
			$dtt = $tb->getPorChave("D");
			if ($ds && $dtt) {
				$lotacao = $funcionario->pegaLotacaoAtual();
				if ($lotacao) {
                    $tb_mt = new TbMovimentacaoTipo();
                    $mt = $tb_mt->getPorChave("E");
                    if ($mt) {
                        $db = Zend_Registry::get("db");
                        $tb = new TbDocumento();
                        $sql = $tb->select();
                        $sql->from(array("d" => "documento"), array("id_documento"));
                        $sql->join(array("dt" => "documento_tipo"), "d.id_documento_tipo = dt.id_documento_tipo", array());
                        $sql->where("d.id_documento_status = {$ds->getId()}");
                        $sql->where("dt.id_documento_tipo_target = {$dtt->getId()}");
                    
                        $sql_mov = $db->select();
                        $sql_mov->from(array("m" => "movimentacao"), array("id_documento"));
                        $sql_mov->where("m.id_movimentacao_tipo = {$mt->getId()}");
                        $sql_mov->where("m.id_movimentacao_recebe = null or m.id_movimentacao_recebe = 0");
                        $sql_mov->where("((m.tipo_destino = 'S' and m.id_destino = {$lotacao->id_setor}) or (m.tipo_destino = 'F' and m.id_destino = {$funcionario->getId()}))");

                        $sql->where("id_documento in ({$sql_mov})");
                        
                        $rg = $tb->fetchAll($sql);
                        if ($rg && count($rg)) {
                            return $rg;
                        }
                    }
				}
			}
		}
		return false;
    }

    public function pegaEstatistica() {
        $db = Zend_Registry::get("db");
        $sql = "select c.id_documento_tipo_target, c.descricao, count(a.id_documento) as total
                from documento a, documento_tipo b, documento_tipo_target c
                where (a.id_documento_tipo = b.id_documento_tipo)
                and (b.id_documento_tipo_target = c.id_documento_tipo_target)
                group by c.id_documento_tipo_target, c.descricao";
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            return $stmt->fetchAll(Zend_Db::FETCH_OBJ);
        }
        return false;
    }
    
    public function getPorId($id) {
        try {
            $obj = parent::getPorId($id);
            if ($obj) {
                $dt = $obj->findParentRow("TbDocumentoTipo");
                if ($dt && $dt->getId()) {
                    $class_name = "Documento_" . $dt->chave;
                    if (@Zend_Loader_Autoloader::autoload($class_name)) {
                        $dados = $obj->toArray();
                        $stored = true;
                        $objeto = new $class_name(array("table" => $this, "data" => $dados, "stored" => $stored));
                        return $objeto;
                    }
                }
                return $obj;
            }
        } catch (Exception $e) {
            if ($obj) {
                return $obj;
            }
            return false;
        }
        return false;        
    }
    
    public function createRow($dados = array()) {
        try {
            $obj = parent::createRow($dados);
            $dt = $obj->findParentRow("TbDocumentoTipo");
            if ($dt && $dt->getId()) {
                $class_name = $class_name = "Documento_" . $dt->chave;
                if (Zend_Loader_Autoloader::autoload($class_name)) {
                    return new $class_name(array("table" => $this, "data" => $obj->toArray(), "stored" => false));
                }
            }
            return $obj;
        } catch (Exception $e) {
            return false;
        }
        return $obj;
	}
	
    public function getEstatisticaPorAno($options) {

		$wheres = [];
		$params = [];

		if ($tipo = Escola_Util::valorOuNulo($options, "tipo")) {
			$wheres[] = "dr.tipo = :tipo";
			$params["tipo"] = $tipo;
		}

		if ($chave = Escola_Util::valorOuNulo($options, "chave")) {
			$wheres[] = "dr.chave = :chave";
			$params["chave"] = $chave;
		}

		$where = '';
		if (!Escola_Util::vazio($wheres)) {
			$where = " where (" . implode(") and (", $wheres) . ") ";
		}

        $db = Zend_Registry::get("db");
		$sql = "
			select d.ano, count(d.id_documento) as quantidade
			from documento_ref dr
				inner join documento d on dr.id_documento = d.id_documento
			" . $where . "
			group by d.ano;		
		";
        $stmt = $db->query($sql, $params);
        if ($stmt && $stmt->rowCount()) {
            return $stmt->fetchAll(Zend_Db::FETCH_OBJ);
        }
        return false;
    }

}