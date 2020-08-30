<?php
class TbMenu extends Escola_Tabela {
	protected $_name = "menu";
	protected $_rowClass = "Menu";
	protected $_referenceMap = array("MenuPosicao" => array("columns" => array("id_menu_posicao"),
															"refTableClass" => "TbMenuPosicao",
															"refColumns" => array("id_menu_posicao")),
									 "menuTipo" => array("columns" => array("id_menu_tipo"),
															"refTableClass" => "TbMenuTipo",
															"refColumns" => array("id_menu_tipo")));
	
	public function listar($dados = array()) {
		$select = $this->select();
		if (isset($dados["id_menu_posicao"]) && $dados["id_menu_posicao"]) {
			$select->where("id_menu_posicao = {$dados["id_menu_posicao"]}");
		}
		if (isset($dados["id_menu_superior"]) && $dados["id_menu_superior"]) {
			if ($dados["id_menu_superior"] == "null") {
				$select->where("id_menu_superior is null or id_menu_superior = 0 ");
			} else {
				$select->where("id_menu_superior = {$dados["id_menu_superior"]}");
			}
		}
		$select->order("ordem");
		$rg = $this->fetchAll($select);
		if ($rg && count($rg)) {
			return $rg;
		}
		return false;
	}	

	public function listarPorPagina($dados = array()) {
		$select = $this->select();
		$select->order("ordem");
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage(50);
		return $paginator;
	}	
	
	public static function pegaUltimaOrdem() {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("menu"), array("maximo" => "max(ordem)"));
		$stmt = $db->query($sql);
		if ($stmt && $stmt->rowCount()) {
			return $stmt->fetch(Zend_Db::FETCH_OBJ)->maximo;
		}
		return 0;
	}
	
	public function pegaPorOrdem($ordem) {
		if ($ordem) {
			$rg = $this->fetchAll("ordem = {$ordem}");
			if ($rg && count($rg)) {
				return $rg->current();
			}
		}
		return false;
	}
	
	public function renderMenuSuperior() {
		$tb = new TbMenuPosicao();
		$ms = $tb->getPorChave("S");
		if ($ms) {
		    $tb = new TbMenu();
		    $menus = $tb->listar(array("id_menu_posicao" => $ms->getId(), "id_menu_superior" => "null"));
		    if ($menus) {
		    	ob_start();
?>
		<ul>
<?php 
foreach ($menus as $menu) {
	 echo $menu->renderMenuSuperior();
} 
?>
		</ul>
<?php
			$html = ob_get_contents();
			ob_end_clean();
			return $html; 
		}} 
		return "";
	}
}