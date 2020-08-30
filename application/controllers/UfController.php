<?php
class UfController extends Escola_Controller
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
        $tb = new TbUf();
        $ufs = $tb->listar($this->getRequest()->getPost());
        if ($ufs && $ufs->count()) {
            $result = array();
            foreach ($ufs as $uf) {
                $obj = new stdClass();
                $obj->id = $uf->id_uf;
                $obj->descricao = $uf->descricao;
                $obj->sigla = $uf->sigla;
                $result[] = $obj;
            }
        }
        $this->view->result = $result;
    }

    public function salvarAction()
    {
        $result = new stdClass();
        $result->mensagem = false;
        $result->id = 0;
        $dados = $this->_request->getPost();
        $tb = new TbUf();
        $registro = $tb->createRow();
        if (isset($dados["descricao"]) && $dados["descricao"]) {
            $registro->descricao = utf8_decode($dados["descricao"]);
        }
        if (isset($dados["id_pais"]) && $dados["id_pais"]) {
            $registro->id_pais = $dados["id_pais"];
        }
        $errors = $registro->getErrors();
        if (!$errors) {
            $registro->save();
            $result->id = $registro->getId();
        } else {
            $result->mensagem = implode("<br>", $errors);
        }
        $this->view->result = $result;
    }
}
