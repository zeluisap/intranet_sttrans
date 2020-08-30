<?php
class Escola_Alerta {
	private $items = array();
	private static $obj = false;
	
	public function __construct() {
		$this->items = array();
	}
	
	public function pega_items() {
		return $this->items;
	}
	
	public static function getInstance() {
		if (self::$obj!== false)
			return self::$obj;
		$class = __CLASS__;
		self::$obj = new $class();
		return self::$obj;
	}
	
	public function add(Escola_IAlerta $item) {
		$this->items[] = $item;
	}
	
	public function render() {
		ob_start();
		if (count($this->items)) {
			foreach ($this->items as $item) {
				$alertas = $item->pega_alertas();
				if ($alertas) {
					foreach ($alertas as $alerta) {
						echo $alerta->render();
					}
				}
			}
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}