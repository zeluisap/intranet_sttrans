<?php
class AutoinfracaonotificacaoController extends Escola_Controller_Logado
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper("AjaxContext");
        $ajaxContext->addActionContext("html", "json");
        $ajaxContext->addActionContext("listarporpagina", "json");
        $ajaxContext->initContext();
    }

    public function htmlAction()
    {
        $html = false;
        $id = $this->_request->getPost("id");
        if ($id) {
            $ain = TbAutoInfracaoNotificacao::pegaPorId($id);
            if ($ain) {
                $html = $ain->view($this->view);
            }
        }
        $this->view->html = $html;
    }

    public function listarporpaginaAction()
    {
        $dados = $this->getRequest()->getPost();
        $tb = new TbAutoInfracaoNotificacao();
        $registros = $tb->listar_por_pagina($dados);
        $info = $registros->getPages();
        $this->view->items = false;
        $this->view->total_pagina = $info->pageCount;
        $this->view->pagina_atual = $info->current;
        $this->view->primeira = $info->first;
        $this->view->ultima = $info->last;
        if ($registros && count($registros)) {
            $items = array();
            foreach ($registros as $registro) {
                $txt_auto_infracao = $txt_ocorrencia = $txt_data_hora = $txt_veiculo = $txt_motorista = $txt_valor_total = $txt_status_pagamento = "--";
                $aio = $registro->pegaOcorrencia();
                if ($aio) {
                    $txt_ocorrencia = $aio->toString();
                    $ai = $aio->findParentRow("TbAutoInfracao");
                    if ($ai) {
                        $txt_auto_infracao = $ai->toString();
                    }
                }
                $txt_data_hora = Escola_Util::formatData($registro->data_infracao) . " " . $registro->hora_infracao;
                $veiculo = $registro->findParentRow("TbVeiculo");
                if ($veiculo) {
                    $txt_veiculo = $veiculo->toString();
                }
                $pf = $registro->findParentRow("TbPessoaFisica");
                if ($pf) {
                    $txt_motorista = $pf->toString();
                }
                $txt_valor_total = Escola_Util::number_format($registro->pegaValorTotal());
                $ss = $registro->pegaServicoSolicitacao();
                if ($ss) {
                    if ($ss->aguardando_pagamento()) {
                        $emitir_boleto = true;
                    }
                    $sss = $ss->findParentRow("TbServicoSolicitacaoStatus");
                    if ($sss) {
                        $txt_status_pagamento = $sss->toString();
                    }
                }

                $obj = new stdClass();
                $obj->id = $registro->getId();
                $obj->auto_infracao = $txt_auto_infracao;
                $obj->ocorrencia = $txt_ocorrencia;
                $obj->data_hora = $txt_data_hora;
                $obj->veiculo = $txt_veiculo;
                $obj->motorista = $txt_motorista;
                $obj->valor_total = $txt_valor_total;
                $obj->status_pagamento = $txt_status_pagamento;
                $obj->tostring = $registro->toString();
                $items[] = $obj;
            }
            $this->view->items = $items;
        }
    }
}
