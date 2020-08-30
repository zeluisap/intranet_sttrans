<?php

define('TYPE_REQUEST', 0);
define('TYPE_SESSION', 1);

class Escola_Controller extends Zend_Controller_Action
{

    protected $session = null;

    protected $entidade = null;
    protected $entidade_id = null;
    protected $action_anterior = null;

    public function atualizaFiltros($filtros)
    {
        $session = Escola_Session::getInstance();
        return $session->atualizaFiltros($filtros);
    }

    public function getActionAnterior()
    {
        $anterior = $this->action_anterior;
        if (!($anterior && is_array($anterior) && count($anterior))) {
            return null;
        }
        return $anterior;
    }

    public function carregar($entidades)
    {
        if (Escola_Util::vazio($entidades)) {
            return;
        }

        if (!is_array($entidades)) {
            $entidades = [$entidades];
        }

        $entidade_encontrada = null;
        //produra no request
        foreach ($entidades as $entidade) {
            if ($this->carregaObjeto($entidade)) {
                $entidade_encontrada = $entidade;
                break;
            }
        }

        if (!$entidade_encontrada) {
            // caso não encontre, procura na sessão
            foreach ($entidades as $entidade) {
                if ($this->carregaObjeto($entidade, TYPE_SESSION)) {
                    $entidade_encontrada = $entidade;
                    break;
                }
            }
        }

        // apaga as outras entidades da sessão
        $entidade_encontrada = strtolower($entidade_encontrada);
        foreach ($entidades as $entidade) {
            $entidade = strtolower($entidade);

            $id_name = "id_" . $entidade;

            if ($entidade_encontrada != $entidade) {
                unset($this->session->$id_name);
            }
        }
    }

    public function carregaObjeto($entidade, $type = TYPE_REQUEST)
    {

        if (Escola_Util::vazio($entidade)) {
            return false;
        }

        $entidade = strtolower($entidade);
        $id_name = "id_" . $entidade;
        $tb_nome = Escola_Tabela::getTabelaPorEntidade($entidade);
        if (Escola_Util::vazio($tb_nome)) {
            return false;
        }

        $obj = null;

        switch ($type) {
            case TYPE_REQUEST:
                $id = $this->_request->getParam($id_name);
                break;
            case TYPE_SESSION:
                $id = $this->session->$id_name;
                break;
        }

        if (!$id) {
            return false;
        }

        $obj = $tb_nome::pegaPorId($id);
        if (!$obj) {
            return false;
        }

        $this->session->$id_name = $id;

        $this->view->$entidade = $obj;

        $this->$entidade = $obj;

        $this->entidade = $entidade;
        $this->entidade_id = $id;

        $this->addGrupoEntidade($entidade);

        return true;
    }

    public function addGrupoEntidade($entidade, $legenda = null)
    {

        if (!$this->$entidade) {
            if (isset($this->session->header_grupo[$entidade])) {
                unset($this->session->header_grupo[$entidade]);
                return;
            }
        }

        if (!isset($this->session->header_grupo)) {
            $this->session->header_group = [];
        }

        if (Escola_Util::vazio($legenda)) {
            $tb_nome = Escola_Tabela::getTabelaPorEntidade($entidade);
            if (!Escola_Util::vazio($tb_nome)) {
                $legenda = $tb_nome::getLegenda();
                $legenda = ucfirst($legenda);
            }
        }

        if (!$this->$entidade) {
            return;
        }

        $this->session->header_grupo[$entidade] = [
            "legenda" => $legenda,
            "valor" => $this->$entidade->toString()
        ];
    }

    public function removeGrupoValor($chaves = null)
    {

        if (Escola_Util::vazio($chaves)) {
            $objs = $this->getHeaderGrupo();
            $chaves = [];
            foreach ($objs as $chave => $obj) {
                $chaves[] = $chave;
            }
        }

        if (!is_array($chaves)) {
            $chaves = [$chaves];
        }

        foreach ($chaves as $chave) {
            unset($this->session->header_grupo[$chave]);
        }
    }

    public function getHeaderGrupo($chave = null)
    {

        return $this->view->getHeaderGrupo($chave);
    }

    public function getHeaderGrupoValor($chave = null)
    {

        $obj = $this->view->getHeaderGrupo($chave);

        return Escola_Util::valorOuNulo($obj, "valor");
    }


    public function addGrupoValor($chave, $legenda = null, $valor = null)
    {

        if (Escola_Util::vazio($valor)) {
            if (isset($this->session->header_grupo[$chave])) {
                unset($this->session->header_grupo[$chave]);
                return;
            }
        }

        if (!isset($this->session->header_grupo)) {
            $this->session->header_group = [];
        }

        if (Escola_Util::vazio($legenda)) {
            $legenda = $chave;
        }

        $this->session->header_grupo[$chave] = [
            "legenda" => $legenda,
            "valor" => $valor
        ];
    }

    public function preDispatch()
    {

        $this->session = Escola_Session::getInstance();

        $controller = $this->getRequest()->getControllerName();
        if ($controller != "error") {
            //página online
            $online = true;
            $config = Zend_Registry::get("config");

            if (isset($config->sistema->online)) {
                $online = $config->sistema->online;
            }

            if (!$online) {
                $this->_redirect("error/indisponivel");
            }
        }

        $this->view->actionErrors = $this->_getFlashMessages("Errors");
        $this->view->actionMessages = $this->_getFlashMessages("Messages");

        $action = $this->getRequest()->getActionName();
        if (!(($controller == "error") || (($controller == "auth") && ($action == "sistema" || $action == "salvar")))) {
            $tb_sistema = new TbSistema();
            $sistema = $tb_sistema->pegaSistema();
            if (!$sistema) {
                $msgs = $this->_getFlashMessages("Errors");
                $this->_flashMessage("Nenhuma informação do Sistema cadastrada, efetue o cadastro antes de prosseguir!");
                if (count($msgs)) {
                    foreach ($msgs as $msg) {
                        $this->_flashMessage($msg);
                    }
                }
                $this->_redirect("auth/sistema");
            }
        }

        $view = $this->initView();
        $paths = $view->getScriptPaths();

        $front = Zend_Controller_Front::getInstance();
        $filename = $paths[0] . $controller . "/_css.css";
        if (file_exists($filename)) {
            $view->headStyle()->appendStyle(file_get_contents($filename));
        }
        $filename = $paths[0] . $controller . "/" . $action . ".css";
        if (file_exists($filename)) {
            $view->headStyle()->appendStyle(file_get_contents($filename));
        }
        ob_start();
        include(ROOT_DIR . "/public/js/" . $this->_helper->layout->getLayout() . "/site.js");
        $script = ob_get_contents();
        ob_end_clean();
        if (trim($script)) {
            $view->headScript()->appendScript($script);
        }

        $this->caminhando();
    }

    private function caminhando()
    {

        if ($this->_request->isXmlHttpRequest()) {
            return;
        }

        $session = Escola_Session::getInstance();
        $caminhos = $session->action_caminhos;
        if (!$caminhos) {
            $caminhos = [];
        }

        $novo = $this->_request->getParams();
        if (count($caminhos)) {
            $this->action_anterior = $this->getAnterior($caminhos, $novo);
        }

        $caminhos[] = $novo;

        $caminhos = array_filter($caminhos, function ($caminho) {
            return $caminho && is_array($caminho) && count($caminho);
        });

        $session->action_caminhos = array_values($caminhos);
    }

    public function postDispatch()
    {
        /* início recovery */
        /*
          $tb_pais = new TbPais(); $tb_pais->recuperar();
          $tb_uf = new TbUf(); $tb_uf->recuperar();
          $tb = new TbMunicipio(); $tb->recuperar();
          $tb = new TbBairro(); $tb->recuperar();
          $tb = new TbUsuarioSituacao(); $tb->recuperar();
          $tb_modulo = new TbModulo(); $tb_modulo->recuperar();
          $tb = new TbSetorNivel(); $tb->recuperar();
          $tb = new TbFuncionarioFuncaoTipo(); $tb->recuperar();
          $tb = new TbLotacaoTipo(); $tb->recuperar();
          $tb = new TbFuncionarioSituacao(); $tb->recuperar();
          $tb = new TbCargoTipo(); $tb->recuperar();
          $tb = new TbArquivoTipo(); $tb->recuperar();
          $tb = new TbMensagemTipo(); $tb->recuperar();
          $tb = new TbInfoTipo(); $tb->recuperar();
          $tb = new TbInfoStatus(); $tb->recuperar();
          $tb = new TbMenuPosicao(); $tb->recuperar();
          $tb = new TbMenuTipo(); $tb->recuperar();
          $tb = new TbMensagemTipo(); $tb->recuperar();
          $tb = new TbComentarioStatus(); $tb->recuperar();
          $tb = new TbDocumentoTipoTarget(); $tb->recuperar();
          $tb = new TbDocumentoModo(); $tb->recuperar();
          $tb = new TbDocumentoStatus(); $tb->recuperar();
          $tb = new TbSetorTipo(); $tb->recuperar();
          $tb = new TbMovimentacaoTipo(); $tb->recuperar();
          $tb = new TbChamadoStatus(); $tb->recuperar();
          $tb = new TbChamadoOcorrenciaTipo(); $tb->recuperar();
          $tb = new TbFuncionarioOcorrenciaTipo(); $tb->recuperar();
          $tb = new TbPrioridade(); $tb->recuperar();
          $tb = new TbPortalStatus(); $tb->recuperar();
          $tb = new TbIconeTipo(); $tb->recuperar();
          $tb = new TbPacote(); $tb->recuperar();
          $tb = new TbVinculoTipo(); $tb->recuperar();
          $tb = new TbVinculoStatus(); $tb->recuperar();
          $tb = new TbInfoBancariaTipo(); $tb->recuperar();
          $tb = new TbBolsistaStatus(); $tb->recuperar();
          $tb = new TbPrevisaoTipo(); $tb->recuperar();
          $tb = new TbVinculoLoteStatus(); $tb->recuperar();
          $tb = new TbVinculoLoteItemStatus(); $tb->recuperar();
          $tb = new TbAditivoTipo(); $tb->recuperar();
          $tb = new TbVinculoLoteOcorrenciaTipo(); $tb->recuperar();
          $tb = new TbServicoTipo(); $tb->recuperar();
          $tb = new TbTransportePessoaTipo(); $tb->recuperar();
          $tb = new TbTransporteVeiculoStatus(); $tb->recuperar();
          $tb = new TbServicoSolicitacaoStatus(); $tb->recuperar();
          $tb = new TbServicoSolicitacaoPagamentoStatus(); $tb->recuperar();
          $tb = new TbTransporteGrupo(); $tb->recuperar();
          /* fim recovery */

        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $view = $this->initView();
        $paths = $view->getScriptPaths();
        $script = "";
        ob_start();
        if (file_exists($paths[0] . $controller . "/_js.php")) {
            include($paths[0] . $controller . "/_js.php");
        }
        if (file_exists($paths[0] . $controller . "/" . $action . ".js.php")) {
            include($paths[0] . $controller . "/" . $action . ".js.php");
        }
        $script = ob_get_contents();
        ob_end_clean();
        if (trim($script)) {
            $view->headScript()->appendScript($script);
        }
        $alerta = Escola_Alerta::getInstance();
        $tb = new TbFuncionario();
        $funcionario = $tb->pegaLogado();
        if ($funcionario) {
            $alerta->add($funcionario);
        }
    }

    protected function _flashMessage($message, $option = "Errors")
    {
        $flashMessage = $this->_helper->FlashMessenger;
        $flashMessage->setNamespace("action" . $option);
        $flashMessage->addMessage($message);
    }

    protected function _getFlashMessages($option)
    {
        $flashMessage = $this->_helper->FlashMessenger;
        $flashMessage->setNamespace("action" . $option);
        return $flashMessage->getMessages();
    }

    public function addErro($erro)
    {
        $this->_flashMessage($erro);
    }

    public function addMensagem($mensagem)
    {
        $this->_flashMessage($mensagem, "Messages");
    }

    private function getAnterior($caminhos, $novo)
    {
        if (empty($caminhos)) {
            return null;
        }

        $novo_controller = Escola_Util::valorOuNulo($novo, "controller");
        $novo_action = Escola_Util::valorOuNulo($novo, "action");

        $caminhos = array_reverse($caminhos);
        foreach ($caminhos as $caminho) {
            $controller = Escola_Util::valorOuNulo($caminho, "controller");
            $action = Escola_Util::valorOuNulo($caminho, "action");

            if ($controller !== $novo_controller) {
                return $caminho;
            }

            if ($action !== $novo_action) {
                return $caminho;
            }
        }

        return null;
    }
}
