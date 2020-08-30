<?php

class Escola_Relatorio_Default_Taxi_TdMot extends Escola_Relatorio
{

    protected $transporte;

    public function set_transporte(Transporte $transporte)
    {
        $this->transporte = $transporte;
        $this->setFilename("termo_declaracao_motorista_" . $this->transporte->codigo);
    }

    public function get_transporte()
    {
        return $this->transporte;
    }

    public function __construct()
    {
        parent::__construct("termo_declaracao_motorista");
        $this->SetMargins(20, 20);
        $this->SetAutoPageBreak(20);
    }

    public function header()
    { }

    public function Footer()
    { }

    public function toPDF()
    {
        if ($this->transporte->getId()) {
            ob_start();
            $this->AddPage();
            $this->css();

            $pj = false;
            $tb = new TbSistema();
            $sistema = $tb->pegaSistema();
            if ($sistema) {
                if ($sistema) {
                    $pj = $sistema->findParentRow("TbPessoaJuridica");
                }
            }

            $proprietario = $this->transporte->pegaProprietario();
            if ($proprietario) {
                $proprietario_pessoa = $proprietario->findParentRow("TbPessoa");
                $concessao = $this->transporte->findParentrow("TbConcessao");
                if ($concessao) {
                    ?>
                    <div></div>
                    <div class="centro titulo_servico"><u>TERMO DE DECLARAÇÃO</u></div>
                    <div></div>
                    <?php
                                        $motoristas = $this->transporte->pegaMotoristas();
                                        if ($motoristas) {
                                            ?>
                        <div class="paragrafo normal">Eu, <?php echo $proprietario_pessoa->mostrar_nome(); ?>, detentor da permissão <?php echo $this->transporte->mostrar_codigo(); ?> conforme Processo No.: <?php echo $concessao->processo_numero; ?> declaro a <?php echo $pj->mostrar_nome(); ?>, que prestam o serviço de transporte de passageiro em veículo de aluguel (Táxi) conjuntamente a este permissionário o(s) condutor(es) auxiliar(es) abaixo relacionado(s):</div>
                        <div></div>
                        <div></div>
                        <div class="direita"><?php echo $this->mostrar_data_completa($pj->findParentRow("TbPessoa")); ?></div>
                        <div></div>
                        <div class="paragrafo">MOTORISTA<br />
                            <?php
                                                    $contador = 0;
                                                    foreach ($motoristas as $motorista) {
                                                        $pm = $motorista->findParentRow("TbPessoaMotorista");
                                                        $pf = $pm->findParentRow("TbPessoaFisica");
                                                        $contador++;
                                                        ?>
                                <?php echo $contador; ?> - <?php echo $pf->nome; ?> - <?php echo $motorista->matricula; ?><br />
                            <?php } ?>
                        <?php
                                            } else {
                                                ?>
                            <div class="paragrafo normal">Eu, <span class="negrito"><?php echo $proprietario_pessoa->mostrar_nome(); ?></span>, detentor da permissão <?php echo $this->transporte->mostrar_codigo(); ?> conforme Processo No.: <?php echo $concessao->processo_numero; ?> declaro a <?php echo $pj->mostrar_nome(); ?> que, <span class="negrito">não possuo motorista(s) auxiliar(es)</span>.</div>
                            <div class="paragrafo normal">Por ser verdade dato e assino a presente declaração para que a mesma produza seus devidos efeitos na forma da lei.</div>
                            <div></div>
                            <div></div>
                            <div class="direita"><?php echo $this->mostrar_data_completa($pj->findParentRow("TbPessoa")); ?></div>
                            <div></div>
                    <?php
                                        }
                                    }
                                    ?>
                    <div></div>
                    <div class="centro">__________________________________________<br />
                        <?php echo $proprietario_pessoa->mostrar_nome(); ?><br />
                        C.P.F. <?php echo $proprietario_pessoa->mostrar_documento(); ?></div>
                    <?php
                                    if ($motoristas) {
                                        $motoristas->rewind();
                                        foreach ($motoristas as $motorista) {
                                            $pm = $motorista->findParentRow("TbPessoaMotorista");
                                            $pf = $pm->findParentRow("TbPessoaFisica");
                                            ?>
                            <div></div>
                            <div class="centro">__________________________________________<br />
                                <?php echo $pf->mostrar_nome(); ?><br />
                                C.P.F. <?php echo $pf->mostrar_documento(); ?></div>
                        <?php } ?>
                        <div></div>
    <?php
                    }
                }

                $html = ob_get_contents();
                ob_end_clean();
                $this->writeHTML($html, true, false, true, false, '');
                $this->lastPage();
                $this->download();
            }
            return false;
        }
    }
