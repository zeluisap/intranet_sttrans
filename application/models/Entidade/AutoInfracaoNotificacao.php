<?php
class AutoInfracaoNotificacao extends Escola_Entidade {
    
    protected $_arquivo = false;

    public function pegaPessoaFisica() {
        $obj = $this->findParentRow("TbPessoaFisica");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }

    public function pegaVeiculo() {
        $obj = $this->findParentRow("TbVeiculo");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }
    
    public function init() {
        parent::init();
        $this->carregaArquivo();
    }
    
    public function carregaArquivo() {
        $arquivo = $this->findParentrow("TbArquivo");
        if (!$arquivo) {
            $tb = new TbArquivo();
            $arquivo = $tb->createRow();
        }
        $this->_arquivo = $arquivo;
    }
    
    public function pegaMedicao() {
        $medicaos = $this->findDependentRowset("TbNotificacaoMedicao");
        if ($medicaos && count($medicaos)) {
            return $medicaos->current();
        }
        return false;
    }

    public function setFromArray(array $data) {
        if (isset($data["arquivo"]) && isset($data["arquivo"]["size"]) && $data["arquivo"]["size"]) {
            $dados = array("arquivo" => $data["arquivo"],
                           "legenda" => "Notificação de Auto de Infração.");
            $this->_arquivo->setFromArray($dados);
        } 
        if (isset($data["data_infracao"])) {
            $data["data_infracao"] = Escola_Util::montaData($data["data_infracao"]);
        }
        if (isset($data["not_observacoes"])) {
            $data["observacoes"] = $data["not_observacoes"];
        }
        parent::setFromArray($data);
    }
    
    public function save($flag = false) {
        $id_ain = $this->getId();
        $this->_arquivo->save();
        if ($this->_arquivo->getId()) {
            $this->id_arquivo = $this->_arquivo->getId();
        }
        $return_id = parent::save($flag);
        if ($this->veiculo_retido()) {
            $veiculo = $this->findParentRow("TbVeiculo");
            if ($veiculo) {
                $tb = new TbVeiculoRetido();
                $tb->inserirVeiculo($veiculo, $this);
            }
        }
        return $return_id;
    }
    
    public function toString() {
        $items = array();
        $ai = $this->pegaAutoInfracao();
        if ($ai) {
            $items[] = $ai->toString();
        }
        $veiculo = $this->findParentRow("TbVeiculo");
        if ($veiculo) {
            if ($veiculo->placa) {
                $items[] = $veiculo->placa;
            } elseif ($veiculo->chassi) {
                $items[] = $veiculo->chassi;
            }
        }
        $pf = $this->findParentRow("TbPessoaFisica");
        if ($pf) {
            $items[] = $pf->toString();
        }
        return implode(" - ", $items);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->id_pessoa_fisica)) {
			$msgs[] = "CAMPO PESSOA FÍSICA OBRIGATÓRIO!";
		}
		if (!trim($this->id_veiculo)) {
			$msgs[] = "CAMPO VEÍCULO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $ss = $this->pegaServicoSolicitacao();
        if ($ss && $ss->pago()) {
            $msgs[] = "NOTIFICAÇÃO JÁ ESTÁ PAGA!";
        }
        $rjs = $this->pegaRequerimentoJari();
        if ($rjs) {
            foreach ($rjs as $rj) {
                if ($rj->respondido()) {
                    $msgs[] = "NOTIFICAÇÃO DE AUTO DE INFRAÇÃO POSSUI UM REQUERIMENTO JARI RESPONDIDO!";
                }
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }    
    
    public function delete() {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            $ai = $this->pegaAutoInfracao();
            
            $db = Zend_Registry::get("db");
            $db->query("delete from notificacao_infracao where id_auto_infracao_notificacao = {$this->getId()}");
            $aios = $this->findDependentRowset("TbAutoInfracaoOcorrencia");
            if ($aios && count($aios)) {
                foreach ($aios as $aio) {
                    $errors = $aio->getErrors();
                    if (!$errors) {
                        $aio->delete();
                    } else {
                        throw new Exception(implode("<br>", $errors));
                    }
                }
            }
            $vrs = $this->findDependentRowset("TbVeiculoRetido");
            if ($vrs && count($vrs)) {
                foreach ($vrs as $vr) {
                    $errors = $vr->getErrors();
                    if (!$errors) {
                        $vr->delete();
                    } else {
                        throw new Exception(implode("<br>", $errors));
                    }
                }
            }
            $rjs = $this->pegaRequerimentoJari();
            if ($rjs && count($rjs)) {
                foreach ($rjs as $rj) {
                    $rj->delete();
                }
            }
            $return_id = parent::delete();
            $tb = new TbAutoInfracaoStatus();
            $ais = $tb->getPorChave("EN");
            if ($ais) {
                $ai->id_auto_infracao_status = $ais->getId();
                $ai->id_auto_infracao_devolucao_status = null;
                $ai->save();
            } else {
                throw new Exception("STATUS DO AUTO DE INFRAÇÃO INVÁLIDO!");
            }
            $db->commit();
            return $return_id;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        return false;
    }
    
    public function render(Zend_View_Abstract $view) {
        ob_start();
?>
<script type="text/javascript">
var ajax_notificacao_obj = false;
var infracaos = [];
$(document).ready(function() {
    $(".link_add").click(function(event) {
        event.preventDefault();
        $("#janela_notificacao").modal("show");
    });
    $("#janela_notificacao").css({ "width": "900px", "margin-left": "-450px"}).modal("hide");
    $("#janela_notificacao").on("show", function() {
        atualiza_infracao();
    });
    $("#janela_notificacao #btn_procurar").click(function() {
        atualiza_infracao();
    });
    $("#janela_notificacao .filtro").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            atualiza_infracao();
        }        
    })
    $("#janela_notificacao #btn_limpar_filtro").click(function() {
        $("#janela_notificacao .filtro").val("");
        $("#janela_notificacao_pagina").val("1");
        atualiza_infracao();
    });
});

function atualiza_infracao() {
    $("#janela_notificacao_resposta .corpo_destino tr, .notificacao_paginacao").remove();
    $("#janela_notificacao_resposta").hide();
    if (ajax_notificacao_obj != false) {
        ajax_notificacao_obj.abort();
    }
    ajax_notificacao_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/infracao/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "id_amparo_legal": $("#janela_id_amparo_legal").val(),
                   "descricao": $("#janela_descricao").val(),
                   "pagina_atual": $("#janela_notificacao_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            pjs = [];
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    infracaos[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.codigo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.descricao + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.amparo_legal + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.moeda + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.valor + '</a></td>').appendTo(tr);
                    tr.appendTo($("#janela_notificacao_resposta .corpo_destino"));
                }
                $(".link_destino").click(
                    function(event) {
                        event.preventDefault();
                        var infracao_item = infracaos[$(this).attr("id")];
                        var id = infracao_item.id;
                        var flag = true;
                        $(".id_infracao").each(function(index, obj) {
                            if ($(this).val() == id) {
                                flag = false;
                            }
                        })
                        if (flag) {
                            var ultima = $(".p_clone").last();
                            var clone = ultima.clone();
                            clone.find(".id_infracao").val(infracao_item.id);
                            clone.find(".id_amparo_legal_txt").val(infracao_item.tostring);
                            clone.find("i.icon-plus").attr("class", "icon-minus");
                            clone.find("a.link_add").attr("class", "link_minus");
                            ultima.before(clone);
                            $(".link_minus").on("click", function(event) {
                                event.preventDefault();
                                var obj = $(this).parents(".p_clone");
                                if (obj.length) {
                                    obj.remove();
                                }
                            });
                        }
                        $("#janela_notificacao").modal("hide");
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                paginacao.nome_funcao = "notificacao_set_page";
                var html = paginacao.render();
                $('<div class="notificacao_paginacao">' + html + '</td></tr>').appendTo($("#janela_notificacao .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='5' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#janela_notificacao_resposta"));
            }
            $("#janela_notificacao .filtro").first().focus();
            $("#janela_notificacao_resposta").show();
        }
    });
}

function notificacao_set_page(pagina) {
    $("#janela_notificacao_pagina").val(pagina);
    atualiza_infracao();
}
</script>
<div id="janela_notificacao" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Infrações</h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <fieldset>
<?php 
$ctrl = new Escola_Form_Element_Select_Table("janela_id_amparo_legal");
$ctrl->setAttrib("class", "filtro");
$ctrl->setPkName("id_amparo_legal");
$ctrl->setModel("TbAmparoLegal");
$ctrl->setValue("");
$ctrl->setLabel("Amparo Legal:");
echo $ctrl->render($view);
?>
                <div class="control-group">
                    <label for="janela_descricao" class="control-label">Descrição:</label>
                    <div class="controls">
                        <input type="text" name="janela_descricao" id="janela_descricao" value="" class="filtro span5" />
                    </div>
                </div>
            </fieldset>
        </div>
        <input type="hidden" name="janela_notificacao_pagina" id="janela_notificacao_pagina" />
        <table id="janela_notificacao_resposta" class="table table-striped table-bordered">
            <thead class="head_destino">
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Descrição da Infração</th>
                    <th>Amparo Legal</th>
                    <th>Moeda</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody class="corpo_destino"></tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Limpar Filtro" id="btn_limpar_filtro" class="btn" />
        <input type="button" value="Procurar" id="btn_procurar" class="btn btn-primary" />
    </div>
</div>    
<fieldset>
    <legend>Notificação</legend>
<?php 
        $ctrl = new Escola_Form_Element_Select_Table_Veiculo("id_veiculo");
        $ctrl->setLabel("Veículo:");
        echo $ctrl->render($view);

        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        $pj = $sistema->findParentRow("TbPessoaJuridica");
?>
    <div class="control-group">
        <label for="veiculo_recolhido" class="control-label">Veículo Recolhido ao Pátio da <?php echo $pj->sigla; ?>?</label>
        <div class="controls">
            <select name="veiculo_recolhido" id="veiculo_recolhido">
                <option value="" selected>==> SELECIONE <==</option>
                <option value="S">SIM</option>
                <option value="N">NÃO</option>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label for="clandestino" class="control-label">Veículo Clandestino?</label>
        <div class="controls">
            <select name="clandestino" id="clandestino">
                <option value="" selected>==> SELECIONE <==</option>
                <option value="S">SIM</option>
                <option value="N">NÃO</option>
            </select>
        </div>
    </div>
<?php
        $ctrl = new Escola_Form_Element_Select_Table_PessoaFisica("id_pessoa_fisica");
        $ctrl->setLabel("Motorista:");
        echo $ctrl->render($view);
?>
    <div class="control-group">
        <label for="" class="control-label">Infração(ões):</label>
        <div class="controls">
            <div class="p_clone">
                <div class="input-append">
                    <input type="hidden" name="id_infracao[]" id="" class="id_infracao" />
                    <input type="text" name="id_amparo_legal_txt" id="" class="id_amparo_legal_txt input-xxlarge" disabled />
                    <div class="add-on"><a href="#" class="link_add"><i class="icon-plus"></i></a></div>
                </div>
                <br /><br />
            </div>
        </div>
    </div>
    <div class="control-group">
        <label for="data_infracao" class="control-label">Data / Hora da Infração:</label>
        <div class="controls">
            <input type="text" name="data_infracao" id="data_infracao" class="data span2" /> <input type="text" name="hora_infracao" id="hora_infracao" class="hora span2" />
        </div>
    </div>
    <div class="control-group">
        <label for="local_infracao" class="control-label">Local da Infração:</label>
        <div class="controls">
            <textarea name="local_infracao" id="local_infracao" rows="6" class="span6"></textarea>
        </div>
    </div>
    <div class="control-group">
        <label for="arquivo" class="control-label">Imagem Notificação:</label>
        <div class="controls">
            <input type="file" name="arquivo" id="arquivo" />
        </div>
    </div>
    <div class="control-group">
        <label for="not_observacoes" class="control-label">Observações:</label>
        <div class="controls">
            <textarea name="not_observacoes" id="not_observacoes" rows="6" class="span6"></textarea>
        </div>
    </div>
</fieldset>
<?php
        $tb = new TbNotificacaoMedicao();
        $medicao = $tb->createRow();
        echo $medicao->render($view);
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    public function possuiInfracao($id_infracao) {
        if ($id_infracao) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("notificacao_infracao"));
            $sql->where("id_auto_infracao_notificacao = {$this->getId()}");
            $sql->where("id_infracao = {$id_infracao}");
            $stmt = $db->query($sql);
            if ($stmt && $stmt->rowCount()) {
                return true;
            }
        }
        return false;
    }
    
    public function addInfracao($infracao) {
        if ($infracao && $infracao->getId() && !$this->possuiInfracao($infracao->getId())) {
            $db = Zend_Registry::get("db");
            $db->query("insert into notificacao_infracao (id_auto_infracao_notificacao, id_infracao) values ({$this->getId()}, {$infracao->getId()})");
        }
    }
    
    public function removeInfracao($infracao) {
        if ($infracao && $infracao->getId()) {
            $db = Zend_Registry::get("db");
            $sql = "delete from auto_infracao_notificacao
                    where (id_auto_infracao_notificacao = {$this->getId()})
                    and (id_infracao = {$infracao->getId()})";
            $db->query($sql);
        }
    }
    
    public function listarInfracao() {
        if ($this->getId()) {
            $tb = new TbInfracao();
            $sql = $tb->select();
            $sql->from(array("i" => "infracao"));
            $sql->join(array("ni" => "notificacao_infracao"), "i.id_infracao = ni.id_infracao", array());
            $sql->where("ni.id_auto_infracao_notificacao = {$this->getId()}");
            $sql->order("i.descricao");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                return $rs;
            }
        }
        return false;
    }
    
    public function infracaoAtiva($infracao) {
        $rjs = $this->pegaRequerimentoJari();
        if ($rjs) {
            foreach ($rjs as $rj) {
                if ($rj->deferimento_parcial()) {
                    $rjrs = $rj->pegaResposta();
                    if ($rjrs) {
                        $rjr = $rjrs->current();
                        $infracaos = $rjr->listarInfracoes();
                        if ($infracaos) {
                            foreach ($infracaos as $rjr_infracao) {
                                if ($infracao->getId() == $rjr_infracao->getId()) {
                                    return false;
                                }
                            }
                        }
                    }
                } elseif ($rj->deferimento_total()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function listarInfracaoAtivas() {
        $rj = false;
        $infracaos = $this->listarInfracao();
        if ($infracaos) {
            $items = array();
            foreach ($infracaos as $infracao) {
                if ($this->infracaoAtiva($infracao)) {
                    $items[] = $infracao;
                }
            }
            return $items;
        }
        return false;
    }
    
    public function view(Zend_View_Abstract $view) {
        $txt_status_pagamento = $txt_auto_infracao = "";
        $txt_valor_total = $txt_valor_pagar = "--";
        $veiculo = $this->findParentRow("TbVeiculo");
        $pf = $this->findParentRow("TbPessoaFisica");
        $infracaos = $this->listarInfracao();
        $infracao_txt = array();
        if ($infracaos) {
            foreach ($infracaos as $infracao) {
                if ($this->infracaoAtiva($infracao)) {
                    $infracao_txt[] = $infracao->toString();
                } else {
                    $infracao_txt[] = "<div style='text-decoration: line-through;'>{$infracao->toString()}</div>";
                }
            }
        }
        $tb = new TbMoeda();
        $moeda_padrao = $tb->pega_padrao();
        $txt_status_pagamento = $this->mostrarStatus();
        $ai = $this->pegaAutoInfracao();
        if ($ai) {
            $txt_auto_infracao = $ai->toString();
        }
        $txt_valor_total = Escola_Util::number_format($this->pegaValorTotal());
        $valor_pagar = $this->pegaValorFinal();
        if ($valor_pagar) {
            $txt_valor_pagar = Escola_Util::number_format($valor_pagar);
        }
        ob_start();
        if ($this->getId()) {
?>
<dl class="dl-horizontal"><dt>ID:</dt><dd><?php echo $this->getId(); ?></dd></dl>
<?php } ?>
<?php if ($txt_auto_infracao) { ?>
<dl class="dl-horizontal"><dt>Auto de Infração:</dt><dd><?php echo $txt_auto_infracao; ?></dd></dl>
<?php } ?>
<dl class="dl-horizontal"><dt>Veículo:</dt><dd><?php echo $veiculo->toString(); ?></dd></dl>
<dl class="dl-horizontal"><dt>Veículo Clandestino?</dt><dd><?php echo $this->mostrarClandestino(); ?></dd></dl>
<dl class="dl-horizontal"><dt>Veículo Recolhido?</dt><dd><?php echo $this->mostrarVeiculoRecolhido(); ?></dd></dl>
<dl class="dl-horizontal"><dt>Motorista:</dt><dd><?php echo $pf->toString(); ?></dd></dl>
<?php if (count($infracao_txt)) { ?>
<dl class="dl-horizontal"><dt>Infrações:</dt><dd><ul><li><?php echo implode("</li><li>", $infracao_txt); ?></li></ul></dd></dl>
<?php } ?>
<?php if ($moeda_padrao) { ?>
<dl class="dl-horizontal"><dt>Valor Total ( <?php echo $moeda_padrao->simbolo; ?> ):</dt><dd><?php echo $txt_valor_total; ?></dd></dl>
<dl class="dl-horizontal"><dt>Valor a Pagar ( <?php echo $moeda_padrao->simbolo; ?> ):</dt><dd><?php echo $txt_valor_pagar; ?></dd></dl>
<?php } ?>
<?php if ($txt_status_pagamento) { ?>
<dl class="dl-horizontal"><dt>Situação do Pagamento:</dt><dd><?php echo $txt_status_pagamento; ?></dd></dl>
<?php } ?>
<dl class="dl-horizontal"><dt>Data / Hora Infração:</dt><dd><?php echo Escola_Util::formatData($this->data_infracao); ?> <?php echo $this->hora_infracao; ?></dd></dl>
<dl class="dl-horizontal"><dt>Local da Infração:</dt><dd><?php echo $this->local_infracao; ?></dd></dl>
<?php if ($this->_arquivo->existe()) { ?>
<dl class="dl-horizontal"><dt>Arquivo:</dt><dd><a href="<?php echo $view->url(array("controller" => "arquivo", "action" => "view", "id" => $this->_arquivo->getId()), null, true); ?>" target="_blank"><?php echo $this->_arquivo->miniatura(); ?></a></dd></dl>
<?php } ?>
<?php if ($this->observacoes) { ?>
<dl class="dl-horizontal"><dt>Observações:</dt><dd><?php echo $this->observacoes; ?></dd></dl>
<?php } ?>
<?php
        $medicao = $this->pegaMedicao();
        if ($medicao) {
            echo $medicao->view($view);
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function pegaValorTotal() {
        $valor = 0;
        if ($this->getId()) {
            $infracaos = $this->listarInfracao();
            if ($infracaos) {
                foreach ($infracaos as $infracao) {
                    $valor += $infracao->pega_valor()->converter();
                }
            }
        }
        return $valor;
    }
    
    public function pegaValorPagar() {
        $valor = 0;
        if ($this->getId()) {
            $infracaos = $this->listarInfracaoAtivas();
            if ($infracaos) {
                foreach ($infracaos as $infracao) {
                    $valor += $infracao->pega_valor()->converter();
                }
            }
        }
        return $valor;
    }
    
    public function pegaOcorrencia() {
        if ($this->getId()) {
            $tb = new TbAutoInfracaoOcorrencia();
            $sql = $tb->select();
            $sql->where("id_auto_infracao_notificacao = {$this->getId()}");
            $rs = $tb->fetchall($sql);
            if ($rs && count($rs)) {
                return $rs->current();
            }
        }
        return false;
    }
    
    public function veiculo_retido() {
        return ($this->veiculo_recolhido == "S");
    }
    
    public function mostrarVeiculoRecolhido() {
        if ($this->veiculo_retido()) {
            return "SIM";
        }
        return "NÃO";
    }
    
    public function mostrarClandestino() {
        if ($this->clandestino == "S") {
            return "SIM";
        }
        return "NÃO";
    }
    
    
    public function pegaAutoInfracao() {
        $aio = $this->pegaOcorrencia();
        if ($aio && $aio->getId()) {
            $ai = $aio->findParentRow("TbAutoInfracao");
            if ($ai) {
                return $ai;
            }
        }
        return false;
    }
    
    public function pegaServicoSolicitacao() {
        $tb_ss = new TbServicoSolicitacao();
        $sss = $tb_ss->listar(array("tipo" => "NO", "chave" => $this->getId()));
        if ($sss && count($sss)) {
            return $sss->current();
        }
        return false;
    }
    
    public function pendente() {
        $ss = $this->pegaServicoSolicitacao();
        if ($ss) {
            return $ss->aguardando_pagamento();
        }
    }
    
    public function pegaRequerimentoJari() {
        if ($this->getId()) {
            $tb = new TbRequerimentoJari();
            $rjs = $tb->listar(array("id_auto_infracao_notificacao" => $this->getId()));
            if ($rjs && count($rjs)) {
                return $rjs;
            }
        }
        return false;
    }
    
    public function pegaRequerimentoJariPendente() {
        if ($this->getId()) {
            $tb = new TbRequerimentoJariStatus();
            $rjs = $tb->getPorChave("AR");
            if ($rjs) {
                $tb = new TbRequerimentoJari();
                $rjs = $tb->listar(array("id_auto_infracao_notificacao" => $this->getId(), "id_requerimento_jari_status" => $rjs->getId()));
                if ($rjs && count($rjs)) {
                    $rj = $rjs->current();
                    return $rj;
                }
            }
        }
        return false;
    }
    
    public function emitir_boleto() {
        $jari = $this->pegaRequerimentoJariPendente();
        if ($jari) {
            return false;
        } else {
            $ss = $this->pegaServicoSolicitacao();
            if ($ss) {
                if ($ss->aguardando_pagamento()) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function mostrarStatus() {
        $jari = $this->pegaRequerimentoJariPendente();
        if ($jari) {
            return "Aguardando Resposta JARI";
        } else {
            $ss = $this->pegaServicoSolicitacao();
            if ($ss) {
                $sss = $ss->findParentRow("TbServicoSolicitacaoStatus");
                if ($sss) {
                    return $sss->toString();
                }
            }
        }
        return "";
    }
    
    public function cancelar() {
        $ss = $this->pegaServicoSolicitacao();
        if ($ss) {
            $ss->cancelar();
        }
    }
    
    public function pegaValorFinal() {
        $valor_final = $this->pegaValorPagar();
        $ss = $this->pegaServicoSolicitacao();
        if ($ss) {
            $valor_pagar = $ss->pega_valor_pagar();
            if ($valor_pagar) {
                $valor_final = $valor_pagar;
            }
        }
        return $valor_final;
    }
}