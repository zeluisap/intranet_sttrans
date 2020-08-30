<?php
class Mensagem extends Escola_Entidade {

	public function init() {
		$atual = new Zend_Date();
		$this->data = $atual->get("YYYY-MM-dd");
		$this->hora = $atual->get("HH:mm:ss");
	}
	
	public function getErrors() {
		$errors = array();
		if (!$this->id_mensagem_tipo) {
			$errors[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (!$this->assunto) {
			$errors[] = "CAMPO ASSUNTO OBRIGATÓRIO!";
		}
		if (!$this->mensagem) {
			$errors[] = "CAMPO MENSAGEM OBRIGATÓRIO!";
		}
		$mt = $this->findParentRow("TbMensagemTipo");
		if ($mt && !$mt->todos() && !$this->chave_destino) {
			$errors[] = "CAMPO DESTINO DA MENSAGEM OBRIGATÓRIO!";
		}
		if (count($errors)) {
			return $errors;
		}
		return false;
	}
	
	public function lido($usuario) {
		if ($this->getId()) {
			$db = Zend_Registry::get("db");
			$sql = $db->select();
			$sql->from(array("um" => "usuario_mensagem"));
			$sql->where("um.id_usuario = " . $usuario->getId());
			$sql->where("um.id_mensagem = " . $this->getId());
			$stmt = $db->fetchAll($sql);
			if ($stmt && count($stmt)) {
				return true;
			}
		}
		return false;
	}
	
	public function pegaDestinatario() {
		$mt = $this->findParentRow("TbMensagemTipo");
		$destinatario = false;
		if ($mt->setor_subordinado() || $mt->setor_atual() || $mt->setor()) {
			$destinatario = TbSetor::pegaPorId($this->chave_destino);
		} elseif ($mt->pessoal()) {
			$destinatario = TbFuncionario::pegaPorId($this->chave_destino);
		}
		return $destinatario;
	}
	
	public function mostrarDestinatario() {
		$retorno = "--";
		$destinatario = $this->pegaDestinatario();
		if ($destinatario) {
			$retorno = $destinatario->toString();
		}
		return $retorno;
	}
	
	public function ler($usuario) {
		if (!$this->lido($usuario)) {
			$db = Zend_Registry::get("db");
			$sql = "insert into usuario_mensagem values (" . $usuario->getId() . ", " . $this->getId() . ")";
			$db->query($sql);
		}
	}
	
	public function delete() {
		$sql = "delete from usuario_mensagem where id_mensagem = " . $this->getId();
		$db = Zend_Registry::get("db");
		$db->query($sql);
		return parent::delete();
	}
}