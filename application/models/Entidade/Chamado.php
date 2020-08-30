<?php
class Chamado extends Escola_Entidade {
	
    public function init() {
		if (!$this->getId()) {
			$this->data_criacao = date("Y-m-d");
			$this->hora_criacao = date("H:i:s");
			$tb = new TbChamadoStatus();
			$ds = $tb->getPorChave("P");
			if ($ds) {
				$this->id_chamado_status = $ds->getId();
			}
			$usuario = TbUsuario::pegaLogado();
			if ($usuario) {
				$tb = new TbFuncionario();
				$funcionario = $tb->getPorUsuario($usuario);
				if ($funcionario) {
					$this->id_funcionario = $funcionario->getId();
					$lotacao = $funcionario->pegaLotacaoAtual();
					if ($lotacao) {
						$this->id_setor = $lotacao->id_setor;
					}
				}
			}
		}
	}
	
    public function setFromArray(array $dados) {
		if (isset($dados["data_criacao"])) {
			$dados["data_criacao"] = Escola_Util::montaData($dados["data_criacao"]);
		}
        parent::setFromArray($dados);
    }
	    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_chamado_tipo) {
			$msgs[] = "CAMPO TIPO DE CHAMADO OBRIGATÓRIO!";
		}
		if (!$this->descricao_problema) {
			$msgs[] = "CAMPO DESCRIÇÃO DO PROBLEMA OBRIGATÓRIO!";
		}
		if (!$this->id_chamado_status) {
			$msgs[] = "CAMPO STATUS DO CHAMADO OBRIGATÓRIO!";
		}
		if (!$this->id_setor) {
			$msgs[] = "CAMPO SETOR DO CHAMADO OBRIGATÓRIO!";
		}
		if (!$this->id_funcionario) {
			$msgs[] = "CAMPO FUNCIONÁRIO CHAMADO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}

	public function habilitaAlterar($funcionario) {
		return ($this->findParentRow("TbChamadoStatus")->pendente() && ($this->id_funcionario == $funcionario->getId()));
	}
	
	public function habilitaAtendimento($funcionario) {
		$ds = $this->findParentRow("TbChamadoStatus");
		if ($ds->pendente() || $ds->em_atendimento()) {
			$dt = $this->findParentRow("TbChamadoTipo");
			if ($dt) {
				$lotacao = $funcionario->pegaLotacaoAtual();
				if ($lotacao) {
					$setor = $lotacao->findParentRow("TbSetor");
					if ($setor && $dt->pegaSetor($setor)) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	public function toString() {
		$string = "";
		$ct = $this->findParentRow("TbChamadoTipo");
		if ($ct) {
			$string = $ct->toString() . " - ";
		}
		$string .= Escola_Util::formatData($this->data_criacao) . " - ";
		$string .= $this->descricao_problema;
		return $string;
	}
	
	public function habilitaConfirmacao($funcionario) {
		$ds = $this->findParentRow("TbChamadoStatus");
		if ($ds->atendido()) {
			$lotacao = $funcionario->pegaLotacaoAtual();
			if ($lotacao && ($lotacao->id_setor == $this->id_setor)) {
				return true;
			}
		}
		return false;
	}
	
	public function pegaOcorrencia() {
		$tb = new TbChamadoOcorrencia();
		$sql = $tb->select();
		$sql->where("id_chamado = " . $this->getId());
		$sql->order("data_ocorrencia desc");
		$sql->order("hora_ocorrencia desc");
		$objs = $tb->fetchAll($sql);
		if ($objs && count($objs)) {
			return $objs;
		}
		return false;
	}
	
	public function finalizado() {
		$ds = $this->findParentRow("TbChamadoStatus");
		if ($ds) {
			return $ds->finalizado();
		}
		return false;
	}
	
	public function mostrarStatus() {
		$cs = $this->findParentRow("TbChamadoStatus");
		if ($cs) {
			$classe = "";
			if ($cs->pendente()) {
				$classe = "pendente";
			} elseif ($cs->em_atendimento()) {
				$classe = "em_atendimento";
			} elseif ($cs->atendido()) {
				$classe = "atendido";
			}
			return "<div class='status_chamado {$classe}'>" . $cs->toString() . "</div>";	
		}
		return "";
	}
}