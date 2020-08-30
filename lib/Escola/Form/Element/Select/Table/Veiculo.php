<?php
class Escola_Form_Element_Select_Table_Veiculo extends Escola_Form_Element_Select_Table {
    
    protected $hab_adicionar = true;
    
    public function get_hab_adicionar() {
        return $this->hab_adicionar;
    }
    
    public function set_hab_adicionar($hab_adicionar) {
        $this->hab_adicionar = $hab_adicionar;
    }
    
    public function init() {
        parent::init();
        $this->setPkName("id_veiculo");
        $this->setModel("TbVeiculo");
    }
	
	public function render(Zend_View_Interface $view = null) {
        $veiculo = TbVeiculo::pegaPorId($this->getValue());
        $txt = "";
        if ($veiculo) {
            $txt = $veiculo->toString();
        }
        ob_start();
?>
<script type="text/javascript">
var veiculos = [];
$(document).ready(function() {
    $(".link_<?php echo $this->getName(); ?>").click(function(event) {
        event.preventDefault();
        $("#janela_<?php echo $this->getName(); ?>").modal("show");
    });
    $("#janela_<?php echo $this->getName(); ?>").css({ "width": "900px", "margin-left": "-450px" }).modal("hide");
    $("#janela_<?php echo $this->getName(); ?>").on("show", function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza();
    });
    $("#janela_<?php echo $this->getName(); ?>").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            <?php echo $this->getName(); ?>_atualiza();
        }
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_procurar").click(function() {
        <?php echo $this->getName(); ?>_atualiza();
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_limpar_filtro").click(function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza();
    });
<?php if ($this->hab_adicionar) { ?>
    $("#janela_add_<?php echo $this->getName(); ?>").css({ "width": "900px", "margin-left": "-450px" }).modal("hide");
    $("#janela_add_<?php echo $this->getName(); ?>").on("show", function() {
        $("#janela_add_<?php echo $this->getName(); ?> .alert").hide();
    });
    $("#janela_add_<?php echo $this->getName(); ?>").on("shown", function() {
        $("#<?php echo $this->getName(); ?>_add_nome").focus();
    });
    $("#janela_add_<?php echo $this->getName(); ?> .field").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            <?php echo $this->getName(); ?>_salvar_pf();
        }
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_add").click(function() {
        $("#janela_<?php echo $this->getName(); ?>").modal("hide");
        $("#janela_chassi_<?php echo $this->getName(); ?>").modal("show");
    });
    $("#janela_add_<?php echo $this->getName(); ?> #btn_salvar").click(function() {
        <?php echo $this->getName(); ?>_salvar_veiculo();
    });
    $("#janela_chassi_<?php echo $this->getName(); ?>").css({ "width": "700px", "margin-left": "-350px" }).modal("hide");
    $("#janela_chassi_<?php echo $this->getName(); ?>").on("show", function() {
        $("#janela_chassi_<?php echo $this->getName(); ?> .field").val("");
        $("#janela_chassi_<?php echo $this->getName(); ?> .alert").hide();
    });
    $("#janela_chassi_<?php echo $this->getName(); ?>").on("shown", function() {
        $("#<?php echo $this->getName(); ?>_add_chassi").focus();
    });
    $("#janela_chassi_<?php echo $this->getName(); ?> .field").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            <?php echo $this->getName(); ?>_veiculo_procurar();
        }
    });
    $("#janela_chassi_<?php echo $this->getName(); ?> #btn_procurar").click(function() {
        <?php echo $this->getName(); ?>_veiculo_procurar();
    });
<?php } ?>
})

function <?php echo $this->getName(); ?>_atualiza() {
    $("#<?php echo $this->getName(); ?>_resposta .corpo_destino tr, .<?php echo $this->getName(); ?>_paginacao").remove();
    $("#<?php echo $this->getName(); ?>_resposta").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/veiculo/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_id_veiculo_tipo": $("#<?php echo $this->getName(); ?>_id_veiculo_tipo").val(),
                   "filtro_placa": $("#<?php echo $this->getName(); ?>_placa").val(),
                   "filtro_chassi": $("#<?php echo $this->getName(); ?>_chassi").val(),
                   "filtro_id_fabricante": $("#<?php echo $this->getName(); ?>_id_fabricante").val(),
                   "pagina_atual": $("#<?php echo $this->getName(); ?>_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            pjs = [];
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    veiculos[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.placa + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.veiculo_tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.fabricante + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.ano_modelo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.combustivel + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.uf + '</a></td>').appendTo(tr);
                    tr.appendTo($("#<?php echo $this->getName(); ?>_resposta .corpo_destino"));
                }
                $(".link_destino").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_<?php echo $this->getName(); ?>").val(veiculos[$(this).attr("id")].veiculo);
                        $("#<?php echo $this->getName(); ?>").val(veiculos[$(this).attr("id")].id);
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
    <?php echo $this->getName(); ?>_atualiza();
}
<?php if ($this->hab_adicionar) { ?>
function <?php echo $this->getName(); ?>_salvar_veiculo() {
    $("#janela_add_<?php echo $this->getName(); ?> .alert").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/veiculo/salvar/format/json/",
        "type" : "POST",
        "data" : { "chassi": $("#janela_add_<?php echo $this->getName(); ?> #<?php echo $this->getName(); ?>_add_chassi").val(),
                   "placa": $("#janela_add_<?php echo $this->getName(); ?> #<?php echo $this->getName(); ?>_add_placa").val(),
                   "id_veiculo_tipo": $("#<?php echo $this->getName(); ?>_add_id_veiculo_tipo").val(),
                   "id_veiculo_categoria": $("#<?php echo $this->getName(); ?>_add_id_veiculo_categoria").val(),
                   "id_uf": $("#<?php echo $this->getName(); ?>_add_id_uf").val(),
                   "id_municipio": $("#<?php echo $this->getName(); ?>_add_id_municipio").val(),
                   "id_combustivel": $("#<?php echo $this->getName(); ?>_add_id_combustivel").val(),
                   "id_cor": $("#<?php echo $this->getName(); ?>_add_id_cor").val(),
                   "id_fabricante": $("#<?php echo $this->getName(); ?>_add_id_fabricante").val(),
                   "modelo": $("#<?php echo $this->getName(); ?>_add_modelo").val(),
                   "ano_fabricacao": $("#<?php echo $this->getName(); ?>_add_ano_fabricacao").val(),
                   "ano_modelo": $("#<?php echo $this->getName(); ?>_add_ano_modelo").val(),
                   "data_aquisicao": $("#<?php echo $this->getName(); ?>_add_data_aquisicao").val(),
                   "renavan": $("#<?php echo $this->getName(); ?>_add_renavan").val(),
                   "dut": $("#<?php echo $this->getName(); ?>_add_dut").val(),
                   "tara": $("#<?php echo $this->getName(); ?>_add_tara").val(),
                   "lotacao": $("#<?php echo $this->getName(); ?>_add_lotacao").val(),
                   "isento": $("#<?php echo $this->getName(); ?>_add_isento").val()},
        "success" : function(result) {
            if (result.erro && result.erro.length) {
                $("#janela_add_<?php echo $this->getName(); ?> .mensagem_erro").html(result.erro);
                $("#janela_add_<?php echo $this->getName(); ?> .alert").show();
            }
            if (result.id > 0) {
                $("#show_<?php echo $this->getName(); ?>").val(result.descricao);
                $("#<?php echo $this->getName(); ?>").val(result.id);
                $("#janela_add_<?php echo $this->getName(); ?>").modal("hide");
            }
        }
    });
}

function <?php echo $this->getName(); ?>_veiculo_procurar() {
    $("#janela_chassi_<?php echo $this->getName(); ?> .alert").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/veiculo/dados/format/json/",
        "type" : "POST",
        "data" : { "filtro_chassi": $("#<?php echo $this->getName(); ?>_add_chassi").val(), "filtro_placa": $("#<?php echo $this->getName(); ?>_add_placa").val() },
        "success" : function(result) {
            if (result.erro && result.erro.length) {
                $("#janela_chassi_<?php echo $this->getName(); ?> .mensagem_erro").html(result.erro);
                $("#janela_chassi_<?php echo $this->getName(); ?> .alert").show();
            }
            if (result.obj) {
                for (valor in result.obj) {
                    $("#janela_add_<?php echo $this->getName(); ?> #<?php echo $this->getName(); ?>_add_" + valor).val(result.obj[valor]);
                }
                $("#janela_chassi_<?php echo $this->getName(); ?>").modal("hide");
                $("#janela_add_<?php echo $this->getName(); ?>").modal("show");
            }
        }
    });    
}
<?php } ?>
</script>
<input type="hidden" name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" value="<?php echo $this->getValue(); ?>" />
<input type="hidden" name="<?php echo $this->getName(); ?>_pagina" id="<?php echo $this->getName(); ?>_pagina" />
<div id="janela_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Veículos</h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <fieldset>
<?php
$tb = new TbVeiculoTipo();
$vts = $tb->listar();
if ($vts) {
?>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_id_veiculo_tipo" class="control-label">Tipo de Veículo:</label>
                    <div class="controls">
                        <select name="<?php echo $this->getName(); ?>_id_veiculo_tipo" id="<?php echo $this->getName(); ?>_id_veiculo_tipo" class="filtro">
                            <option value="">==> SELECIONE <==</option>
<?php foreach ($vts as $vt) { ?>
                            <option value="<?php echo $vt->getId(); ?>"><?php echo $vt->toString(); ?></option>
<?php } ?>
                        </select>
                    </div>
                </div>
<?php } ?>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_placa" class="control-label">Placa:</label>
                    <div class="controls">
                        <input type="text" name="<?php echo $this->getName(); ?>_placa" id="<?php echo $this->getName(); ?>_placa" value="" size="60" class="filtro span2 placa" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_chassi" class="control-label">Chassi:</label>
                    <div class="controls">
                        <input type="text" name="<?php echo $this->getName(); ?>_chassi" id="<?php echo $this->getName(); ?>_chassi" value="" size="60" class="filtro span3" />
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="<?php echo $this->getName(); ?>_resposta" class="table table-striped table-bordered">
            <thead class="head_destino">
                <tr>
                    <th>ID</th>
                    <th>Placa</th>
                    <th>Tipo</th>
                    <th>Fabricante</th>
                    <th>Ano Modelo</th>
                    <th>Combustível</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody class="corpo_destino"></tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
<?php if ($this->hab_adicionar) { ?>
        <input type="button" value="Adicionar" id="btn_add" class="btn btn-danger" />
<?php } ?>
        <input type="button" value="Limpar Filtro" id="btn_limpar_filtro" class="btn" />
        <input type="button" value="Procurar" id="btn_procurar" class="btn btn-primary" />
    </div>
</div>
<?php if ($this->hab_adicionar) { ?>
<div id="janela_chassi_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Veículo - Informe o Número do Chassi e(ou) Placa</h3>
    </div>
    <div class="modal-body">
        <div class="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <span class="mensagem_erro"></span>
        </div>
        <fieldset>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_chassi" class="control-label">Chassi:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_chassi" id="<?php echo $this->getName(); ?>_add_chassi" class="field span6" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_placa" class="control-label">Placa:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_placa" id="<?php echo $this->getName(); ?>_add_placa" class="field span4 placa" />
                </div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Procurar" id="btn_procurar" class="btn btn-primary" />
    </div>
</div>

<div id="janela_add_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Veículo - Adicionar</h3>
    </div>
    <div class="modal-body">
        <input type="hidden" name="<?php echo $this->getName(); ?>_add_id_veiculo" id="<?php echo $this->getName(); ?>_add_id_veiculo" />
        <div class="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <span class="mensagem_erro"></span>
        </div>
        <fieldset>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_chassi" class="control-label">Chassi:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_chassi" id="<?php echo $this->getName(); ?>_add_chassi" maxlength="30" value="" class="span3" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_placa" class="control-label">Placa:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_placa" id="<?php echo $this->getName(); ?>_add_placa" maxlength="10" value="" class="span2 placa" />
                </div>
            </div>
<?php	
$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_VeiculoTipo($this->getName() . "_add_id_veiculo_tipo");
$ctrl->setPkName("id_veiculo_tipo");
$ctrl->setModel("TbVeiculoTipo");
$ctrl->setLabel("Tipo: ");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_VeiculoCategoria($this->getName() . "_add_id_veiculo_categoria");
$ctrl->setPkName("id_veiculo_categoria");
$ctrl->setModel("TbVeiculoCategoria");
$ctrl->setLabel("Categoria: ");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table($this->getName() . "_add_id_uf");
$ctrl->setPkName("id_uf");
$ctrl->setModel("TbUf");
$ctrl->setLabel("Estado: ");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_Municipio($this->getName() . "_add_id_municipio");
$ctrl->setPkName("id_municipio");
$ctrl->setModel("TbMunicipio");
$ctrl->setLabel("Município: ");
$ctrl->set_id_uf($this->getName() . "_add_id_uf");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_Combustivel($this->getName() . "_add_id_combustivel");
$ctrl->setPkName("id_combustivel");
$ctrl->setModel("TbCombustivel");
$ctrl->setLabel("Combustível: ");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_Cor($this->getName() . "_add_id_cor");
$ctrl->setPkName("id_cor");
$ctrl->setModel("TbCor");
$ctrl->setLabel("Cor: ");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_Fabricante($this->getName() . "_add_id_fabricante");
$ctrl->setPkName("id_fabricante");
$ctrl->setModel("TbFabricante");
$ctrl->setLabel("Fabricante: ");
echo $ctrl->render($view);
?>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_modelo" class="control-label">Modelo:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_modelo" id="<?php echo $this->getName(); ?>_add_modelo" maxlength="100" value="" class="span7" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_ano_fabricacao" class="control-label">Ano Fabricação:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_ano_fabricacao" id="<?php echo $this->getName(); ?>_add_ano_fabricacao" maxlength="10" value="" class="span2" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_ano_modelo" class="control-label">Ano Modelo:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_ano_modelo" id="<?php echo $this->getName(); ?>_add_ano_modelo" maxlength="10" value="" class="span2" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_data_aquisicao" class="control-label">Data Aquisição:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_data_aquisicao" id="<?php echo $this->getName(); ?>_add_data_aquisicao" maxlength="10" value="" class="span2 data" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_renavan" class="control-label">Renavan:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_renavan" id="<?php echo $this->getName(); ?>_add_renavan" maxlength="30" value="" class="span3" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_dut" class="control-label">DUT:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_dut" id="<?php echo $this->getName(); ?>_add_dut" maxlength="30" value="" class="span3" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_tara" class="control-label">Tara (Kg):</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_tara" id="<?php echo $this->getName(); ?>_add_tara" maxlength="30" value="" class="span2 moeda" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_lotacao" class="control-label">Lotação:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_lotacao" id="<?php echo $this->getName(); ?>_add_lotacao" maxlength="30" value="" class="span2" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_isento" class="control-label">Isento:</label>
                <div class="controls">
                    <select name="<?php echo $this->getName(); ?>_add_isento" id="<?php echo $this->getName(); ?>_add_isento">
                        <option value="">==> SELECIONE <==</option>
                        <option value="S">SIM</option>
                        <option value="N">NÃO</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Salvar" id="btn_salvar" class="btn btn-primary" />
    </div>
</div>
<?php } ?>
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