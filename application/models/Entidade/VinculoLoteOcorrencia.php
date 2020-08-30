<?php
class VinculoLoteOcorrencia extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data = date("Y-m-d");
            $this->hora = date("H:i:s");
        }
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["data"])) {
            $dados["data"] = Escola_Util::montaData($dados["data"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->id_vinculo_lote_ocorrencia_tipo)) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (!trim($this->id_vinculo_lote)) {
			$msgs[] = "CAMPO LOTE OBRIGATÓRIO!";
		}
		if (!trim($this->id_usuario)) {
			$msgs[] = "CAMPO USUÁRIO OBRIGATÓRIO!";
		}
		if (!Escola_Util::validaData($this->data)) {
			$msgs[] = "CAMPO DATA INVÁLIDO!";
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
            $id = $this->getId();
            
            $errors = $this->getErrors();
            if ($errors) {
                throw new Exception(implode("<br>", $errors));
            }
            
            $return = parent::save($flag);
/*
            if (!$id && $this->pagamento()) {
                $tb = new TbDespesaTipo();
                $dt = $tb->getPorChave("NO");
                if ($dt) {
                    $tb = new TbVinculoMovimento();
                    $despesa = $tb->createRowDespesa();
                    $lote = $this->findParentRow("TbVinculoLote");
                    if ($lote) {
                        $ib = false;
                        $vinculo = $lote->findParentRow("TbVinculo");
                        if ($vinculo) {
                            $ibs = $vinculo->pega_info_bancaria();
                            if ($ibs) {
                                $ib = $ibs->current();
                            }
                        }
                        if ($ib) {
                            $tb = new TbFormaPagamento();
                            $fp = $tb->getPorChave("DI");
                            if ($fp) {
                                $dados = array();
                                $dados["descricao"] = "PAGAMENTO DO LOTE : " . $lote->toString();
                                $dados["valor"] = Escola_Util::number_format($lote->pega_valor_total());
                                $dados["id_despesa_tipo"] = $dt->getId();
                                $dados["id_forma_pagamento"] = $fp->getId();
                                $dados["data_movimento"] = $this->data;
                                $despesa->setFromArray($dados);
                                $despesa->set_info_bancaria($ib);
                                $erros = $despesa->getErrors();
                                if (!$erros) {
                                    $despesa->save();
                                } else {
                                    Zend_Debug::dump($erros); die();
                                }
                            }
                        }
                    }
                }
            }
*/
            if ($trans) {
                $db->commit();
            }
            return $return;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
    
    public function delete() {
        $tb = new TbVinculoLoteOcorrenciaPgto();
        $vlops = $tb->listar(array("id_vinculo_lote_ocorrencia" => $this->getId()));
        if ($vlops && count($vlops)) {
            foreach ($vlops as $vlop) {
                $vlop->delete();
            }
        }
        return parent::delete();
    }
    
    public function pega_arquivo_pc() {
        $arquivo = $this->findParentRow("TbArquivo");
        if ($arquivo && file_exists($arquivo->pegaNomeCompleto())) {
            return $arquivo;
        }
        return false;
    }
    
    public function mostrar_observacoes() {
        $obs = $this->observacoes;
        $arquivo = $this->pega_arquivo_pc();
        if ($arquivo) {
            $obs .= ' - <a href="' . $arquivo->getLink() . '">Arquivo Prestação de Contas.</a>';
        }
        return $obs;
    }
    
    public function pagamento() {
        $tipo = $this->findParentRow("TbVinculoLoteOcorrenciaTipo");
        if ($tipo && $tipo->pagamento()) {
            return true;
        }
        return false;
    }
}