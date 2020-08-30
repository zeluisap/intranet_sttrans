<?php
class VinculoLoteOcorrenciaPgto extends Escola_Entidade {
    
    protected $_arquivo;
    protected $_valor = false;
    
    public function pega_valor() {
        if ($this->_valor) {
            return $this->_valor;
        }
        $valor = $this->findParentRow("TbValor");
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }    
    
    public function init() {
        parent::init();
        $this->_valor = $this->pega_valor();        
        $this->_arquivo = $this->get_arquivo();
        if (!$this->data_cadastro) {
            $this->data_cadastro = date("Y-m-d");
        }
        if (!$this->hora_cadastro) {
            $this->hora_cadastro = date("H:i:s");
        }
    }
    
    public function get_arquivo() {
        if ($this->_arquivo) {
            return $this->_arquivo;
        }
        $tb = new TbArquivo();
        if ($this->id_arquivo) {
            $arquivo = $tb->getPorId($this->id_arquivo);
            if ($arquivo) {
                return $arquivo;
            }
        }
        return $tb->createRow();
    }
    
    public function setFromArray($dados = array()) {
        if (isset($dados["data_cadastro"])) {
            $dados["data_cadastro"] = Escola_Util::montaData($dados["data_cadastro"]);
        }
        if (isset($dados["doc_numero"])) {
            $dados["doc_numero"] = Escola_Util::maiuscula($dados["doc_numero"]);
        }
        if (isset($dados["forma_numero"])) {
            $dados["forma_numero"] = Escola_Util::maiuscula($dados["forma_numero"]);
        }
        $this->_valor->setFromArray($dados);
        parent::setFromArray($dados);
        $this->_arquivo->setFromArray($dados);
    }

    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_vinculo_lote)) {
            $msgs[] = "CAMPO LOTE OBRIGATÓRIO!";
        }        
/*        
        if (!trim($this->id_vinculo_lote_ocorrencia)) {
            $msgs[] = "CAMPO OCORRÊNCIA DE LOTE OBRIGATÓRIO!";
        }
*/
        if (empty($this->data_cadastro)) {
            $msgs[] = "CAMPO DATA DE CADASTRO OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaData($this->data_cadastro)) {
            $msgs[] = "CAMPO DATA DE CADASTRO INVÁLIDO!";
        }        
        if (!trim($this->hora_cadastro)) {
            $msgs[] = "CAMPO HORA DE CADASTRO OBRIGATÓRIO!";
        }
        if (!trim($this->id_previsao_tipo)) {
            $msgs[] = "CAMPO TIPO DE LANÇAMENTO OBRIGATÓRIO!";
        }
        if (!trim($this->id_bolsa_tipo)) {
            $msgs[] = "CAMPO TIPO DE DESPESA OBRIGATÓRIO!";
        }
        if (!trim($this->id_forma_pagamento)) {
            $msgs[] = "CAMPO FORMA DE PAGAMENTO OBRIGATÓRIO!";
        }
        if (!trim($this->id_doc_comprovacao)) {
            $msgs[] = "CAMPO TIPO DE DOCUMENTO DE COMPROVAÇÃO OBRIGATÓRIO!";
        }
        if (!trim($this->_arquivo->existe())) {
            $msgs[] = "DOCUMENTO DE COMPROVAÇÃO INVÁLIDO OU NÃO LOCALIZADO!";
        }
        if (!count($msgs)) {
            $tb = new TbVinculoLoteOcorrenciaPgto();
            $sql = $tb->select();
            $sql->where("id_vinculo_lote = {$this->id_vinculo_lote}");
            $sql->where("id_previsao_tipo = {$this->id_previsao_tipo}");
            $sql->where("id_bolsa_tipo = {$this->id_bolsa_tipo}");
            $sql->where("id_vinculo_lote_ocorrencia_pgto <> {$this->getId()}");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                $msgs[] = "INFORMAÇÃO DE PAGAMENTO DE LOTE JÁ CADASTRADA PARA ESTA OCORRÊNCIA!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function save($flag = false) {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            
            $errors = $this->getErrors();
            if ($errors) {
                throw new Exception(implode("<br>", $errors));
            }
            
            $lote = $this->findParentRow("TbVinculoLote");
            if (!$lote) {
                throw new Exception("Falha ao Executar Operação, Tipo de Despesa Não Disponível!");
            }
            
            $dt = $this->findParentRow("TbPrevisaoTipo");
            if (!$dt) {
                throw new Exception("Falha ao Executar Operação, Tipo de Despesa Não Disponível!");
            }
            
            $bt = $this->findParentRow("TbBolsaTipo");
            if (!$bt) {
                throw new Exception("Falha ao Executar Operação, Tipo de Ítem de Lote Não Disponível!");
            }
            
            if (!$this->_valor->valor) {
                $valor = $lote->pega_valor_total(array("tipo" => $dt->chave, "id_bolsa_tipo" => $this->id_bolsa_tipo));
                if (!$valor) {
                    throw new Exception("Falha ao Executar Operação, Valor Não Disponível!");
                }
                $this->_valor->valor = $valor;
            }
            
            $obs_lote = "Confirmação de Pagamento de Lote: {$lote->toString()}. Tipo de Despesa: {$dt->toString()}. Tipo de Ítem de Lote: {$bt->toString()}. Valor: {$this->_valor->toString()}";
            
            $this->id_valor = $this->_valor->save();
            
            $this->_arquivo->save();
            if ($this->_arquivo->getId()) {
                $this->id_arquivo = $this->_arquivo->getId();
            }
            
            if (!$this->id_vinculo_movimento) {
                $tb = new TbDespesaTipo();
                $dt = $tb->getPorChave("NO");
                if ($dt) {
                    $tb = new TbVinculoMovimento();
                    $despesa = $tb->createRowDespesa();
                    
                    $ib = false;
                    $vinculo = $lote->findParentRow("TbVinculo");
                    if ($vinculo) {
                        $ibs = $vinculo->pega_info_bancaria();
                        if ($ibs) {
                            $ib = $ibs->current();
                        }
                    }
                    if (!$ib) {
                        throw new Exception("Falha ao Executar Operação, Informação Bancária do Projeto Não Disponível!");
                    }
                    
                    $dados = array();
                    $dados["descricao"] = $obs_lote;
                    $dados["valor"] = Escola_Util::number_format($this->_valor->valor);
                    $dados["id_despesa_tipo"] = $dt->getId();
                    $dados["id_forma_pagamento"] = $this->id_forma_pagamento;
                    $dados["numero_documento"] = $this->forma_numero;
                    $dados["data_movimento"] = $this->data_cadastro;
                    $despesa->setFromArray($dados);
                    $despesa->set_info_bancaria($ib);
                    try {   
                        $despesa->save();
                        
                        $this->id_vinculo_movimento = $despesa->getId();
                    } catch (Exception $ex) {
                        throw new Exception("Falha ao Salvar Movimento Bancário: " . $ex->getMessage());
                    }
                }                
            }
            if (!$this->id_vinculo_lote_ocorrencia_pgto) {
                
                $tb = new TbVinculoLoteOcorrenciaTipo();
                $vlot = $tb->getPorChave("PG");
                if (!$vlot) {
                    throw new Exception("Falha ao Executar Operação, Tipo de Ocorrência de Pagamento Não Encontrada!");
                }
                
                $tb = new TbUsuario();
                $usuario = $tb->pegaLogado();
                if (!$usuario) {
                    throw new Exception("Falha ao Executar Operação, Nenhum Usuário Logado Disponível!");
                }
                                                
                $tb = new TbVinculoLoteOcorrencia();
                $vlo = $tb->createRow();
                $dados = array();
                $dados["data"] = Escola_Util::formatData($this->data_cadastro);
                $dados["hora"] = $this->hora_cadastro;
                $dados["observacoes"] = $obs_lote;
                $dados["id_vinculo_lote"] = $this->id_vinculo_lote;
                $dados["id_vinculo_lote_ocorrencia_tipo"] = $vlot->getId();
                $dados["id_usuario"] = $usuario->getId();
                
                $vlo->setFromArray($dados);
                try {
                    
                    $vlo->save();
                    $this->id_vinculo_lote_ocorrencia = $vlo->getId();
                    
                } catch (Exception $ex) {
                    throw new Exception("Falha ao Salvar Ocorrência: " . $ex->getMessage());
                }
            }
            
            $return_id = parent::save($flag);
            
            $items = $this->pegaVinculoLoteItem("PP"); //PAGAMENTO PENDENTE
            if ($items) {
                foreach ($items as $item) {
                    $item->pagar();
                }
            }
            
            if ($trans) {
                $db->commit();
            }
            
            return $return_id;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            
            throw $ex;
        }
    }
    
    public function getDeleteErrors() {
        $msgs = array();

        $lote = $this->findParentRow("TbVinculoLote");
        if ($lote) {
            if (!$lote->aprovado()) {
                $msgs[] = "Pagamentos de Lotes com Status diferente de APROVADO, não podem ser excluídos!";
            }
        }
        
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function delete() {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            $vlo = $this->findParentRow("TbVinculoLoteOcorrencia");
            $arquivo = $this->findParentRow("TbArquivo");
            $valor = $this->findParentRow("TbValor");
            $vm = $this->findParentRow("TbVinculoMovimento");
            
            $items = $this->pegaVinculoLoteItem("PG"); // PAGAMENTO CONFIRMADO
            if ($items) {
                foreach ($items as $item) {
                    $item->cancelar_pagamento();
                }
            }
            
            $return_id = parent::delete();
            
            if ($vlo) { $vlo->delete(); }
            if ($arquivo) { $arquivo->delete(); }
            if ($valor) { $valor->delete(); }
            if ($vm) { $vm->delete(); }
                                    
            if ($trans) {
                $db->commit();
            }
            
            return $return_id;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
    
    public function pegaVinculoLoteItem($status = "") {
        $pt = $this->findParentRow("TbPrevisaoTipo");
        $bt = $this->findParentRow("TbBolsaTipo");
        $lote = $this->findParentRow("TbVinculoLote");
        if ($pt && $bt && $lote) {
            $dados = array();
            $dados["tipo"] = $pt->chave;
            $dados["id_bolsa_tipo"] = $bt->getId();
            $dados["id_vinculo_lote"] = $lote->getId();
            if ($status) {
                $dados["vinculo_lote_item_status"] = $status;
            }
            $tb = new TbVinculoLoteItem();
            $items = $tb->listar($dados);
            if ($items && count($items)) {
                return $items;
            }
        }
        return false;
    }
}