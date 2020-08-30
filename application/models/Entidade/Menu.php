<?php
class Menu extends Escola_Entidade {
	protected $_arquivo;
	
	public function init() {
		$this->_arquivo = $this->pegaArquivo();
		if (!$this->getId()) {
			$this->id_menu_superior = 0;
		}
	}
	
	public function setFromArray(array $dados) {
		if (isset($dados["arquivo"]) && isset($dados["arquivo"]["size"]) && $dados["arquivo"]["size"]) {
			$dados["legenda"] = "ÍCONE MENU";
			$this->_arquivo->setFromArray($dados);
		}
		parent::setFromArray($dados);
		$mt = $this->findParentRow("TbMenuTipo");
		if ($mt && $mt->info() && isset($dados["id_info"]) && $dados["id_info"]) {
			$this->url = $dados["id_info"];
		}
	}
	
	public function save() {
		if (!$this->ordem) {
			$this->ordem = 1;
			$db = Zend_Registry::get("db");
			$sql = $db->select();
			$sql->from(array("menu"), array("max(ordem) as maximo"));
			$rg = $db->fetchAll($sql);
			if ($rg && count($rg)) {
				if ($rg[0]["maximo"]) {
					$this->ordem = $rg[0]["maximo"] + 1;
				}
			}
		}
		$id = parent::save();
		if ($this->_arquivo->existe()) {
			$id_arquivo = $this->_arquivo->save();
			$tb = new TbArquivoRef();
			$ar = $tb->createRow();
			$ar->setFromArray(array("tipo" => "M",
									"chave" => $this->getId(),
									"id_arquivo" => $id_arquivo));
			if (!$ar->getErrors()) {
				$ar->save();
			}
		}
		return $id;
	}

	public function getErrors() {
		$msgs = array();
		if (!trim($this->id_menu_posicao)) {
			$msgs[] = "CAMPO POSIÇÃO OBRIGATÓRIO!";
		}
		if (!trim($this->id_menu_tipo)) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		if (!trim($this->url)) {
			$msgs[] = "CAMPO URL OBRIGATÓRIO!";
		}
		if ($this->findParentRow("TbMenuPosicao")->servicos() && !$this->_arquivo->eImagem()) {
			$msgs[] = "NO TIPO DE MENU SERVIÇOS O ÍCONE É UM ARQUIVO DE IMAGEM OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function toString() {
        return $this->descricao;
    }
    
    public function toStringCompleto() {
    	$string = $this->toString();
    	$superior = $this->pegaSuperior();
    	if ($superior) {
    		$string .= " << " . $superior->toString(); 
    	}
    	return $string;
    }

	public function subir() {
		$this->mover($this->ordem - 1);
	}
	
	public function descer() {
		$this->mover($this->ordem + 1);
	}
	
	protected function mover($ordem) {
		$ordem_antiga = $this->ordem;
		if ($ordem && ($ordem <= TbModulo::pegaUltimaOrdem())) {
			$modulo = $this->getTable()->pegaPorOrdem($ordem);
			if ($modulo) {
				$modulo->ordem = $ordem_antiga;
				$modulo->save();
			}
			$this->ordem = $ordem;
			$this->save();
		}
	}
	
	public function url() {
		if ($this->findParentRow("TbMenuTipo")->interno()) {
			return Escola_Util::getBaseUrl() . "/" . $this->url;
		} elseif ($this->findParentRow("TbMenuTipo")->info()) {
			return Escola_Util::getBaseUrl() . "/portal/view/id/" . $this->url;
		}
		return $this->url;
	}
	
	public function mostrarUrl() {
		if ($this->findParentRow("TbMenuTipo")->info() && $this->url) {
			$info = TbInfo::pegaPorId($this->url);
			if ($info) {
				return $info->toString();
			}
		}
		return $this->url;
	}
	
	public function pegaArquivo() {
		$tb = new TbArquivo();
		if ($this->getId()) {
			$db = Zend_Registry::get("db");
			$sql = $db->select();
			$sql->from(array("ar" => "arquivo_ref"), array("id_arquivo"));
			$sql->where(" tipo = 'M' ");
			$sql->where(" chave = " . $this->getId());
			$stmt = $db->query($sql);
			if ($stmt && $stmt->rowCount()) {
				return $tb->getPorId($stmt->fetch(Zend_Db::FETCH_OBJ)->id_arquivo);
			}
		}
		return $tb->createRow();
	}
	
	public function mostrarIcone() {
		$arquivo = $this->pegaArquivo();
		if ($arquivo && $arquivo->existe()) {
            $src = Escola_Util::url(array("controller" => "arquivo", "action" => "view", "id" => $arquivo->getId()));
			return '<img src="' . $src . '" alt="' . $this->descricao . '" />';
		}
		return "";
	}
	
	public function externo() {
		$mt = $this->findParentRow("TbMenuTipo");
		if ($mt) {
			return $mt->externo();
		}
		return false;
	}
	
	public function pegaSuperior() {
		if ($this->id_menu_superior) {
			$menu = TbMenu::pegaPorId($this->id_menu_superior);
			if ($menu) {
				return $menu;
			}
		}
		return false;
	}
	
	public function mostrarSuperior() {
		$superior = $this->pegaSuperior();
		if ($superior) {
			return $superior->toString();
		}
		return "";
	}
	
	public function pegaInferiores() {
		$tb = new TbMenu();
		$menus = $tb->listar(array("id_menu_superior" => $this->getId()));
		if ($menus) {
			return $menus;
		}
		return false;
	}
	
	public function renderMenuSuperior() {
		echo "<li>";
		echo '<a href="' . $this->url() . '" ';
		if ($this->externo()) {
			echo ' target="_blank" '; 
		}
		echo '>' . $this->descricao . '</a>';
		$inferiores = $this->pegaInferiores();
		if ($inferiores) {
			echo "<ul>";
			foreach ($inferiores as $inf) {
				$inf->renderMenuSuperior();
			}
			echo "</ul>";
		}
		echo "</li>";
	}
}