$(document).ready(
    function() {
        $(".link_lotacao").click(
            function(event) {
                event.preventDefault();
                janelaLotacao();
            }
        );
        $(".link_atraso").click(
            function(event) {
                event.preventDefault();
                $("#formulario_atraso").submit();
            }
        );
        $(".link_chamado").click(
            function(event) {
                event.preventDefault();
                $("#formulario_chamado").submit();
            }
        );
        $("#janela_selecao_lotacao").css({ "width": "800px", "margin-left": "-400px"}).modal("hide");
<?php
$tb = new TbFuncionario();
$funcionario = $tb->getPorPessoaFisica($this->view->pf);
if ($funcionario) {
$lotacaos = $funcionario->pegaLotacaoAtiva();
if ($lotacaos && !$funcionario->pegaLotacaoAtual()) {
?>
    janelaLotacao();
<?php }} ?>
    }
);

function janelaLotacao() {
    $("#janela_selecao_lotacao").modal("show");    
}