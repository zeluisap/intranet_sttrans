<?php

class Escola_Relatorio_Servico_AUTSF extends Escola_Relatorio_Servico_Autorizacao
{

    public function getFilename()
    {
        return "autorizacao_trafegar_sem_faixa";
    }

    public function getTextoCentro()
    {
        $transporte = $this->registro->pegaTransporte();
        if (!$transporte) {
            return false;
        }
        $tg = $transporte->findParentRow("TbTransporteGrupo");
        $txt_data_validade = Escola_Util::formatData($this->registro->data_validade);
        ?>
        <div class="paragrafo">A <?php echo $this->pj->sigla; ?> - <?php echo $this->pj->mostrar_nome(); ?>, autoriza o detentor da Permissão de Serviço de <?php echo $tg->toString(); ?>, trafegar sem a faixa de identificação lateral, conforme disposto no Art. 8°, Inc. V, da Lei n°.: 1.008/2013, até o dia <?= $txt_data_validade ?>.</div>
<?php
    }
}
