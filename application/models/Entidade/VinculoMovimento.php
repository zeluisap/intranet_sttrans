<?php
class VinculoMovimento extends Escola_Entidade {
    
    protected $_valor = false;
    protected $_valor_anterior = false;
    protected $_valor_posterior = 0;
    protected $_info_bancaria = false;
    
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
    
    public function pega_valor_anterior() {
        if ($this->_valor_anterior) {
            return $this->_valor_anterior;
        }
        $valor = false;
        $tb = new TbValor();
        if ($this->id_valor_anterior) {
            $vlr = $tb->pegaPorId($this->id_valor_anterior);
            if ($vlr) {
                return $vlr;
            }
        }
        $tb = new TbValor();
        $valor = $tb->createRow();
        return $valor;
    }
    
    public function pega_valor_posterior() {
        return $this->_valor_posterior;
    }
    
    public function set_info_bancaria($info_bancaria) {
        $this->_info_bancaria = $info_bancaria;
    }
    
    public function pega_info_bancaria($info_bancaria) {
        return $this->_info_bancaria;
    }
    
    public function init() {
        parent::init();
        $this->_valor = $this->pega_valor();
        $this->_valor_anterior = $this->pega_valor_anterior();
        if (!$this->getId()) {
            $this->data_movimento = date("Y-m-d");
            $this->loadInfoBancaria();
        }
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["data_movimento"])) {
            $dados["data_movimento"] = Escola_Util::montaData($dados["data_movimento"]);
        }
        $this->_valor->setFromArray($dados);
        $this->_valor_anterior->setFromArray($dados);
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!Escola_Util::limpaNumero($this->data_movimento)) {
			$msgs[] = "CAMPO DATA DE MOVIMENTAÇÃO OBRIGATÓRIO!";
		} elseif (!Escola_Util::validaData($this->data_movimento)) {
			$msgs[] = "CAMPO DATA DE MOVIMENTAÇÃO INVÁLIDO!";
		}
        if (!$this->id_vinculo_movimento_tipo) {
			$msgs[] = "CAMPO TIPO DE MOVIMENTO OBRIGATÓRIO!";
		}                
        if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}                
        if (!$this->_valor->valor) {
			$msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
		}
        if (!$this->_info_bancaria) {
            $msgs[] = "NENHUMA CONTA BANCÁRIA DISPONÍVEL!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
     
    public function save() {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            
            $erros = $this->getErrors();
            if ($erros) {
                throw new Exception(implode("<br>", $erros));
            }
            
            $this->id_valor = $this->_valor->save();
            $this->id_valor_anterior = $this->_valor_anterior->save();
            $id = parent::save();
            if ($this->_info_bancaria) {
                $this->_valor_anterior->valor = $this->_info_bancaria->pegaSaldo();
                $tb = new TbInfoBancariaRef();
                $ibr = $tb->createRow();
                $ibr->setFromArray(array("tipo" => "VM", 
                                         "chave" => $this->getId(), 
                                         "id_info_bancaria" => $this->_info_bancaria->getId()));
                $errors = $ibr->getErrors();
                if (!$errors) {
                    $ibr->save();
                }
                $this->_info_bancaria->atualizaSaldoAnterior();
            }
            if ($trans) {
                $db->commit();
            }
            return $id;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }            
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
            
            $id_valor = $this->_valor->getId();
            $id_valor_anterior = $this->_valor_anterior->getId();
            $tb = new TbInfoBancariaRef();
            $ibs = $tb->listar(array("tipo" => "VM", "chave" => $this->getId()));
            if ($ibs && count($ibs)) {
                foreach ($ibs as $ib) {
                    $ib->delete();
                }
            }
            $obj = $this->pegaVinculoLoteOcorrenciaPgto();
            if ($obj) {
                $obj->delete();
            }
            $return = parent::delete();
            $tb = new TbValor();
            $valor = $tb->pegaPorId($id_valor);
            if ($valor) {
                $valor->delete();
            }
            $valor = $tb->pegaPorId($id_valor_anterior);
            if ($valor) {
                $valor->delete();
            }
            
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
    
    public function loadInfoBancaria() {
        $this->_info_bancaria = false;
        if ($this->getId()) {
            $tb = new TbInfoBancariaRef();
            $ibrs = $tb->listar();
            if ($ibrs && count($ibrs)) {
                $ibr = $ibrs->current();
                $this->_info_bancaria = $ibr->findParentRow("TbInfoBancaria");
            }
        }
    }
    
    public function pegaVinculoLoteOcorrenciaPgto() {
        $objs = $this->findDependentRowset("TbVinculoLoteOcorrenciaPgto");
        if ($objs) {
            return $objs->current();
        }
        return false;
    }
    
    public function pegaDocComprovacao() {
        $vlop = $this->pegaVinculoLoteOcorrenciaPgto();
        if ($vlop) {
            $arquivo = $vlop->findParentRow("TbArquivo");
            if ($arquivo) {
                return $arquivo;
            }
        }
        return false;
    }
}