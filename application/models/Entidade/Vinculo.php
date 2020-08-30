<?php
class Vinculo extends Escola_Entidade {
    
    protected $_valor = false;
    protected $_id_pf_coordenador = false;
    
    public function get_id_pf_coordenador() {
        return $this->_id_pf_coordenador;
    }
    
    public function set_id_pf_coordenador($id_pf) {
        $this->_id_pf_coordenador = $id_pf;
    }

    public function pega_valor() {
        if ($this->_valor) {
            return $this->_valor;
        }
        $valor = $this->findParentRow("TbValor");
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }
    
    public function init() {
        parent::init();
        $this->_valor = $this->pega_valor();        
        if ($this->getId()) {
            $pf = $this->pega_coordenador();
            if ($pf) {
                $this->_id_pf_coordenador = $pf->getId();
            }
        } else {
            $tb = new TbVinculoStatus();
            $vs = $tb->getPorChave("A");
            if ($vs) {
                $this->id_vinculo_status = $vs->getId();
            }
        }
    }
    
    public function pega_info_bancaria() {
        if ($this->getId()) {
            $tb = new TbInfoBancaria();
            $sql = $tb->select();
            $sql->from(array("ib" => "info_bancaria"));
            $sql->join(array("ibr" => "info_bancaria_ref"), "ib.id_info_bancaria = ibr.id_info_bancaria", array());
            $sql->where("ibr.tipo = 'V'");
            $sql->where("ibr.chave = {$this->getId()}");
            $sql->order("ib.id_info_bancaria");
            $rows = $tb->fetchAll($sql);
            if ($rows && count($rows)) {
                return $rows;
            }
        }
        return false;
    }
    
    public function mostrar_numero() {
        return $this->codigo . '/' . $this->ano;
    }
    
    public function toString() {
        $items = array();
        $vt = $this->findParentRow("TbVinculoTipo");
        if ($vt) {
            $items[] = $vt->toString();
        }
        $items[] = $this->mostrar_numero();
        $items[] = $this->descricao;
        return implode(" - ", $items);
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["sigla"])) {
            $dados["sigla"] = Escola_Util::maiuscula($dados["sigla"]);
        }
        if (isset($dados["data_inicial"])) {
            $dados["data_inicial"] = Escola_Util::montaData($dados["data_inicial"]);
        }
        if (isset($dados["data_final"])) {
            $dados["data_final"] = Escola_Util::montaData($dados["data_final"]);
        }
        $this->_valor->setFromArray($dados);
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_vinculo_tipo)) {
            $msgs[] = "CAMPO TIPO DO VÍNCULO OBRIGATÓRIO!";
        }
        if (!trim($this->codigo)) {
            $msgs[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
        }
        if (!trim($this->ano)) {
            $msgs[] = "CAMPO ANO OBRIGATÓRIO!";
        }
        if (!trim($this->sigla)) {
            $msgs[] = "CAMPO SIGLA OBRIGATÓRIO!";
        }
        if (!trim($this->descricao)) {
            $msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
        }
        if (!trim($this->id_vinculo_status)) {
            $msgs[] = "CAMPO STATUS DO VÍNCULO OBRIGATÓRIO!";
        }
        if (!$this->_valor->valor) {
            $msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" codigo = '{$this->codigo}' and id_vinculo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "VÍNCULO JÁ CADASTRADO!";
        }
        $previsao = $this->pega_valor_previsao();
        if ($previsao > $this->pega_valor()->valor) {
            $msgs[] = "Valor do Convênio previsa ser Superior ao Valor Já Aprovisionado!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function save() {
        $this->id_valor = $this->_valor->save();
        return parent::save();
    }
    
    public function delete() {
        $tb = new TbBolsaTipo();
        $bts = $tb->listar(array("id_vinculo" => $this->getId()));
        if ($bts) {
            foreach ($bts as $bt) {
                $bt->delete();
            }
        }
        $tb = new TbInfoBancariaRef();
        $ibrs = $tb->fetchAll("tipo = 'V' and chave = {$this->getId()}");
        if ($ibrs && count($ibrs)) {
            foreach ($ibrs as $ibr) {
                $ibr->findParentRow("TbInfoBancaria")->delete();
            }
        }
        $vps = $this->findDependentRowset("TbVinculoPessoa");
        if ($vps) {
            foreach ($vps as $vp) {
                $vp->delete();
            }
        }
        parent::delete();
    }
    
    public function add_info_bancaria($ib) {
        $tb = new TbInfoBancariaRef();
        $sql = $tb->select();
        $sql->where("tipo = 'V'");
        $sql->where("chave = {$this->getId()}");
        $sql->where("id_info_bancaria = {$ib->getId()}");
        $rows = $tb->fetchAll($sql);
        if (!$rows || !count($rows)) {
            $tbr = $tb->createRow();
            $tbr->setFromArray(array("tipo" => "V", "chave" => $this->getId(), "id_info_bancaria" => $ib->getId()));
            if (!$tbr->getErrors()) {
                $tbr->save();
            }
        }        
    }
    
    public function mostrar_pj() {
        $pj = $this->findParentRow("TbPessoaJuridica");
        if ($pj) {
            return $pj->toString();
        }
        return "--";
    }
    
    public function pega_coordenador() {
        if ($this->getId()) {
            $tb = new TbVinculoPessoa();
            $vps = $tb->listar(array("vinculo_pessoa_tipo" => "CO", "id_vinculo" => $this->getId()));
            if ($vps) {
                return $vps->current();
            }
        }
        return false;
    }
    
    public function pega_pf_coordenador() {
        $vp = $this->pega_coordenador();
        if ($vp) {
            $pf = $vp->findParentRow("TbPessoaFisica");
            if ($pf) {
                return $pf;
            }
        }
        return false;
    }
    
    public function mostrar_coordenador() {
        $pf = $this->pega_pf_coordenador();
        if ($pf) {
            return $pf->toString();
        }
        return "--";
    }
    
    public function pega_responsavel_financeiro() {
        if ($this->getId()) {
            $tb = new TbVinculoPessoa();
            $vps = $tb->listar(array("vinculo_pessoa_tipo" => "RF", "id_vinculo" => $this->getId()));
            if ($vps) {
                return $vps->current();
            }
        }
        return false;
    }
    
    public function mostrar_responsavel_financeiro() {
        $vp = $this->pega_responsavel_financeiro();
        if ($vp) {
            $pf = $vp->findParentRow("TbPessoaFisica");
            if ($pf) {
                return $pf->toString();
            }
        }
        return "--";
    }
    
    public function pega_responsavel_contabil() {
        if ($this->getId()) {
            $tb = new TbVinculoPessoa();
            $vps = $tb->listar(array("vinculo_pessoa_tipo" => "RC", "id_vinculo" => $this->getId()));
            if ($vps) {
                return $vps->current();
            }
        }
        return false;
    }
    
    public function mostrar_responsavel_contabil() {
        $vp = $this->pega_responsavel_contabil();
        if ($vp) {
            $pf = $vp->findParentRow("TbPessoaFisica");
            if ($pf) {
                return $pf->toString();
            }
        }
        return "--";
    }
    
    public function pega_responsavel_tecnico() {
        $tb = new TbVinculoPessoa();
        $vps = $tb->listar(array("vinculo_pessoa_tipo" => "RT", "id_vinculo" => $this->getId()));
        if ($vps) {
            return $vps->current();
        }
        return false;
    }
    
    public function mostrar_responsavel_tecnico() {
        $vp = $this->pega_responsavel_tecnico();
        if ($vp) {
            $pf = $vp->findParentRow("TbPessoaFisica");
            if ($pf) {
                return $pf->toString();
            }
        }
        return "--";
    }
    
    public function mostrar_ib() {
        $ibs = $this->pega_info_bancaria();
        if ($ibs) {
            $items = array();
            foreach ($ibs as $ib) {
                $items[] = $ib->toString();
            }
            return implode(", ", $items);
        }
        return "";
    }
    
    public function getDeleteErrors() {
		$msgs = array();
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("b" => "bolsista"), array("b.id_bolsista"));
        $sql->where("id_vinculo = {$this->getId()}");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $msgs[] = "Existem Bolsistas vinculados a este " . $this->findParentRow("TbVinculoTipo")->toString() . ", apague os vínculos antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function pega_bolsa_tipo() {
        if ($this->getId()) {
            $tb = new TbPrevisaoTipo();
            $pt = $tb->getPorChave("BO");
            if ($pt) {
                $tb = new TbBolsaTipo();
                $objs = $tb->listar(array("id_vinculo" => $this->getId(), "id_previsao_tipo" => $pt->getId()));
                if ($objs && count($objs)) {
                    return $objs;
                }
            }
        }
        return false;
    }
    
    public function pega_valor_previsao($dados = array()) {
        if ($this->getId()) {
            $tb = new TbPrevisao();
            $dados["id_vinculo"] = $this->getId();
            return $tb->pega_valor_total($dados);
        }
        return 0;
    }
    
    public function pega_valor_utilizado($dados) {
        $tb = new TbVinculoLoteItemStatus();
        $vlis = $tb->getPorChave("PG");
        if ($vlis) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("vli" => "vinculo_lote_item"), array("total" => "sum(v.valor)"));
            $sql->join(array("vl" => "vinculo_lote"), "vli.id_vinculo_lote = vl.id_vinculo_lote", array());
            $sql->join(array("v" => "valor"), "vli.id_valor = v.id_valor", array());
            $sql->where("vl.id_vinculo = {$this->getId()}");
            $sql->where("vli.id_vinculo_lote_item_status = {$vlis->getId()}");
            if (isset($dados["mes"]) && $dados["mes"]) {
                $sql->where("vl.mes = {$dados["mes"]}");
            }
            if (isset($dados["ano"]) && $dados["ano"]) {
                $sql->where("vl.ano = {$dados["ano"]}");
            }
            $stmt = $db->query($sql);
            if ($stmt && $stmt->rowCount()) {
                $obj = $stmt->fetch(Zend_Db::FETCH_OBJ);
                return $obj->total;
            }
        }
        return 0;
    }
    
    public function pega_anos() {
        $db = Zend_Registry::get("db");
        $anos = array();
        $sql = $db->select();
        $sql->from(array("p" => "vinculo_lote"), array("ano"));
        $sql->where("id_vinculo = {$this->getId()}");
        $sql->group("ano");
        $objs = $db->fetchAll($sql);
        if ($objs && count($objs)) {
            foreach ($objs as $obj) {
                if (!in_array($obj["ano"], $anos)) {
                    $anos[] = $obj["ano"];
                }
            }
        }
        $sql = $db->select();
        $sql->from(array("p" => "previsao"), array("ano"));
        $sql->where("id_vinculo = {$this->getId()}");
        $sql->group("ano");
        $objs = $db->fetchAll($sql);
        if ($objs && count($objs)) {
            foreach ($objs as $obj) {
                if (!in_array($obj["ano"], $anos)) {
                    $anos[] = $obj["ano"];
                }
            }
        }
        asort($anos);
        return $anos;
    }
    
    public function set_vinculo_pessoa($id_vpt, $id_pf) {
        if ($id_vpt && $id_pf) {
            $tb = new TbVinculoPessoa();
            $vps = $tb->listar(array("id_vinculo_pessoa_tipo" => $id_vpt, "id_vinculo" => $this->getId()));
            if ($vps) {
                if (count($vps) > 1) {
                    foreach ($vps as $vp) {
                        $vp->delete();
                    }
                } elseif (count($vps) == 1) {
                    $vp = $vps->current();
                    if ($vp->id_pessoa_fisica != $id_pf) {
                        $vp->delete();
                    }
                }
            }
            $vp = $tb->createRow();
            $vp->id_vinculo_pessoa_tipo = $id_vpt;
            $vp->id_vinculo = $this->getId();
            $vp->id_pessoa_fisica = $id_pf;
            $errors = $vp->getErrors();
            if (!$errors) {
                $vp->save();
            }
        }
    }
    
    public function pega_data_final() {
        $obj_data = new stdClass();
        $obj_data->data_final = $this->data_final;
        $obj_data->aditivo = false;
        $date_vinculo = $this->data_final;
        if ($this->getId()) {
            $tb = new TbAditivo();
            $sql = $tb->select();
            $sql->from(array("a" => "aditivo"));
            $sql->join(array("at" => "aditivo_tipo"), "a.id_aditivo_tipo = at.id_aditivo_tipo", array());
            $sql->where("a.id_vinculo = {$this->getId()}");
            $sql->where("at.chave = 'D'");
            $sql->order("a.data_aditivo desc");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                $obj = $rs->current();
                $obj_data->data_final = $obj->data_aditivo;
                $obj_data->aditivo = $obj;
            }
        }
        return $obj_data;
    }
    
    public function mostrar_data_final() {
        $obj_data_final = $this->pega_data_final();
        $item = array();
        $item[] = Escola_Util::formatData($obj_data_final->data_final);
        if ($obj_data_final->aditivo) {
            $item[] = "ADITIVO: " . $obj_data_final->aditivo->toString();
        }
        return implode(" - ", $item);
    }
    
    public function pega_saldo() {
        $obj_saldo = new stdClass();
        $obj_saldo->saldo = $this->_valor->valor;
        $obj_saldo->aditivo = false;
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $tb = new TbAditivo();
            $sql = $db->select();
            $sql->from(array("a" => "aditivo"), array("saldo" => "sum(v.valor)"));
            $sql->join(array("at" => "aditivo_tipo"), "a.id_aditivo_tipo = at.id_aditivo_tipo", array());
            $sql->join(array("v" => "valor"), "a.id_valor = v.id_valor", array());
            $sql->where("a.id_vinculo = {$this->getId()}");
            $sql->where("at.chave = 'V'");
            $sql->order("a.data desc");
            $rs = $db->query($sql);
            if ($rs && count($rs)) {
                $obj = $rs->fetchObject();
                $obj_saldo->saldo = $obj_saldo->saldo + $obj->saldo;
            }
            $sql = $tb->select();
            $sql->from(array("a" => "aditivo"));
            $sql->join(array("at" => "aditivo_tipo"), "a.id_aditivo_tipo = at.id_aditivo_tipo", array());
            $sql->where("a.id_vinculo = {$this->getId()}");
            $sql->where("at.chave = 'V'");
            $sql->order("a.data desc");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                $obj = $rs->current();
                $obj_saldo->aditivo = $obj;
            }
        }
        return $obj_saldo;
    }
    
    public function mostrar_saldo() {
        $items = array();
        $tb = new TbMoeda();
        $moeda = $tb->pega_padrao();
        if ($moeda) {
            $obj_saldo = $this->pega_saldo();
            $saldo_final = $obj_saldo->saldo;
            $items[] = $moeda->simbolo . " " . Escola_Util::number_format($saldo_final);
            if ($obj_saldo->aditivo) {
                $items[] = "ADITIVO: " . $obj_saldo->aditivo->toString();
            }
        }
        return implode(" - ", $items);
    }
}