<?php
class Tarifa extends Escola_Entidade {
    
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
        
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        $this->_valor->setFromArray($dados);
        parent::setFromArray($dados);
    }
    
    public function save() {
        $criar_ocorrencia = false;
        
        $this->id_valor_atual = $this->_valor->save();
        $salvar =  parent::save();
        $tb_to = new TbTarifaOcorrencia();
        $stmt = $this->findDependentRowset("TbTarifaOcorrencia");
        if ($stmt && count($stmt)) {
            $to_atual = $this->pega_ocorrencia_atual();
            if ($to_atual) {
                $valor_ocorrencia = $to_atual->findParentRow("TbValor");
                if ($valor_ocorrencia && ($valor_ocorrencia->valor != $this->_valor->valor)) {
                    $to_atual->data_final = date("Y-m-d");
                    $to_atual->save();
                    $criar_ocorrencia = true;
                }
            }
        } else {
            $criar_ocorrencia = true;
        }
        
        if ($criar_ocorrencia) {
            $tb_valor = new TbValor();
            $row_valor = $tb_valor->createRow();
            $row_valor->valor = $this->_valor->valor;
            $row_valor->save();
            
            $row = $tb_to->createRow();
            $row->setFromArray(array("id_tarifa" => $this->getId(),
                                     "id_valor" => $row_valor->getId()));
            $errors = $row->getErrors();
            if (!$errors) {
                $row->save();
            }
        }
        return $salvar;
    }

    public function toString() {
        return $this->descricao;
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
                if (!$this->_valor->valor) {
			$msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" descricao = '{$this->descricao}' and id_tarifa <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TARIFA JÁ CADASTRADA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTarifaOcorrencia");
        if ($registros && count($registros)) {
            foreach ($registros as $to) {
                $rotas = $to->findDependentRowset("TbRota");
                if ($rotas && count($rotas)) {
                    $msgs[] = "Existem Rotas Vinculadas a esta Tarifa! Apague as referencias antes de excluir!";
                    break;
                }
                $obts = $to->findDependentRowset("TbOnibusBdoTarifa");
                if ($obts && count($obts)) {
                    $msgs[] = "Existem BDO's Vinculados a esta Tarifa! Apague as referencias antes de excluir!";
                    break;
                }
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function delete() {
        $registros = $this->findDependentRowset("TbTarifaOcorrencia");
        if ($registros && count($registros)) {
            foreach ($registros as $to) {
                $to->delete();
            }
        }
        $valor = $this->pega_valor();
        $flag = parent::delete();
        if ($valor->getId()) {
            $valor->delete();
        }
        return $flag;
    }
    
    public function pega_ocorrencia_atual() {
        if ($this->getId()) {
            $tb = new TbTarifaOcorrencia();
            $sql = $tb->select();
            $sql->where("id_tarifa = {$this->getId()}");
            $sql->where("data_final is null");
            $sql->order("id_tarifa_ocorrencia desc");
            $stmt = $tb->fetchAll($sql);
            if ($stmt && count($stmt)) {
                return $stmt->current();
            }
        } 
        return false;
    }
}