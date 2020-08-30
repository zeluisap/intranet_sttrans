<?php

class Escola_Form_Element_Select_Table_Crud_Bairro extends Escola_Form_Element_Select_Table_Crud {

    protected $_id_municipio = "";

    public function init() {
        parent::init();
        $this->setPkName("id_bairro");
        $this->setModel("TbBairro");
    }

    public function set_id_municipio($vinculo) {
        $this->_id_municipio = $vinculo;
    }

    public function pega_id_municipio() {
        return $this->_id_municipio;
    }

    public function janela_modal(Zend_View_Interface $view = null) {
        ob_start();
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#janela_crud_<?php echo $this->getName(); ?> #bt_crud_submit").click(function () {
                    ajax = $.ajax({
                        "url": "<?php echo $view->baseUrl(); ?>/bairro/salvar/format/json/",
                        "type": "POST",
                        "data": {"descricao": $("#janela_crud_<?php echo $this->getName(); ?> #descricao").val(), "id_municipio": $("#<?php echo $this->_id_municipio; ?>").val()},
                        "success": function (obj_view) {
                            if (obj_view.result) {
                                if (obj_view.result.mensagem) {
                                    $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud .mensagem_erro").html(obj_view.result.mensagem);
                                    $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud").show();
                                }
                                if (obj_view.result.id) {
                                    reloadCrud<?php echo $this->getName(); ?>(obj_view.result.id);
                                    $("#janela_crud_<?php echo $this->getName(); ?>").modal("hide");
                                }
                            }
                        }
                    });
                    return false;
                });
                $("#descricao").keyup(function () {
                    $("#janela_crud_<?php echo $this->getName(); ?> #msg_erro_crud").hide();
                });
                $("#<?php echo $this->_id_municipio; ?>").change(function () {
                    $("#linha_<?php echo $this->getName(); ?>").hide();
                    if ($(this).val().length) {
                        reloadCrud<?php echo $this->getName(); ?>(<?php echo $this->getName(); ?>_id_default);
                        $("#linha_<?php echo $this->getName(); ?>").show();
                    }
                }).change();
                $("#janela_crud_<?php echo $this->getName(); ?>").css({"width": "600px", "margin-left": "-300px"});
            });
            function reloadCrud<?php echo $this->getName(); ?>(default_id) {
                var ctrl = $("#<?php echo $this->getName(); ?>");
                var id_municipio = $("#<?php echo $this->_id_municipio; ?>").val();
                ctrl.children().remove();
                $("<option value=''>==> SELECIONE <==</option>").appendTo(ctrl);
                if ($("#<?php echo $this->_id_municipio; ?>").val().length) {
                    ajax = $.ajax({
                        "url": "<?php echo $view->baseUrl(); ?>/bairro/listar/format/json/",
                        "type": "POST",
                        "data": {"id_municipio": id_municipio},
                        "success": function (obj_view) {
                            if (obj_view.result) {
                                for (var x = 0; x < obj_view.result.length; x++) {
                                    var obj = obj_view.result[x];
                                    var selected = "";
                                    if (obj.id == default_id) {
                                        selected = " selected ";
                                    }
                                    $("<option value='" + obj.id + "' " + selected + ">" + obj.descricao + "</option>").appendTo(ctrl);
                                }
                            }
                            ctrl.change();
                        }
                    });
                } else {
                    ctrl.change();
                }
            }
        </script>
        <div id="janela_crud_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Adicionando Registro - Bairro</h3>
            </div>
            <div class="modal-body">
                <div class="alert" id="msg_erro_crud">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div class="mensagem_erro"></div>
                </div>
                <div class="control-group">
                    <div class="control-label">Descrição:</div>
                    <div class="controls">
                        <input type="text" name="descricao" id="descricao" maxlength="50" class="crud_cadastro" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
                <button class="btn btn-primary" id="bt_crud_submit">Salvar</button>
            </div>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

}