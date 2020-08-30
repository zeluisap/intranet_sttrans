<?php
class VinculoLoteItem_BO extends VinculoLoteItem {
    
    public function setFromArray(array $dados) {
        parent::setFromArray($dados);
        if (isset($dados["id_bolsista"]) && $dados["id_bolsista"]) {
            $bolsista = TbBolsista::pegaPorId($dados["id_bolsista"]);
            if ($bolsista) {
                $this->chave = $bolsista->getId();
                $bolsa_tipo = $bolsista->findParentRow("TbBolsaTipo");
                if ($bolsa_tipo) {
                    $this->_valor->setFromArray(array("valor" => Escola_Util::number_format($bolsa_tipo->pega_valor()->valor)));
                }
            }
        }
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->tipo)) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (!trim($this->id_vinculo_lote_item_status)) {
			$msgs[] = "CAMPO STATUS OBRIGATÓRIO!";
		}
		if (!trim($this->id_vinculo_lote)) {
			$msgs[] = "CAMPO LOTE OBRIGATÓRIO!";
		}
		if (!trim($this->chave)) {
			$msgs[] = "CAMPO BOLSISTA OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" id_vinculo_lote = {$this->id_vinculo_lote} and tipo = '{$this->tipo}' and chave = '{$this->chave}' and id_vinculo_lote_item <> '{$this->getId()}' ");
        if ($rg && count($rg)) {
            $msgs[] = "ÍTEM DO LOTE JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
    }
    
    public function toForm(Zend_View_Abstract $view) {
        $vl = $this->findParentRow("TbVinculoLote");
        ob_start();
?>
<script type="text/javascript">
var ajax_obj = false;
var bolsistas = [];
$(document).ready(function() {
    $(".link_id_bolsista").click(function() {
        $("#janela_bolsista").modal("show");
    });
    $("#janela_bolsista").modal("hide").css({"width": "900px", "margin-left": "-450px"});
    $("#janela_bolsista").on("show", function() {
        listar_lote_item();
    });
    $("#janela_bolsista").on("shown", function() {
        $("#janela_bolsista_cpf").focus();
    });
    $("#janela_bolsista").on("keypress", function(event) {
        if (event.which == 13) {
            event.preventDefault();
            listar_lote_item();
        }
    });
    $("#btn_limpar_filtro").click(function() {
        $("#janela_bolsista").find(".filtro").val("");
        listar_lote_item();
    });
    $("#btn_procurar").click(function() {
        listar_lote_item();
    });
});

function listar_lote_item() {
    var bolsistas = [];
    $(".corpo_bolsista tr, .bolsista_paginacao").remove();
    if (ajax_obj) {
        ajax_obj.abort();
    }
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/vinculo/listarbolsista/format/json/",
        "type" : "POST",
        "data" : { "cpf": $("#janela_bolsista_cpf").val(),
                   "nome": $("#janela_bolsista_nome").val(),
                   "tipo": "<?php echo $view->getRequest()->getParam("tipo"); ?>",
                   "id_bolsa_tipo": "<?php echo $view->getRequest()->getParam("id_bolsa_tipo"); ?>",
                   "id_vinculo": "<?php echo $vl->id_vinculo; ?>",
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    bolsistas[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    tr.appendTo($(".corpo_bolsista"));
                    $('<td><a href="#" id="' + i + '" class="link_bolsista">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_bolsista">' + item.bolsa_tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_bolsista">' + item.cpf + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_bolsista">' + item.nome + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_bolsista">' + item.valor + '</a></td>').appendTo(tr);
                }
                $(".link_bolsista").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_id_bolsista").val(bolsistas[$(this).attr("id")].bolsa_tipo + " - " + bolsistas[$(this).attr("id")].cpf + " - " + bolsistas[$(this).attr("id")].nome);
                        $("#id_bolsista").val(bolsistas[$(this).attr("id")].id);
                        $("#janela_bolsista").modal("hide");
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="bolsista_paginacao">' + html + '</div>').appendTo($("#janela_bolsista .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='4' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($(".corpo_bolsista"));
            }
        }
    });    
}
</script>
<div id="janela_bolsista" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Adicionar Bolsista</h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <fieldset>
                <div class="control-group">
                    <label for="janela_bolsista_cpf" class="control-label">C.P.F.:</label>
                    <div class="controls">
                        <input type="text" name="janela_bolsista_cpf" id="janela_bolsista_cpf" value="" class="filtro cpf span4" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="janela_bolsista_nome" class="control-label">Nome:</label>
                    <div class="controls">
                        <input type="text" name="janela_bolsista_nome" id="janela_bolsista_nome" value="" size="60" class="filtro span7" />
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="tabela_resposta" class="table table-striped table-bordered">
            <thead class="head_destino">
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>C.P.F.</th>
                    <th>Nome</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody class="corpo_bolsista"></tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Limpar Filtro" id="btn_limpar_filtro" class="btn" />
        <input type="button" value="Procurar" id="btn_procurar" class="btn btn-primary" />
    </div>
</div>
        <div class="control-group" id="linha_id_bolsista">
            <label for="id_bolsista" class="control-label">Bolsista:</label>
            <div class="controls">
                <div class="input-append">
                    <input type="hidden" name="id_bolsista" id="id_bolsista" />
                    <input type="text" name="show_id_bolsista" id="show_id_bolsista" disabled="" class="input-xxlarge" value="">
                    <div class="add-on">
                        <a href="#" class="link_id_bolsista">
                            <i class="icon-search"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}