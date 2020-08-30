<?php
class Escola_Session extends Zend_Session_Namespace
{

    protected static $session = false;

    public function __construct()
    {
        $namespace = "default";
        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        if ($sistema) {
            $namespace = $sistema->sigla;
        }
        parent::__construct($namespace);
        /*
		echo $this->controller_atual . " - " . $request->getControllerName(); 
		if ($this->controller_atual != $request->getControllerName()) {
			$this->limparFiltro();
		}
		$this->controller_atual = $request->getControllerName();
		*/
    }

    public static function getInstance()
    {
        if (self::$session !== false)
            return self::$session;
        $class = __CLASS__;
        self::$session = new $class();
        return self::$session;
    }

    public function atualizaFiltros($filtros, $action = "")
    {
        $dados = array();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();

        if (!$action) {
            $action = $request->getActionName();
        }

        foreach ($filtros as $filtro) {
            $session_filtro = $controller . "_" . $action . "_" . $filtro;

            if ($request->has($filtro)) {
                $dados[$filtro] = $request->getParam($filtro);
                $this->filtro[$session_filtro] = $request->getParam($filtro);
                continue;
            }

            if (isset($this->filtro[$session_filtro])) {
                $dados[$filtro] = $this->filtro[$session_filtro];
                continue;
            }

            $dados[$filtro] = "";
        }

        return $dados;
    }

    public function limparFiltro($chave = "")
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if ($chave) {
            $session_filtro = $controller . "_" . $action . "_" . $chave;
            unset($this->filtro[$session_filtro]);
        } elseif (isset($this->filtro)) {
            unset($this->filtro);
        }
    }

    public function set_lotacao_principal($lotacao)
    {
        $this->limparFiltro();
        $this->id_lotacao_atual = $lotacao->getId();
    }

    public function limparFiltros($filtros = array())
    {
        foreach ($filtros as $filtro) {
            $this->limparFiltro($filtro);
        }
    }
}
