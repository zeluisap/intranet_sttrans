<?php
class VinculoLote extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $tb = new TbVinculoLoteStatus();
            $vls = $tb->getPorChave("AL");
            if ($vls) {
                $this->id_vinculo_lote_status = $vls->getId();
            }
        }
    }
    
    public function save() {
        $id = $this->getId();
        $new_id = parent::save();
        if (!$id) {
            $this->atualiza_bolsista();
        }
        return $new_id;
    }
        
    public function getErrors() {
		$msgs = array();
		if (!trim($this->id_vinculo_lote_status)) {
			$msgs[] = "CAMPO STATUS OBRIGATÓRIO!";
		}
		if (!trim($this->mes)) {
			$msgs[] = "CAMPO MÊS OBRIGATÓRIO!";
		}
		if (!trim($this->ano)) {
			$msgs[] = "CAMPO ANO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" mes = {$this->mes} and ano = {$this->ano} and id_vinculo = {$this->id_vinculo} and id_vinculo_lote <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "LOTE JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        /*
        $tb = new TbVinculoLoteItem();
        $objs = $tb->listar(array("id_vinculo_lote" => $this->getId()));
        if ($objs && count($objs)) {
            $msgs[] = "Existem registros vinculados ao Registro, exclua os vínculos antes de continuar!";
        }
         */
        $status = $this->findParentRow("TbVinculoLoteStatus");
        if ($status && !$status->aguardando_liberacao() && !$status->aguardando_aprovacao()) {
            $msgs[] = "Lote com status [{$status->toString()}] não pode ser excluído!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
    }
    
    public function delete() {
        $db = Zend_Registry::get("db");
        $trans = true;
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            $tb = new TbVinculoLoteItem();
            $objs = $tb->listar(array("id_vinculo_lote" => $this->getId()));
            if ($objs && count($objs)) {
                foreach ($objs as $obj) {
                    $obj->delete();
                }
            }
            $tb = new TbVinculoLoteOcorrencia();
            $objs = $tb->listar(array("id_vinculo_lote" => $this->getId()));
            if ($objs && count($objs)) {
                foreach ($objs as $obj) {
                    $obj->delete();
                }
            }
            $return_id = parent::delete();
            
            if ($trans) {
                $db->commit();
            }
            
            return $return_id;
            
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
    
    public function pega_valor_total($dados = array()) {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("vli" => "vinculo_lote_item"), array("soma" => "sum(v.valor)"));
        $sql->join(array("vlis" => "vinculo_lote_item_status"), "vli.id_vinculo_lote_item_status = vlis.id_vinculo_lote_item_status", array());
        $sql->join(array("v" => "valor"), "vli.id_valor = v.id_valor", array());
        $sql->where("id_vinculo_lote = {$this->getId()}");
        $sql->where("vlis.chave <> 'IN'");
        if (isset($dados["tipo_item"]) && $dados["tipo_item"]) {
            $sql->where("vli.tipo = '{$dados["tipo_item"]}'");
        }
        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $sql->where("vli.tipo = '{$dados["tipo"]}'");
        }
        if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
            $sql->where("vli.id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
/*
            $sql->where("vli.tipo = 'BO'");
            $sql->join(array("b" => "bolsista"), "vli.chave = b.id_bolsista", array());
            $sql->where("b.id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
*/
        }
        $stmt = $db->query($sql);
        if ($stmt) {
            $obj = $stmt->fetch(Zend_Db::FETCH_OBJ);
            return $obj->soma;
        }
        return 0;
    }
    
    public function pega_valor_previsao($dados = array()) {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("p" => "previsao"), array("id_previsao_tipo", "id_bolsa_tipo", "soma" => "sum(v.valor)"));
        $sql->join(array("v" => "valor"), "p.id_valor = v.id_valor", array());
        $sql->where("id_vinculo = {$this->id_vinculo}");
        $sql->where("ano = {$this->ano}");
        $sql->where("mes = {$this->mes}");
        $sql->group("id_previsao_tipo");
        $sql->group("id_bolsa_tipo");
        if (isset($dados["tipo_item"]) && $dados["tipo_item"]) {
            $tb = new TbPrevisaoTipo();
            $pt = $tb->getPorChave($dados["tipo_item"]);
            if ($pt) {
                $sql->where("id_previsao_tipo = {$pt->getId()}");
            }
        }
        if (isset($dados["tipo"]) && $dados["tipo"]) {
            $tb = new TbPrevisaoTipo();
            $pt = $tb->getPorChave($dados["tipo"]);
            if ($pt) {
                $sql->where("id_previsao_tipo = {$pt->getId()}");
            }
        }
        if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
            $sql->where("id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
        }
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $soma = 0;
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $pt = TbPrevisaoTipo::pegaPorId($obj->id_previsao_tipo);
                if ($pt && $pt->bolsista()) {
                    $bt = TbBolsaTipo::pegaPorId($obj->id_bolsa_tipo);
                    if ($bt) {
                        $soma += ($bt->pega_valor()->valor * $obj->soma);
                    }
                } else {
                    $soma += $obj->soma;
                }
            }
            return $soma;
        }
        return 0;
    }
    
    public function toStringMenor() {
        $infos = array();
        $txt = $this->ano;
        $mes = Escola_Util::pegaMes($this->mes);
        if ($mes) {
            $txt .= " / " . $mes;
        }
        $infos[] = $txt;
        $status = $this->findParentRow("TbVinculoLoteStatus");
        if ($status) {
            $infos[] = $status->toString();
        }
        return implode(" - ", $infos);
    }
    
    public function toString($full = true) {
        $infos = array();
        if ($full) {
            $vinculo = $this->findParentRow("TbVinculo");
            if ($vinculo) {
                $infos[] = $vinculo->toString();
            }
        }
        $infos[] = $this->toStringMenor();
        return implode(" - ", $infos);
    }
    
    public function pega_items($dados = array()) {
        if ($this->getId()) {
            $tb = new TbVinculoLoteItem();
            $dados["id_vinculo_lote"] = $this->getId();
            $objs = $tb->listar($dados);
            if ($objs && count($objs)) {
                return $objs;
            }
        }
        return false;
    }
    
    public function possui_bolsista($bolsista) {
        $db = Zend_Registry::get("db");
        $tb = new TbVinculoLoteItem();
        $sql = $tb->getSql(array("id_vinculo_lote" => $this->getId(), "tipo" => "BO", "chave" => $bolsista->getId()));
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            return true;
        }
        return false;
    }
    
    public function add_bolsista($bolsista) {
        if (!$this->possui_bolsista($bolsista)) {
            $tb = new TbVinculoLoteItem();
            $item = $tb->createRow();
            $dados = array("id_vinculo_lote" => $this->getId(), "tipo" => "BO", "chave" => $bolsista->getId());
            $bt = $bolsista->findParentRow("TbBolsaTipo");
            if ($bt) {
                $dados["id_bolsa_tipo"] = $bt->getId();
                $dados["valor"] = $bt->pega_valor()->valor;
            }
            $item->setFromArray($dados);
            $errors = $item->getErrors();
            if (!$errors) {
                $item->save();
            }
        }
    }
    
    public function liberar() {
        $status = $this->findParentRow("TbVinculoLoteStatus");
        if ($status && $status->aguardando_liberacao()) {
            $tb = new TbVinculoLoteStatus();
            $status = $tb->getPorChave("AG");
            if ($status->getId()) {
                $this->id_vinculo_lote_status = $status->getId();
                $this->save();
                $this->refresh();
                $status = $this->findParentRow("TbVinculoLoteStatus");
                if ($status && $status->aguardando_aprovacao()) {
                    $tb = new TbVinculoLoteOcorrenciaTipo();
                    $vlot = $tb->getPorChave("LB");
                    if ($vlot) {
                        $usuario = TbUsuario::pegaLogado();
                        $dados = array("id_vinculo_lote" => $this->getId(),
                                       "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId(),
                                       "id_usuario" => $usuario->getId(),
                                       "observacoes" => "Liberação do Lote para Aprovação pelo Coordenador.");
                        $tb = new TbVinculoLoteOcorrencia();
                        $vlo = $tb->createRow();
                        $vlo->setFromArray($dados);
                        $vlo->save();
                    }
                }
            }
        }
        return false;
    }
    
    public function atualiza_bolsista() {
        $vinculo = $this->findParentRow("TbVinculo");
        if ($vinculo) {
            $tb = new TbBolsistaStatus();
            $status = $tb->getPorChave("A");
            $tb = new TbBolsista();
            $registros = $tb->listar(array("id_vinculo" => $vinculo->getId(), "id_bolsista_status" => $status->getId()));
            if (count($registros)) {
                foreach ($registros as $registro) {
                    $loteitems = $this->pega_items(array("tipo" => "BO", "chave" => $registro->getId()));
                    if (!$loteitems) {
                        $this->add_bolsista($registro);
                    }
                }
                $loteitems = $this->pega_items(array("tipo" => "BO"));
                foreach ($loteitems as $item) {
                    $bolsista = $item->pega_referencia();
                    if ($bolsista) {
                        $status = $bolsista->findParentRow("TbBolsistaStatus");
                        if ($status && !$status->ativo()) {
                            $item->delete();
                        }
                    }
                }
            } else {
                $loteitems = $this->pega_items();
                if ($loteitems) {
                    foreach ($loteitems as $loteitem) {
                        $loteitem->delete();
                    }
                }
            }
            return true;
        }
        return false;
    }
    
    public function listar_tipo() {
        $tb = new TbVinculoLoteItem();
        $sql = $tb->select();
        $sql->from(array("vli" => "vinculo_lote_item"), array("tipo", "id_bolsa_tipo", "quantidade" => "count(id_vinculo_lote_item)"));
        $sql->where("vli.id_vinculo_lote = {$this->getId()}");
        $sql->group("vli.tipo");
        $sql->group("vli.id_bolsa_tipo");
        $db = Zend_Registry::get("db");
        $stmt = $db->query($sql);
        if ($stmt && count($stmt)) {
            $tb = new TbPrevisaoTipo();
            $items = array();
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $obj->valor = $this->pega_valor_total(array("tipo_item" => $obj->tipo, "id_bolsa_tipo" => $obj->id_bolsa_tipo));
/* 
                $pt = $tb->getPorChave($obj->tipo);
                if ($pt && $pt->bolsista()) {
                    $sql = $db->select();
                    $sql->from(array("bli" => "vinculo_lote_item"), array("bt.id_bolsa_tipo", "quantidade" => "count(bli.id_vinculo_lote_item)"));
                    $sql->join(array("b" => "bolsista"), "bli.chave = b.id_bolsista", array());
                    $sql->join(array("bt" => "bolsa_tipo"), "b.id_bolsa_tipo = bt.id_bolsa_tipo", array());
                    $sql->where("bli.id_vinculo_lote = {$this->getId()}");
                    $sql->where("bli.tipo = '{$obj->tipo}'");
                    $sql->group("bt.id_bolsa_tipo");
                    $stmt1 = $db->query($sql);
                    if ($stmt1 && $stmt1->rowCount()) {
                        while ($obj1 = $stmt1->fetch(Zend_Db::FETCH_OBJ)) {
                            $row = new stdClass();
                            $row->tipo = $obj->tipo;
                            $row->id_bolsa_tipo = $obj1->id_bolsa_tipo;
                            $row->quantidade = $obj1->quantidade;
                            $items[] = $row;
                        }
                    }
                } else {
                    $row = new stdClass();
                    $row->tipo = $obj->tipo;
                    $row->id_bolsa_tipo = false;
                    $row->quantidade = $obj->quantidade;
                    $items[] = $row;
                }
*/
                $items[] = $obj;
            }
            return $items;
        }
        return false;
    }
    
    public function pega_mensagem($dados) {
        $total = $this->pega_valor_total($dados);
        $previsao = $this->pega_valor_previsao($dados);
        $mensagem = "";
        if ($total > $previsao) {
            $mensagem = "Valor Vinculado Maior que a Previsão!";
        }
        return $mensagem;
    }
    
    public function get_erros_aprovar($usuario = false) {
        $erros = array();
        if ($this->findParentRow("TbVinculoLoteStatus")->aguardando_aprovacao()) {
            if ($usuario) {
                $vinculo = $this->findParentRow("TbVinculo");
                $pf = $vinculo->pega_coordenador();
                if ($vinculo && ($pf->getId() == $usuario->id_pessoa_fisica)) {
                    $tipos = $this->listar_tipo();
                    $flag = true;
                    foreach ($tipos as $tipo) {
                        $mensagem = $this->pega_mensagem(array("tipo_item" => $tipo->tipo, "id_bolsa_tipo" => $tipo->id_bolsa_tipo));
                        if ($mensagem) {
                            $msg = array();
                            if ($tipo->tipo) {
                                $tb = new TbPrevisaoTipo();
                                $pt = $tb->getPorChave($tipo->tipo);
                                if ($pt) {
                                    $msg[] = "Tipo de Previsão: " . $pt->toSTring();
                                }
                            }
                            if ($tipo->id_bolsa_tipo) {
                                $tb = new TbBolsaTipo();
                                $bt = $tb->getPorId($tipo->id_bolsa_tipo);
                                if ($bt) {
                                    $msg[] = "Tipo de Despesa: " . $bt->toString();
                                }
                            }
                            if (count($msg)) {
                                $mensagem = $mensagem  . " [" . implode(", ", $msg) . "]";
                            }
                            $erros[] = $mensagem;
                        }
                    }
                }
            }
            if (!count($erros)) {
                $tb = new TbVinculoLote();
                $lotes = $tb->listar(array("id_vinculo" => $vinculo->getId()));
                if ($lotes) {
                    $ano_mes_atual = (int)$this->ano . $this->mes;
                    foreach ($lotes as $lote) {
                        if ($lote->getId() != $this->getId()) {
                            if (!$lote->findParentRow("TbVinculoLoteStatus")->pc()) {
                                $ano_mes = (int)$lote->ano . $lote->mes;
                                if ($ano_mes < $ano_mes_atual) {
                                    $erros[] = "Para Aprovar um Lote, Todos os Lotes anteriores deverão estar com a prestação de Contas Confirmada!";
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $erros[] = "Lote não Disponível para Aprovação!";
        }
        if (count($erros)) {
            return $erros;
        }
        return false;
    }
    
    public function habilita_aprovar($usuario = false) {
        return (!$this->get_erros_aprovar($usuario));
    }
    
    public function aprovar($usuario) {
        $status = $this->findParentRow("TbVinculoLoteStatus");
        if ($status && $status->aguardando_aprovacao()) {
            $tb = new TbVinculoLoteStatus();
            $status = $tb->getPorChave("AP");
            if ($status->getId()) {
                $this->id_vinculo_lote_status = $status->getId();
                $this->save();
                $this->refresh();
                $status = $this->findParentRow("TbVinculoLoteStatus");
                if ($status && $status->aprovado()) {
                    $tb = new TbVinculoLoteOcorrenciaTipo();
                    $vlot = $tb->getPorChave("AP");
                    if ($vlot) {
                        $dados = array("id_vinculo_lote" => $this->getId(),
                                       "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId(),
                                       "id_usuario" => $usuario->getId(),
                                       "observacoes" => "Aprovação do Lote.");
                        $tb = new TbVinculoLoteOcorrencia();
                        $vlo = $tb->createRow();
                        $vlo->setFromArray($dados);
                        $vlo->save();
                    }
                    return true;
                }
            }
        }
        return false;
    }
    
    public function gerar_nf($usuario) {
        $status = $this->findParentRow("TbVinculoLoteStatus");
        if ($status && $status->aprovado()) {
            $tb = new TbVinculoLoteStatus();
            $status = $tb->getPorChave("NF");
            if ($status->getId()) {
                $this->id_vinculo_lote_status = $status->getId();
                $this->save();
                $this->refresh();
                $status = $this->findParentRow("TbVinculoLoteStatus");
                if ($status && $status->nf()) {
                    $tb = new TbVinculoLoteOcorrenciaTipo();
                    $vlot = $tb->getPorChave("NF");
                    if ($vlot) {
                        $dados = array("id_vinculo_lote" => $this->getId(),
                                       "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId(),
                                       "id_usuario" => $usuario->getId(),
                                       "observacoes" => "Geração de Nota Fiscal.");
                        $tb = new TbVinculoLoteOcorrencia();
                        $vlo = $tb->createRow();
                        $vlo->setFromArray($dados);
                        $vlo->save();
                    }
                    return true;
                }
            }
        }
        return false;
    }
    
    public function registra_recurso($usuario) {
        $status = $this->findParentRow("TbVinculoLoteStatus");
        if ($status && $status->nf()) {
            $tb = new TbVinculoLoteStatus();
            $status = $tb->getPorChave("RC");
            if ($status && $status->getId()) {
                $this->id_vinculo_lote_status = $status->getId();
                $this->save();
                $this->refresh();
                $status = $this->findParentRow("TbVinculoLoteStatus");
                if ($status && $status->recurso()) {
                    $tb = new TbVinculoLoteOcorrenciaTipo();
                    $vlot = $tb->getPorChave("RC");
                    if ($vlot) {
                        $dados = array("id_vinculo_lote" => $this->getId(),
                                       "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId(),
                                       "id_usuario" => $usuario->getId(),
                                       "observacoes" => "Recebimento do Recurso.");
                        $tb = new TbVinculoLoteOcorrencia();
                        $vlo = $tb->createRow();
                        $vlo->setFromArray($dados);
                        $vlo->save();
                    }
                    return true;
                }
            }
        }
        return false;
    }
    
    public function pagar($dados) {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            if (isset($dados["id_usuario"]) && $dados["id_usuario"]) {
                $id_usuario = $dados["id_usuario"];
            } else {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, USUÁRIO INVÁLIDO!");
            }
            $status = $this->findParentRow("TbVinculoLoteStatus");
            if ($status && $status->recurso()) {
                $tb = new TbVinculoLoteStatus();
                $status = $tb->getPorChave("PG");
                if ($status->getId()) {
                    $this->id_vinculo_lote_status = $status->getId();
                    $this->save();
                    $this->refresh();
                    $status = $this->findParentRow("TbVinculoLoteStatus");
                    if ($status && $status->pago()) {
                        $tb = new TbVinculoLoteOcorrenciaTipo();
                        $vlot = $tb->getPorChave("PG");
                        if ($vlot) {
                            $dados_vlot = array("id_vinculo_lote" => $this->getId(),
                                                "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId(),
                                                "id_usuario" => $id_usuario,
                                                "observacoes" => "Confirmação de Pagamento.");
                            if (isset($dados["data_pagamento"]) && $dados["data_pagamento"]) {
                                $dados_vlot["data"] = $dados["data_pagamento"];
                            }
                            if (isset($dados["hora_pagamento"]) && $dados["hora_pagamento"]) {
                                $dados_vlot["hora"] = $dados["hora_pagamento"];
                            }
                            $tb = new TbVinculoLoteOcorrencia();
                            $vlo = $tb->createRow();
                            $vlo->setFromArray($dados_vlot);
                            $vlo->save();
                        }
                        $tb = new TbVinculoLoteItem();
                        $vlis = $tb->listar(array("id_vinculo_lote" => $this->getId()));
                        if ($vlis) {
                            foreach ($vlis as $vli) {
                                $vli->pagar();
                            }
                        }
                        if ($vlo->getId()) {
                            $tipos = $this->listar_tipo();
                            if ($tipos) {
                                foreach ($tipos as $tipo) {
                                    $chave = $tipo->tipo . "_" . $tipo->id_bolsa_tipo;
                                    if (isset($dados["chave"]) && in_array($chave, $dados["chave"])) {
                                        $dados_vlop = array();
                                        $flags = explode("_", $chave);
                                        if (count($flags) == 2) {
                                            $tipo_despesa_chave = $flags[0];
                                            $tb = new TbPrevisaoTipo();
                                            $dt = $tb->getPorChave($tipo_despesa_chave);
                                            if ($dt) {
                                                $dados_vlop["id_previsao_tipo"] = $dt->getId();
                                            }
                                            $dados_vlop["id_vinculo_lote_ocorrencia"] = $vlo->getId();
                                            $dados_vlop["id_bolsa_tipo"] = $flags[1];
                                            $dados_vlop["id_forma_pagamento"] = 0;
                                            if (isset($dados["id_forma_pagamento_" . $chave])) {
                                                $dados_vlop["id_forma_pagamento"] = $dados["id_forma_pagamento_" . $chave];
                                            }
                                            $dados_vlop["id_doc_comprovacao"] = 0;
                                            if (isset($dados["id_doc_comprovacao_" . $chave])) {
                                                $dados_vlop["id_doc_comprovacao"] = $dados["id_doc_comprovacao_" . $chave];
                                            }
                                            $dados_vlop["arquivo"] = Escola_Util::getUploadedFile("arquivo_" . $chave);
                                            $tb = new TbVinculoLoteOcorrenciaPgto();
                                            $vlop = $tb->createRow();
                                            $vlop->setFromArray($dados_vlop);
                                            $erros = $vlop->getErrors();
                                            if (!$erros) {
                                                $vlop->save();
                                            } else {
                                                Zend_Debug::dump($erros); die();
                                                throw new Exception("FALHA AO SALVAR PAGAMENTO DO LOTE");
                                            }
                                        } else {
                                            throw new Exception("FALHA AO SALVAR PAGAMENTO DO LOTE");
                                        }
                                    } else {
                                        throw new Exception("FALHA AO SALVAR PAGAMENTO DO LOTE");
                                    }
                                }
                            } else {
                                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                            }
                        } else {
                            throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, CHAME O ADMINISTRADOR!");
                        }
                    } else {
                        throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, STATUS DO LOTE INVÁLIDO!");
                    }
                } else {
                    throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, STATUS DO LOTE INVÁLIDO!");
                } 
            } else {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, STATUS DO LOTE INVÁLIDO!");
            }
            $db->commit();
            return false;
        } catch (Exception $ex) {
            $db->rollBack();
            return $ex->getMessage();
        }
        return false;
    }
    
    public function pega_ocorrencia() {
        $tb = new TbVinculoLoteOcorrencia();
        $registros = $tb->listar(array("id_vinculo_lote" => $this->getId()));
        if ($registros && count($registros)) {
            return $registros;
        }
        return false;
    }
    
    public function add_prestacao_conta($dados = array()) {
        if (isset($dados["usuario"]) && $dados["usuario"]) {
            $usuario = $dados["usuario"];
        } else {
            $usuario = TbUsuario::pegaLogado();
        }
        $arquivo = false;
        if (isset($dados["arquivo"]) && $dados["arquivo"]) {
            $arquivo = $dados["arquivo"];
        }
        if ($arquivo && $arquivo["size"]) {
            $tb = new TbArquivo();
            $arq = $tb->createRow();
            $arq->setFromArray(array("arquivo" => $arquivo));
            $arq->save();
            if ($arq->getId()) {
                $tb = new TbVinculoLoteStatus();
                $status = $tb->getPorChave("APC");
                if ($status->getId()) {
                    $this->id_vinculo_lote_status = $status->getId();
                    $this->save();
                    $this->refresh();
                    $status = $this->findParentRow("TbVinculoLoteStatus");
                    if ($status && $status->aguardando_pc()) {
                        $tb = new TbVinculoLoteOcorrenciaTipo();
                        $vlot = $tb->getPorChave("EPC");
                        if ($vlot) {
                            $dados = array("id_vinculo_lote" => $this->getId(),
                                           "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId(),
                                           "id_usuario" => $usuario->getId(),
                                           "observacoes" => 'Envio de Arquivo de Prestação de Contas.',
                                           "id_arquivo_pc" => $arq->getId());
                            $tb = new TbVinculoLoteOcorrencia();
                            $vlo = $tb->createRow();
                            $vlo->setFromArray($dados);
                            $vlo->save();
                        }
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    public function confirmar_pc($usuario) {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            $status = $this->findParentRow("TbVinculoLoteStatus");
            if (!$status || !$status->aguardando_pc()) {
                throw new Exception("Falha ao Executar Operação, Status do Lote Inválido!");
            }
            
            $tb = new TbVinculoLoteStatus();
            $status = $tb->getPorChave("PCC");
            if (!$status || !$status->getId()) {
                throw new Exception("Falha ao Executar Operação, Status do Lote Inválido!");
            }
            
            $tb = new TbVinculoLoteOcorrenciaTipo();
            $vlot = $tb->getPorChave("EPC");
            if (!$vlot) {
                throw new Exception("Falha ao Executar Operação, Tipo de Ocorrência de Lote Inválido!");
            }
            
            $tb = new TbVinculoLoteOcorrencia();
            $vlos = $tb->listar(array("id_vinculo_lote" => $this->getId(), "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId()));
            if (!$vlos || !count($vlos)) {
                throw new Exception("Falha ao Executar Operação, Ocorrência Aguardando Prestação de Contas Inválido!");
            }
            
            $vlo = $vlos->current();
            $arquivo_pc = $vlo->pega_arquivo_pc();
            if (!$arquivo_pc) {
                throw new Exception("Falha ao Executar Operação, Arquivo de Prestação de Contas Inválido!");
            }
            
            $this->id_vinculo_lote_status = $status->getId();
            $this->id_arquivo_pc = $arquivo_pc->getId();
            $this->save();
            $this->refresh();
            $status = $this->findParentRow("TbVinculoLoteStatus");
            if (!$status || !$status->pc()) {
                throw new Exception("Falha ao Executar Operação, Salvamento do Vínculo Inválido!");
            }
            
            $tb = new TbVinculoLoteOcorrenciaTipo();
            $vlot = $tb->getPorChave("PCC");
            if ($vlot) {
                $dados = array("id_vinculo_lote" => $this->getId(),
                               "id_vinculo_lote_ocorrencia_tipo" => $vlot->getId(),
                               "id_usuario" => $usuario->getId(),
                               "observacoes" => "Confirmação de Prestação de Contas.");
                $tb = new TbVinculoLoteOcorrencia();
                $vlo = $tb->createRow();
                $vlo->setFromArray($dados);
                $vlo->save();
            }
            
            if ($trans) {
                $db->commit();
            }
            return true;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
    
    public function pega_arquivo_pc() {
        $arquivo = $this->findParentRow("TbArquivo");
        if ($arquivo && file_exists($arquivo->pegaNomeCompleto())) {
            return $arquivo;
        }
        return false;
    }
    
    public function geraResumo() {
        $tb = new TbPrevisaoTipo();
        $items = array();
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("p" => "previsao"), array("pt.chave", "total" => "sum(v.valor)"));
        $sql->join(array("v" => "valor"), "p.id_valor = v.id_valor", array());
        $sql->join(array("pt" => "previsao_tipo"), "pt.id_previsao_tipo = p.id_previsao_tipo", array());
        $sql->where("p.id_vinculo = {$this->id_vinculo}");
        $sql->where("p.ano = {$this->ano}");
        $sql->where("p.mes = {$this->mes}");
        $sql->group("pt.chave");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            while ($obj = $stmt->fetchObject()) {
                if (isset($items[$obj->chave])) {
                    $item = $items[$obj->chave];
                } else {
                    $item = new stdClass();
                    $item->lote = 0;
                    $pt = $tb->getPorChave($obj->chave);
                    if ($pt) {
                        $item->tipo = $pt->toString();
                    }
                }
                $valor_previsao = $this->pega_valor_previsao(array("tipo" => $obj->chave));
                $item->previsao = $valor_previsao;
                $items[$obj->chave] = $item;
            }
        }
        
        $sql = $db->select();
        $sql->from(array("vli" => "vinculo_lote_item"), array("vli.tipo", "total" => "sum(v.valor)"));
        $sql->join(array("v" => "valor"), "vli.id_valor = v.id_valor", array());
        $sql->where("vli.id_vinculo_lote = {$this->getId()}");
        $sql->group("vli.tipo");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            while ($obj = $stmt->fetchObject()) {
                if (isset($items[$obj->tipo])) {
                    $item = $items[$obj->tipo];
                } else {
                    $item = new stdClass();
                    $item->previsao = 0;
                    $pt = $tb->getPorChave($obj->tipo);
                    if ($pt) {
                        $item->tipo = $pt->toString();
                    }
                }
                $item->lote = $obj->total;
                $items[$obj->tipo] = $item;
            }
        }
        if (count($items)) {
            ksort($items);
            return $items;
        }
        return false;
    }
    
    public function janelaResumo() {
        $html = "";
        if ($this->getId()) {
            $resumo = $this->geraResumo();
            ob_start();
?>
<!-- JANELA RESUMO LOTE -->
<script type="text/javascript">
$(document).ready(function() {
    $("#janela_resumo_lote_<?php echo $this->getId(); ?>").css({ "width" : "800px", "margin-left": "-400px" });
    $("#janela_resumo_lote_<?php echo $this->getId(); ?>").modal("hide");
});

function resumo_<?php echo $this->getId(); ?>() {
    $("#janela_resumo_lote_<?php echo $this->getId(); ?>").modal("show");
}
</script>
<div id="janela_resumo_lote_<?php echo $this->getId(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 id="myModalLabel">Resumo do Lote</h4>
    </div>
    <div class="modal-body">
        <table class="table table-striped table-bordered" id="tabela_lista">
            <thead>
                <tr>
                    <th colspan="3"><?php echo $this->toString(); ?></th>
                </tr>
                <tr>
                    <th>Tipo</th>
                    <th>Previsão</th>
                    <th>Lote</th>
                </tr>
            </thead>
            <tbody>
<?php 
if ($resumo) { 
    $total_previsao = $total_lote = 0;
    foreach ($resumo as $resumo_item) { 
        $total_previsao += $resumo_item->previsao;
        $total_lote += $resumo_item->lote;
?>
                <tr>
                    <td><?php echo $resumo_item->tipo; ?></td>
                    <td><div class="text-center"><?php echo Escola_Util::number_format($resumo_item->previsao); ?></div></td>
                    <td><div class="text-center"><?php echo Escola_Util::number_format($resumo_item->lote); ?></div></td>
                </tr>
<?php } ?>
                <tr>
                    <th><strong>TOTAL</strong></th>
                    <th><div class="text-center"><strong><?php echo Escola_Util::number_format($total_previsao); ?></strong></div></th>
                    <th><div class="text-center"><strong><?php echo Escola_Util::number_format($total_lote); ?></strong></div></th>
                </tr>
<?php } else { ?>
                <tr>
                    <td colspan="3">NENHUM REGISTRO LOCALIZADO!</td>
                </tr>
<?php } ?>
            </tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-remove-circle"></i><div>Fechar</div></button>
    </div>
</div>
<?php      
            $html = ob_get_contents();
            ob_end_clean();
        }
        return $html;
    }
    
    public function pegaPagamento($id_previsao_tipo, $id_bolsa_tipo) {
        if (!$id_previsao_tipo || !$id_bolsa_tipo) {
            return false;
        }
        
        $dados = array();
        $dados["id_vinculo_lote"] = $this->getId();
        $dados["id_previsao_tipo"] = $id_previsao_tipo;
        $dados["id_bolsa_tipo"] = $id_bolsa_tipo;

        $tb = new TbVinculoLoteOcorrenciaPgto();
        $objs = $tb->listar($dados);
        if ($objs) {
            $obj = $objs->current();
            return $obj;
        }
        return false;
    }
    
    public function aprovado() {
        $status = $this->findParentRow("TbVinculoLoteStatus");
        if ($status) {
            return $status->aprovado();
        }
        return false;
    }
    
    public function pegaPrestacaoConta($id_previsao_tipo, $id_bolsa_tipo) {
        if (!$id_previsao_tipo || !$id_bolsa_tipo) {
            return false;
        }
        
        $dados = array();
        $dados["id_vinculo_lote"] = $this->getId();
        $dados["id_previsao_tipo"] = $id_previsao_tipo;
        $dados["id_bolsa_tipo"] = $id_bolsa_tipo;

        $tb = new TbLotePrestacaoConta();
        $objs = $tb->listar($dados);
        if ($objs) {
            $obj = $objs->current();
            return $obj;
        }
        return false;
    }
}