<?php
class RotaviagemController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("info", "json");
        $ajaxContext->addActionContext("salvar", "json");
        $ajaxContext->addActionContext("excluir", "json");
        $ajaxContext->initContext();
    }

    public function infoAction()
    {
        $this->view->result = false;
        $id_rota = $this->_request->getPost("id_rota");
        if ($id_rota) {
            $rota = TbRota::pegaPorId($id_rota);
            if ($rota) {
                $tb = new TbRotaViagem();
                $items = array();
                $dia_semanas = Escola_Util::listarDiaSemana();
                foreach ($dia_semanas as $dia_semana => $dia_semana_texto) {
                    $rvs = $tb->listar(array("id_rota" => $rota->getId(), "dia_semana" => $dia_semana));
                    if ($rvs && count($rvs)) {
                        $horas = array();
                        foreach ($rvs as $rv) {
                            $item = new stdClass();
                            $item->id_rota_viagem = $rv->getId();
                            $item->hora_saida = $rv->hora_saida;
                            $horas[] = $item;
                        }
                        $items[$dia_semana] = $horas;
                    }
                }
                if (count($items)) {
                    $this->view->result = $items;
                }
            }
        }
    }

    public function salvarAction()
    {
        $this->view->erro = false;
        $dados = $this->_request->getPost();
        $tb = new TbRotaViagem();
        $rv = $tb->createRow();
        $rv->setFromArray($dados);
        $erros = $rv->getErrors();
        if ($erros) {
            $this->view->erro = implode("<br>", $erros);
        } else {
            $rv->save();
        }
    }

    public function excluirAction()
    {
        $this->view->erro = false;
        $id = $this->_request->getPost("id");
        $rv = TbRotaViagem::pegaPorId($id);
        if ($rv && $rv->getId()) {
            $erros = $rv->getDeleteErrors();
            if ($erros) {
                $this->view->erro = implode("<br>", $erros);
            } else {
                $rv->delete();
            }
        } else {
            $this->view->erro = "FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!";
        }
    }
}
