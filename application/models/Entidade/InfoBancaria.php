<?php
class InfoBancaria extends Escola_Entidade {

    public function mostrar_agencia() {
        $agencia = $this->agencia;
        if ($this->agencia_dv) {
            $agencia .= "-" . $this->agencia_dv;
        }
        return $agencia; 
    }
    
    public function mostrar_conta() {
        $conta = $this->conta;
        if ($this->conta_dv) {
            $conta .= "-" . $this->conta_dv;
        }
        return $conta;
    }
    
    public function toString() {
        $txt = array();
        $ibt = $this->findParentRow("TbInfoBancariaTipo");
        if ($ibt) {
            $txt[] = $ibt->toString();
        }
        $banco = $this->findParentRow("TbBanco");
        if ($banco) {
            $txt[] = $banco->sigla;
        }
        $agencia = $this->mostrar_agencia();
        if ($agencia) {
            $txt[] = "Agência: " . $agencia;
        }
        $conta = $this->mostrar_conta();
        if ($conta) {
            $txt[] = "Conta: " . $conta;
        }
        return implode(" - ", $txt);
    }
    
	public function setFromArray(array $dados) {
        $filter = new Zend_Filter_StringToUpper();
		if (isset($dados["agencia"])) {
			$dados["agencia"] = $filter->filter(utf8_decode($dados["agencia"]));
		}
		if (isset($dados["agencia_dv"])) {
			$dados["agencia_dv"] = $filter->filter(utf8_decode($dados["agencia_dv"]));
		}
		if (isset($dados["conta"])) {
			$dados["conta"] = $filter->filter(utf8_decode($dados["conta"]));
		}
		if (isset($dados["conta_dv"])) {
			$dados["conta_dv"] = $filter->filter(utf8_decode($dados["conta_dv"]));
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->id_info_bancaria_tipo)) {
			$msgs[] = "CAMPO TIPO DE INFORMAÇÃO BANCÁRIA OBRIGATÓRIO!";
		}
		if (empty($this->id_banco)) {
			$msgs[] = "CAMPO BANCO OBRIGATÓRIO!";
		}
		if (empty($this->agencia)) {
			$msgs[] = "CAMPO AGÊNCIA OBRIGATÓRIO!";
		}
		if (empty($this->conta)) {
			$msgs[] = "CAMPO CONTA OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function delete() {
        //apagando as referências
        $tb = new TbInfoBancariaRef();
        $ibrs = $tb->fetchAll("id_info_bancaria = {$this->getId()}");
        if ($ibrs && count($ibrs)) {
            foreach ($ibrs as $ibr) {
                $ibr->delete();
            }
        }
        parent::delete();
    }
    
    public function render(Zend_View_Interface $view = null) {
        ob_start();
?>
<fieldset>
    <legend>Informações Bancárias</legend>
<?php
    $ctrl = new Escola_Form_Element_Select_Table("id_info_bancaria_tipo");
    $ctrl->setPkName("id_info_bancaria_tipo");
    $ctrl->setModel("TbInfoBancariaTipo");
    $ctrl->setValue($this->id_info_bancaria_tipo);
    $ctrl->setLabel("Tipo: ");
    echo $ctrl->render($view);
    $ctrl = new Escola_Form_Element_Select_Table("id_banco");
    $ctrl->setPkName("id_banco");
    $ctrl->setModel("TbBanco");
    $ctrl->setValue($this->id_banco);
    $ctrl->setLabel("Banco: ");
    echo $ctrl->render($view);
?>
    <div class="control-group">
        <label for="agencia" class="control-label">Agência:</label>
        <div class="controls">
            <input type="text" name="agencia" id="agencia" class="span2" value="<?php echo $this->agencia; ?>" /> - <input type="text" name="agencia_dv" id="agencia_dv" class="span1" value="<?php echo $this->agencia_dv; ?>" />
        </div>
    </div>
    <div class="control-group">
        <label for="conta" class="control-label">Conta:</label>
        <div class="controls">
            <input type="text" name="conta" id="conta" class="span2" value="<?php echo $this->conta; ?>" /> - <input type="text" name="conta_dv" id="conta_dv" class="span1" value="<?php echo $this->conta_dv; ?>" />
        </div>
    </div>
</fieldset>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function pegaUltimoMovimento() {
        $tb = new TbVinculoMovimento();
        $sql = $tb->select();
        $sql->from(array("vm" => "vinculo_movimento"));
        $sql->join(array("ibr" => "info_bancaria_ref"), "vm.id_vinculo_movimento = ibr.chave", array());
        $sql->where("ibr.tipo = 'VM'");
        $sql->where("ibr.id_info_bancaria = {$this->getId()}");
        $sql->order("vm.data_movimento desc");
        $sql->order("vm.id_vinculo_movimento desc");
        $sql->limit(1);
        $vms = $tb->fetchAll($sql);
        if ($vms && count($vms)) {
            $vm = $vms->current();
            return $tb->getPorId($vm->getId());
        }
        return false;
    }
    
    public function pegaSaldo() {
        $movimento = $this->pegaUltimoMovimento();
        if ($movimento) {
            return $movimento->pega_valor_posterior();
        }
        return 0;
    }
    
    public function atualizaSaldoAnterior() {
        $tb = new TbVinculoMovimento();
        $vms = $tb->listar(array("filtro_id_info_bancaria" => $this->getId()));
        if ($vms && count($vms)) {
            $saldo_anterior = 0;
            foreach ($vms as $vm) {
                $vm = $tb->getPorId($vm->getId());
                $va = $vm->pega_valor_anterior();
                if ($va) {
                    $va->valor = $saldo_anterior;
                    $va->save();
                    $vm = $tb->getPorId($vm->getId());
                }
                $saldo_anterior = $vm->pega_valor_posterior();
            }
        }
    }
}