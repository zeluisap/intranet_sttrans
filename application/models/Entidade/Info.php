<?php
class Info extends Escola_Entidade {
	protected $_arquivo = false;
	
    public function init() {
		if (!$this->getId()) {
			$this->data = date("Y-m-d");
			$tb = new TbInfoStatus();
			$is = $tb->getPorChave("O");
			if ($is) {
				$this->id_info_status = $is->getId();
			}
			$this->destaque = "N";
		}
		$this->_arquivo = $this->load_arquivo();
	}
	
	public function load_arquivo() {
		if ($this->getId()) {
			$obj = $this->findParentRow("TbArquivo");
			if ($obj) {
				return $obj;
			}
		}
		$tb = new TbArquivo();
		return $tb->createRow();
	}
	
	public function pega_arquivo() {
		return $this->_arquivo;
	}
	
	public function setFromArray(array $dados) {
		if (isset($dados["data"])) {
			$dados["data"] = Escola_Util::montaData($dados["data"]);
		}
		if (isset($dados["arquivo_destaque"]) && $dados["arquivo_destaque"]["size"]) {
			$this->_arquivo->setFromArray(array("legenda" => "Imagem Destaque", "arquivo" => $dados["arquivo_destaque"]));
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (!trim($this->titulo)) {
			$msgs[] = "CAMPO Tï¿½TULO OBRIGATï¿½RIO!";
		}
		if (!$this->id_info_tipo) {
			$msgs[] = "CAMPO TIPO OBRIGATï¿½RIO!";
		}
		if (!$this->id_info_status) {
			$msgs[] = "CAMPO STATUS OBRIGATï¿½RIO!";
		}
		$it = $this->findParentRow("TbInfoTipo");
		if ($it && $it->imagem()) {
			if (!$this->_arquivo || !$this->_arquivo->existe()) {
				$msgs[] = "CAMPO IMAGEM DESTAQUE OBRIGATï¿½RIO!";
			}
		}
		if ($this->_arquivo && $this->_arquivo->existe() && !($this->_arquivo->eJpeg() || $this->_arquivo->ePng())) {
			$msgs[] = "CAMPO IMAGEM DESTAQUE PRECISA SER UM ARQUIVO DO TIPO JPEG!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
	
	public function save() {
		$id_arquivo = 0;
		if ($this->_arquivo && $this->_arquivo->tamanho) {
			$id_arquivo = $this->_arquivo->save();
			$this->id_arquivo = $id_arquivo;
		}
		$id = parent::save();
	}
    
    public function toString() {
        return $this->findParentRow("TbInfoTipo")->toString() . " - " . $this->titulo;
    }
	
	public function delete() {
		if ($this->getId()) {
			$db = Zend_Registry::get("db");
			$db->query("delete from info_ref where id_info = " . $this->getId());
		}
		return parent::delete();
	}
	
	public function pegaAnexos() {
		if ($this->getId()) {
			$tb = new TbInfoRef();
			$sql = $tb->select();
			$sql->where("tipo = 'A'");
			$sql->where("id_info = ". $this->getId());
			$sql->order("id_info_ref");
			$rg = $tb->fetchAll($sql);
			if ($rg && count($rg)) {
				return $rg;
			}
		}
		return false;
	}
	
	public function pegaReferencia() {
		if ($this->getId()) {
			$tb = new TbInfoRef();
			$sql = $tb->select();
			$sql->where("tipo <> 'A'");
			$sql->where("id_info = ". $this->getId());
			$sql->order("id_info_ref");
			$rg = $tb->fetchAll($sql);
			if ($rg && count($rg)) {
				return $rg;
			}
		}
		return false;
	}
	
	public function destaque() {
		return ($this->destaque == "S");
	}
	
	public function mostrarDestaque() {
		if ($this->destaque()) {
			return "SIM";
		}
		return "Nï¿½O";
	}
	
	public function galeria() {
		return $this->findParentRow("TbInfoTipo")->galeria();
	}
	
	public function comentario() {
		return ($this->comentario == "S");
	}
	
	public function mostrarComentario() {
		if ($this->comentario()) {
			return "SIM";
		}
		return "Nï¿½O";
	}
	
	public function pegaTbComentario() {
		if ($this->getId()) {
			$tb = new TbComentarioStatus();
			$cs = $tb->getPorChave("P");
			if ($cs) {
				$tb = new TbComentario();
				$sql = $tb->select();
				$sql->where("id_info = " . $this->getId());
				$sql->where("id_comentario_status = " . $cs->getId());
				$sql->order("data desc");
				$sql->order("hora desc");
				$rg = $tb->fetchAll($sql);
				if ($rg && count($rg)) {
					return $rg;
				}
			}
		}
		return false;
	}
}