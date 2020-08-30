<?php
class VinculoPessoa extends Escola_Entidade {
    
    public function getErrors() {
        $msgs = array();
        if (!$this->id_vinculo_pessoa_tipo) {
            $msgs[] = "CAMPO TIPO DE VÍNCULO COM PESSOA OBRIGATÓRIO!";
        }                
        if (!trim($this->id_vinculo)) {
            $msgs[] = "CAMPO PROJETO OBRIGATÓRIO!";
        }
        if (!trim($this->id_pessoa_fisica)) {
            $msgs[] = "CAMPO PESSOA FÍSICA OBRIGATÓRIO!";
        }
        if ($this->getId() && !count($msgs)) {
            $tb = $this->getTable();
            $sql = $tb->select();
            $sql->where("id_vinculo_pessoa_tipo = {$this->id_vinculo_pessoa_tipo}");
            $sql->where("id_vinculo = {$this->id_vinculo}");
            $sql->where("id_pessoa_fisica = {$this->id_pessoa_fisica}");
            $sql->where("id_vinculo_pessoa <> {$this->getId()}");
            $stmt = $tb->fetchAll($sql);
            if ($stmt && count($stmt)) {
                $msgs[] = "VÍNCULO ENTRE PROJETO E PESSOA JÁ CADASTRADO!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function save($flag = false) {
        $id = parent::save($flag);
        $vpt = $this->findParentRow("TbVinculoPessoaTipo");
        if ($vpt && $vpt->coordenador()) {
            $tb = new TbGrupo();
            $grupo = $tb->getPorDescricao("COORDENADOR LOTE");
            if ($grupo) {
                $pf = $this->findParentRow("TbPessoaFisica");
                if ($pf) {
                    $tb = new TbUsuario();
                    $usuarios = $tb->getPorPessoaFisica($pf);
                    $usuario = false;
                    if ($usuarios) {
                        $usuario = $usuarios->current();
                    } else {
                        $obj = $tb->createRow();
                        $obj->setFromArray(array("id_pessoa_fisica" => $pf->getId(), "senha" => $pf->cpf));
                        $errors = $obj->getErrors();
                        if (!$errors) {
                            $obj->save();
                        }
                        if ($obj->getId()) {
                            $usuario = $obj;
                        }
                    }
                    if ($usuario) {
                        $usuario->addGrupo($grupo);
                    }
                }
            }
        }
        return $id;
    }
    
    public function delete() {
        $vpt = $this->findParentRow("TbVinculoPessoaTipo");
        $pf = $this->findParentRow("TbPessoaFisica");
        $flag = parent::delete();
        $tb = new TbGrupo();
        $grupo = $tb->getPorDescricao("COORDENADOR LOTE");
        if ($grupo) {
            if ($vpt && $vpt->coordenador() && $pf) {
                $tb = $this->getTable();
                $sql = $tb->select();
                $sql->where("id_vinculo_pessoa_tipo = {$vpt->getId()}");
                $sql->where("id_pessoa_fisica = {$pf->getId()}");
                $stmt = $tb->fetchAll($sql);
                if (!($stmt && count($stmt))) {
                    $tb = new TbUsuario();
                    $usuarios = $tb->getPorPessoaFisica($pf);
                    if ($usuarios && count($usuarios)) {
                        $usuario = $usuarios->current();
                        $usuario->removeGrupo($grupo);
                    }
                }
            }
        }
        return $flag;
    }
}