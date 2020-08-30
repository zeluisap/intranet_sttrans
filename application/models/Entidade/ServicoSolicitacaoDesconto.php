<?php
class ServicoSolicitacaoDesconto extends Escola_Entidade {

    public function pegaServicoSolicitacao() {
        $obj = $this->findParentRow("TbServicoSolicitacao");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }
        
    public function setFromArray(array $dados) {
        if (isset($dados["valor"])) {
            $dados["valor"] = Escola_Util::montaNumero($dados["valor"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_servico_solicitacao)) {
            $msgs[] = "CAMPO SOLICITAÇÃO DE SERVIÇO OBRIGATÓRIO!";
        }
        if (!trim($this->valor)) {
            $msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
        }
        if (!trim($this->motivo)) {
            $msgs[] = "CAMPO MOTIVO DO DESCONTO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
     
    public function save() {
        if ($this->id_servico_solicitacao) {
            $tb = new TbServicoSolicitacaoDesconto();
            $sql = $tb->select();
            $sql->where("id_servico_solicitacao = {$this->id_servico_solicitacao}");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                foreach ($rs as $obj) {
                    $obj->delete();
                }
            }
        }
        return parent::save();
    }
}