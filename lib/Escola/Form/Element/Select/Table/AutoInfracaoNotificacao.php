<?php
class Escola_Form_Element_Select_Table_AutoInfracaoNotificacao extends Escola_Form_Element_Select_Table {
    
    public function init() {
        parent::init();
        $this->setPkName("id_auto_infracaoNotificação");
        $this->setModel("TbAutoInfracaoNotificacao");
    }
	
    public function render(Zend_View_Interface $view = null) {
        $palavra_chave = "auto_infracao";
        $descricao = "Notificação de Auto de Infração";
        $ain = TbAutoInfracaoNotificacao::pegaPorId($this->getValue());
        $tb = new TbServicoSolicitacaoStatus();
        $sss = $tb->getPorChave("AG");
        $txt = "";
        if ($ain) {
            $txt = $ain->toString();
        }
        ob_start();
?>
<script type="text/javascript">
var <?php echo $this->getName(); ?>_<?php echo $palavra_chave; ?> = [];
$(document).ready(function() {
    $(".link_<?php echo $this->getName(); ?>").click(function(event) {
        event.preventDefault();
        $("#janela_<?php echo $this->getName(); ?>").modal("show");
    });
    $("#janela_<?php echo $this->getName(); ?>").css({ "width": "900px", "margin-left": "-450px" }).modal("hide");
    $("#janela_<?php echo $this->getName(); ?>").on("show", function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza_<?php echo $palavra_chave; ?>();
    });
    $("#janela_<?php echo $this->getName(); ?>").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            <?php echo $this->getName(); ?>_atualiza_<?php echo $palavra_chave; ?>();
        }
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_procurar").click(function() {
        <?php echo $this->getName(); ?>_atualiza_<?php echo $palavra_chave; ?>();
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_limpar_filtro").click(function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza_<?php echo $palavra_chave; ?>();
    });
})

function <?php echo $this->getName(); ?>_atualiza_<?php echo $palavra_chave; ?>() {
    $("#<?php echo $this->getName(); ?>_resposta .corpo_destino tr, .<?php echo $this->getName(); ?>_paginacao").remove();
    $("#<?php echo $this->getName(); ?>_resposta").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/autoinfracaonotificacao/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_placa": $("#<?php echo $this->getName(); ?>_filtro_placa").val(),
                   "filtro_chassi": $("#<?php echo $this->getName(); ?>_filtro_chassi").val(),
                   "filtro_alfa": $("#<?php echo $this->getName(); ?>_filtro_alfa").val(),
                   "filtro_codigo": $("#<?php echo $this->getName(); ?>_filtro_codigo").val(),
                   "filtro_pf_nome": $("#<?php echo $this->getName(); ?>_filtro_pf_nome").val(),
<?php if ($sss) { ?>
                   "filtro_id_servico_solicitacao_status": "<?php echo $sss->getId(); ?>",
<?php } ?>
                   "pagina_atual": $("#<?php echo $this->getName(); ?>_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            <?php echo $this->getName(); ?>_<?php echo $palavra_chave; ?> = [];
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    <?php echo $this->getName(); ?>_<?php echo $palavra_chave; ?>[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.auto_infracao + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.ocorrencia + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.data_hora + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.veiculo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.motorista + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.valor_total + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.status_pagamento + '</a></td>').appendTo(tr);
                    tr.appendTo($("#<?php echo $this->getName(); ?>_resposta .corpo_destino"));
                }
                $(".link_destino").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_<?php echo $this->getName(); ?>").val(<?php echo $this->getName(); ?>_<?php echo $palavra_chave; ?>[$(this).attr("id")].tostring);
                        $("#<?php echo $this->getName(); ?>").val(<?php echo $this->getName(); ?>_<?php echo $palavra_chave; ?>[$(this).attr("id")].id);
                        <?php echo $this->getName(); ?>fechar();
                        $("#<?php echo $this->getName(); ?>").change();
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                paginacao.nome_funcao = "<?php echo $this->getName(); ?>_set_page";
                var html = paginacao.render();
                $('<div class="<?php echo $this->getName(); ?>_paginacao">' + html + '</td></tr>').appendTo($("#janela_<?php echo $this->getName(); ?> .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#<?php echo $this->getName(); ?>_resposta"));
            }
            $("#janela_<?php echo $this->getName(); ?> .filtro").first().focus();
            $("#<?php echo $this->getName(); ?>_resposta").show();
        }
    });
}

function <?php echo $this->getName(); ?>fechar() {
    $("#janela_<?php echo $this->getName(); ?>").modal("hide");
}

function <?php echo $this->getName(); ?>_set_page(pagina) {
    $("#<?php echo $this->getName(); ?>_pagina").val(pagina);
    <?php echo $this->getName(); ?>_atualiza_pf();
}
</script>
<input type="hidden" name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" value="<?php echo $this->getValue(); ?>" />
<input type="hidden" name="<?php echo $this->getName(); ?>_pagina" id="<?php echo $this->getName(); ?>_pagina" />
<div id="janela_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel"><?php echo $descricao; ?></h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <fieldset>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_filtro_caracter" class="control-label">Auto de Infração:</label>
                    <div class="controls">
                        <input type="text" name="<?php echo $this->getName(); ?>_filtro_alfa" id="<?php echo $this->getName(); ?>_filtro_alfa" class="span1 filtro" value="" /><input type="text" name="<?php echo $this->getName(); ?>_filtro_codigo" id="<?php echo $this->getName(); ?>_filtro_codigo" size="5" value="" class="span2 filtro" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_filtro_placa" class="control-label">Placa:</label>
                    <div class="controls">
                        <input type="text" class="filtro span2" name="<?php echo $this->getName(); ?>_filtro_placa" id="<?php echo $this->getName(); ?>_filtro_placa" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_filtro_chassi" class="control-label">Chassi:</label>
                    <div class="controls">
                        <input type="text" class="filtro span2" name="<?php echo $this->getName(); ?>_filtro_chassi" id="<?php echo $this->getName(); ?>_filtro_chassi" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_filtro_pf_nome" class="control-label">Nome do Motorista:</label>
                    <div class="controls">
                        <input type="text" class="filtro span6" name="<?php echo $this->getName(); ?>_filtro_pf_nome" id="<?php echo $this->getName(); ?>_filtro_pf_nome" value="" />
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="<?php echo $this->getName(); ?>_resposta" class="table table-striped table-bordered">
            <thead class="head_destino">
                <tr>
                    <th>ID</th>
                    <th>Auto-Infração</th>
                    <th>Ocorrência</th>
                    <th>Data / Hora</th>
                    <th>Veículo</th>
                    <th>Motorista</th>
                    <th>Valor Total</th>
                    <th>Status Pagamento</th>
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
<div class="control-group" id="linha_<?php echo $this->getName(); ?>">
    <label for="<?php echo $this->getName(); ?>" class="control-label"><?php echo $this->getLabel(); ?></label>
    <div class="controls">
        <div class="input-append">
            <input type="text" name="show_<?php echo $this->getName(); ?>" id="show_<?php echo $this->getName(); ?>" disabled class="input-xxlarge" value="<?php echo $txt; ?>" />
            <div class="add-on">
                <a href="#" class="link_<?php echo $this->getName(); ?>">
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