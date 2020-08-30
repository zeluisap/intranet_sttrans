<?php
class Bolsista extends Escola_Entidade {
    
    protected $_info_bancaria = false;
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $tb = new TbBolsistaStatus();
            $bs = $tb->getPorChave("A");
            if ($bs) {
                $this->id_bolsista_status = $bs->getId();
            }
        } else {
            $tb = new TbInfoBancariaRef();
            $objs = $tb->listar(array("tipo" => "B", "chave" => $this->getId()));
            if ($objs && $objs->count()) {
                $this->_info_bancaria = $objs->current()->findParentRow("TbInfoBancaria");
            }
        }
    }
    
    public function pega_info_bancaria() {
        if ($this->_info_bancaria) {
            return $this->_info_bancaria;
        }
        $tb = new TbInfoBancaria();
        return $tb->createRow();
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["id_info_bancaria"]) && $dados["id_info_bancaria"]) {
            $ib = TbInfoBancaria::pegaPorId($dados["id_info_bancaria"]);
            if ($ib->getId()) {
                $this->_info_bancaria = $ib;
            }
        }
        parent::setFromArray($dados);
    }
    
	public function getErrors() {
		$msgs = array();
		if (empty($this->id_vinculo)) {
			$msgs[] = "CAMPO VÍNCULO OBRIGATÓRIO!";
		}
		if (empty($this->id_bolsa_tipo)) {
			$msgs[] = "CAMPO TIPO DE BOLSA OBRIGATÓRIO!";
		}
		if (empty($this->id_pessoa_fisica)) {
			$msgs[] = "CAMPO PESSOA OBRIGATÓRIO!";
		}
        if (!$this->_info_bancaria) {
            $msgs[] = "CAMPO INFORMAÇÕES BANCÁRIAS OBRIGATÓRIO!";
        }
		$rg = $this->getTable()->fetchAll("id_vinculo = '{$this->id_vinculo}' and id_pessoa_fisica = '{$this->id_pessoa_fisica}' and id_bolsa_tipo = '{$this->id_bolsa_tipo}' and id_bolsista <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "BOLSISTA JÁ CADASTRADO PARA ESTE CONVÊNIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function getDeleteErrors() {
        $msgs = array();
		$db = Zend_Registry::get("db");
        $tb = new TbVinculoLoteItem();
        $sql = $tb->select();
        $sql->where("tipo = 'BO'");
        $sql->where("chave = {$this->getId()}");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $msgs[] = "Existem Pagamentos vinculados a este bolsista, Exclusão não permitida, Chame o Administrador!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
    }
    
    public function save() {
        $id_anterior = $this->getId();
        $id = parent::save();
        if (!$id_anterior && $id) {
            $usuario = TbUsuario::pegaLogado();
            if ($usuario) {
                $tb = new TbBolsistaOcorrencia();
                $row = $tb->createRow();
                $row->setFromArray(array("id_bolsista" => $this->getId(),
                                         "id_usuario" => $usuario->getId(),
                                         "descricao" => "Bolsista Vinculado ao " . $this->findParentRow("TbVinculo")->findParentRow("TbVinculoTipo")->toString() . " com status: " . $this->findParentRow("TbBolsistaStatus")->toString() . "!"));
                $errors = $row->getErrors();
                if (!$errors) {
                    $row->save();
                }
            }
        }
        if ($this->_info_bancaria) {
            $tb = new TbInfoBancariaRef();
            $objs = $tb->listar(array("tipo" => "B", "chave" => $this->getId()));
            if ($objs) {
                foreach ($objs as $obj) {
                    $obj->delete();
                }
            }
            $tb = new TbInfoBancariaRef();
            $ibr = $tb->createRow();
            $ibr->setFromArray(array("id_info_bancaria" => $this->_info_bancaria->getId(),
                                     "tipo" => "B",
                                     "chave" => $this->getId()));
            $errors = $ibr->getErrors();
            if (!$errors) {
                $ibr->save();
            }
        }
        return $id;
    }
    
    public function delete() {
        $bos = $this->findDependentRowSet("TbBolsistaOcorrencia");
        if ($bos) {
            foreach ($bos as $bo) {
                $bo->delete();
            }
        }
        return parent::delete();
    }
    
    public function pega_ocorrencia() {
        $tb = new TbBolsistaOcorrencia();
        return $tb->listar(array("id_bolsista" => $this->getId()));
    }
    
    public function ativo() {
        $status = $this->findParentRow("TbBolsistaStatus");
        if ($status) {
            return $status->ativo();
        }
        return false;
    }
    
    public function muda_status($status) {
        $tb = new TbBolsistaStatus();
        $status = $tb->getPorChave($status);
        if ($status) {
            $this->id_bolsista_status = $status->getId();
            $this->save();
        }
    }
    
    public function ativar() {
        if (!$this->ativo()) {
            $this->muda_status("A");
            if ($this->ativo()) {
                $usuario = TbUsuario::pegaLogado();
                if ($usuario) {
                    $tb = new TbBolsistaOcorrencia();
                    $row = $tb->createRow();
                    $row->setFromArray(array("id_bolsista" => $this->getId(),
                                             "id_usuario" => $usuario->getId(),
                                             "descricao" => "Status do Bolsista alterado para: " . $this->findParentRow("TbBolsistaStatus")->toString() . "!"));
                    $errors = $row->getErrors();
                    if (!$errors) {
                        $row->save();
                    }
                }
            }
        }
    }
    
    public function desativar() {
        if ($this->ativo()) {
            $this->muda_status("I");
            if (!$this->ativo()) {
                $usuario = TbUsuario::pegaLogado();
                if ($usuario) {
                    $tb = new TbBolsistaOcorrencia();
                    $row = $tb->createRow();
                    $row->setFromArray(array("id_bolsista" => $this->getId(),
                                             "id_usuario" => $usuario->getId(),
                                             "descricao" => "Status do Bolsista alterado para: " . $this->findParentRow("TbBolsistaStatus")->toString() . "!"));
                    $errors = $row->getErrors();
                    if (!$errors) {
                        $row->save();
                    }
                }
            }
        }
    }
    
    public function toString() {
        $infos = array();
        $bt = $this->findParentRow("TbBolsaTipo");
        if ($bt) {
            //$infos[] = $bt->toString();
        }
        $infos[] = $this->findParentRow("TbPessoaFisica")->toString();
        return implode(" - ", $infos);
    }
}