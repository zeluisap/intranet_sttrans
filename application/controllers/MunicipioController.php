<?php

class MunicipioController extends Escola_Controller
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("listar", "json");
        $ajaxContext->addActionContext("salvar", "json");
        $ajaxContext->initContext();
    }

    public function listarAction()
    {
        $result = false;
        $tb = new TbMunicipio();
        $municipios = $tb->listar($this->getRequest()->getPost());
        if ($municipios && $municipios->count()) {
            $result = array();
            foreach ($municipios as $municipio) {
                $obj = new stdClass();
                $obj->id = $municipio->id_municipio;
                $obj->descricao = $municipio->descricao;
                $obj->id_uf = $municipio->id_uf;
                $result[] = $obj;
            }
        }
        $this->view->result = $result;
    }

    public function salvarAction()
    {
        $result = new stdClass;
        $result->mensagem = false;
        $result->id = 0;
        $tb = new TbMunicipio();
        $row = $tb->createRow();
        $dados = $this->getRequest()->getPost();
        $row->setFromArray($dados);
        $errors = $row->getErrors();
        if ($errors) {
            $result->mensagem = implode("<br>", $errors);
        } else {
            $id = $row->save();
            if ($id) {
                $result->id = $id;
            }
        }
        $this->view->result = $result;
    }
}
