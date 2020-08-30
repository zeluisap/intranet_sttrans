<?php
class Servico extends Escola_Entidade
{

    public function setFromArray(array $dados)
    {
        if (isset($dados["codigo"])) {
            $dados["codigo"] = Escola_Util::maiuscula($dados["codigo"]);
        }
        parent::setFromArray($dados);
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_servico_tipo)) {
            $msgs[] = "CAMPO TIPO DE SERVIÇO OBRIGATÓRIO!";
        }
        if (!trim($this->codigo)) {
            $msgs[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
        }
        if (!trim($this->descricao)) {
            $msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
        }
        if ($this->transporte() && !(trim($this->id_servico_referencia))) {
            $msgs[] = "CAMPO TIPO DE REFERÊNCIA OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" codigo = '{$this->codigo}' and id_servico <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "SERVIÇO JÁ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function save($flag = false)
    {
        if ($this->transito()) {
            $this->id_servico_referencia = null;
        }
        parent::save($flag);
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        $registros = $this->findDependentRowset("TbServicoTransporteGrupo");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function toString()
    {
        return $this->descricao;
    }

    public function transito()
    {
        $status = $this->findParentRow("TbServicoTipo");
        if ($status) {
            return $status->transito();
        }
        return false;
    }

    public function transporte()
    {
        $status = $this->findParentRow("TbServicoTipo");
        if ($status) {
            return $status->transporte();
        }
        return false;
    }

    public function isCarteiraPermissionario()
    {
        return (strtolower($this->codigo) == 'cp');
    }
}
