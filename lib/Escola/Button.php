<?php
class Escola_Button
{
	protected $_itens = array();
	protected $_titulo = "";
	protected $_icon = "";
	private static $_button = null;

	/**
	 * @return Escola_Button
	 */
	public static function getInstance()
	{
		if (self::$_button === null) {
			self::$_button = new self();
		}
		return self::$_button;
	}

	public function __construct()
	{
		$fc = Zend_Controller_Front::getInstance();
		$request = $fc->getRequest();
		$controller = $request->getParam("controller");
		if ($controller) {
			$tb = new TbModulo();
			$modulo = $tb->getPorController($controller);
			if ($modulo && $modulo->icon) {
				$this->setIcon($modulo->icon);
			}
		}
	}

	public function setTitulo($titulo)
	{
		$this->_titulo = $titulo;
	}

	public function getTitulo()
	{
		return $this->_titulo;
	}

	public function getItens()
	{
		return $this->_itens;
	}

	public function setIcon($icon)
	{
		$this->_icon = $icon;
	}

	public function getIcon()
	{
		return $this->_icon;
	}

	public function addVoltar($instance)
	{
		$anterior = $instance->getActionAnterior();
		if (!$anterior) {
			$instance->view->url([
				"controller" => $instance->getRequest()->getControllerName(),
				"action" => "index",
			], null, true);
		} else {
			$url = $instance->view->url($anterior, null, true);
		}

		$this->addItem("action", "Voltar", $url, "icon-reply");
	}

	public function addAction($titulo = "", $controller = "", $action = "", $img = "", $params = array(), $id = "", $class = "")
	{
		$view = new Zend_View();
		$array_url = array("controller" => $controller, "action" => $action);
		$array_url = array_merge($array_url, $params);
		$url = $view->url($array_url, null, true);
		$this->addItem("action", $titulo, $url, $img, $params, $id, $class);
	}

	public function addScript($titulo = "", $onclick = "", $img = "", $params = array(), $id = "", $class = "")
	{
		$this->addItem("script", $titulo, $onclick, $img, $params, $id, $class);
	}

	public function addFromArray($array)
	{
		$controller = $titulo = $action = $onclick = $img = $id = $class = "";
		$params = array();
		if (isset($array["titulo"]) && $array["titulo"]) {
			$titulo = $array["titulo"];
		}
		if (isset($array["controller"]) && $array["controller"]) {
			$controller = $array["controller"];
		}
		if (isset($array["action"]) && $array["action"]) {
			$action = $array["action"];
		}
		if (isset($array["onclick"]) && $array["onclick"]) {
			$onclick = $array["onclick"];
		}
		if (isset($array["img"]) && $array["img"]) {
			$img = $array["img"];
		}
		if (isset($array["id"]) && $array["id"]) {
			$id = $array["id"];
		}
		if (isset($array["params"]) && is_array($array["params"]) && count($array["params"])) {
			$params = $array["params"];
		}
		if (isset($array["class"]) && $array["class"]) {
			$class = $array["class"];
		}
		if ($onclick) {
			$this->addScript($titulo, $onclick, $img, $params, $id, $class);
		} elseif ($controller) {
			$this->addAction($titulo, $controller, $action, $img, $params, $id, $class);
		}
	}

	public function addItem($tipo = "action", $titulo = "", $url = "", $img = "", $params = array(), $id = "", $class = "")
	{
		$item = new stdClass();
		$item->tipo = $tipo;
		$item->titulo = $titulo;
		$item->url = $url;
		$item->img = $img;
		$item->params = array();
		$item->id = $id;
		$item->class = $class;
		$item->target = "";
		if (isset($params["target"])) {
			$item->target = $params["target"];
			unset($params["target"]);
		}
		if (count($params)) {
			$item->params = $params;
		}
		$this->_itens[] = $item;
	}

	public function render()
	{
		if (count($this->_itens)) {
			ob_start();
			echo "<div class='stats'>";
			foreach ($this->_itens as $item) {
				$url = $item->url;
				if ($item->tipo == "script") {
					$url = "javascript: " . $url;
				}
				$front = Zend_Controller_Front::getInstance();
				$id = "";
				if ($item->id) {
					$id = " id='{$item->id}' ";
				}
?>
				<a href="<?php echo $url; ?>" <?php if ($item->class) { ?>class="<?php echo $item->class; ?>" <?php } ?> id="link_<?php echo $item->id; ?>" <?php if ($item->target) { ?>target="<?php echo $item->target; ?>" <?php } ?>>

					<div style="padding-left: 5px; padding-right: 5px; margin-top: 5px" class="stat btn pull-right btn-primary" <?php echo $id; ?> data-loading-text="Processando, aguarde...">
						<i class="<?php echo $item->img; ?>"></i> <?php echo $item->titulo; ?>

					</div>
				</a>
<?php
			}
			echo "</div>";
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}
}
