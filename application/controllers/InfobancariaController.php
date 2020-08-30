<?php
class InfobancariaController extends Escola_Controller
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
        $dados = $this->_request->getPost();
        if ((isset($dados["tipo"]) && $dados["tipo"]) && (isset($dados["chave"]) && $dados["chave"])) {
            $tb = new TbInfoBancariaRef();
            $ibrs = $tb->listar($dados);
            if ($ibrs && $ibrs->count()) {
                $result = array();
                foreach ($ibrs as $ibr) {
                    $ib = $ibr->findParentRow("TbInfoBancaria");
                    $obj = new stdClass();
                    $obj->id = $ib->getId();
                    $obj->descricao = $ib->toString();
                    $result[] = $obj;
                }
            }
        }
        $this->view->result = $result;
    }

    public function salvarAction()
    {
        $result = new stdClass();
        $result->id = false;
        $result->descricao = false;
        $result->mensagem = false;
        $dados = $this->_request->getPost();
        $tb = new TbInfoBancaria();
        $ib = $tb->createRow();
        $ib->setFromArray($dados);
        $errors = $ib->getErrors();
        if ($errors) {
            $this->mensagem = implode("<br>", $errors);
        } else {
            $result->id = $ib->save();
            if (!$result->id) {
                $result->mensagem = "Falha ao Executar Operação, Chame o Administrador!";
            } else {
                $result->descricao = $ib->toString();
                if ((isset($dados["tipo"]) && $dados["tipo"]) && (isset($dados["chave"]) && $dados["chave"])) {
                    $tb = new TbInfoBancariaRef();
                    $ibr = $tb->createRow();
                    $ibr->setFromArray(array(
                        "id_info_bancaria" => $ib->getId(),
                        "tipo" => $dados["tipo"],
                        "chave" => $dados["chave"]
                    ));
                    $errors = $ibr->getErrors();
                    if (!$errors) {
                        $ibr->save();
                    }
                }
            }
        }
        $this->view->result = $result;
    }
}
