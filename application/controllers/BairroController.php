<?php

class BairroController extends Escola_Controller
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
        $tb = new TbBairro();
        $bairros = $tb->listar($this->getRequest()->getPost());
        if ($bairros && $bairros->count()) {
            $result = array();
            foreach ($bairros as $bairro) {
                $obj = new stdClass();
                $obj->id = $bairro->id_bairro;
                $obj->descricao = $bairro->descricao;
                $obj->id_municipio = $bairro->id_municipio;
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
        $tb = new TbBairro();
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
