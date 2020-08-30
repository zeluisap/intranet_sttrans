<?php
class Pacote extends Escola_Entidade {

    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->status = "A";
        }
    }
	
	public function setFromArray(array $dados) {
		if (isset($dados["sigla"])) {
			$filter = new Zend_Filter_StringToUpper();
			$dados["sigla"] = $filter->filter(utf8_decode($dados["sigla"]));
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$tb = new TbPacote();
		$rg = $tb->fetchAll(" (sigla  = '{$this->sigla}') and (id_pacote <> '" . $this->getId() . "') ");
		if ($rg->count()) {
			$msgs[] = "PACOTE JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function save($flag = false) {
        $tb = $this->getTable();
        if (!$this->ordem) {
            $this->ordem = $tb->pegaUltimaOrdem() + 1;
        } 
        if ($this->ordem) {
            $pacote = $tb->getPorOrdem($this->ordem);
            if ($pacote) {
                $pacote->ordem = $this->ordem + 1;
                $pacote->save();
            }
        }
        parent::save($flag);
    }
	
	public function getDeleteErrors() {
		$msgs = array();
		$modulos = $this->pegaModulos();
		if ($modulos) {
			$msgs[] = "Existem Módulos Cadastrados para Este Pacote, Exclua os Módulos antes de Excluir!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
	
	public function pegaModulos() {
		$tb = new TbModulo();
		$sql = $tb->select();
		$sql->from(array("m" => "modulo"));
		$sql->join(array("mp" => "modulo_pacote"), "m.id_modulo = mp.id_modulo", array());
		$sql->where("mp.id_pacote = {$this->getId()}");
		$sql->order("m.ordem");
		$objs = $tb->fetchAll($sql);
		if ($objs && count($objs)) {
			return $objs;
		}
		return false;
	}
	
	public function toString() {
		return $this->descricao;		
	}
	
	public function possuiModulo($modulo) {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("mp" => "modulo_pacote"));
		$sql->where("id_modulo = ?", $modulo->getId());
		$sql->where("id_pacote = ?", $this->getId());
		$stmt = $db->query($sql);
		if ($stmt && $stmt->rowCount()) {
			return true;
		}
		return false;
	}
	
	public function addModulo($modulo) {
		$superior = $modulo->pegaSuperior();
		if ($superior) {
			$this->addModulo($superior);
		}
		if (!$this->possuiModulo($modulo)) {
			$db = Zend_Registry::get("db");
			$sql = "insert into modulo_pacote (id_modulo, id_pacote) values ({$modulo->getId()}, {$this->getId()})";
			$db->query($sql);
		}
		return $this->possuiModulo($modulo);
	}
	
	public function limparModulos() {
		$db = Zend_Registry::get("db");
		$sql = "delete from modulo_pacote where id_pacote = {$this->getId()}";
		$stmt = $db->query($sql);
	}
	
	public function mostrarIcone() {
		if ($this->icone) {
			return "<i class='{$this->icone} icon-large'></i>";
		}
		return "";
	}
        
        public function ativo() {
            return ($this->status == "A");
        }
        
        public function inativo() {
            return !$this->ativo();
        }
        
    public function mostrar_status() {
        if ($this->ativo()) {
            return "ATIVO";
        }
        return "INATIVO";
    }
}