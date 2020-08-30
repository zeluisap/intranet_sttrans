<?php

class Escola_Relatorio_Servico_TAUT extends Escola_Relatorio_Servico
{

    public function __construct()
    {
        parent::__construct();
        $this->setFilename($this->getFilename());
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $this->SetTopMargin(40);
        $this->SetAutoPageBreak(true, 11);
    }

    public function getFilename()
    {
        return "termo_de_autorizacao";
    }

    public function header()
    {
        $this->Ln(8);
        $this->cabecalho();
    }

    public function getConcessaoAnos()
    {

        if (!(isset($this->concessao) && $this->concessao)) {
            throw new Escola_Exception("Não foi possível identificar o registro de concessão!");
        }

        try {
            $concessao_data = new DateTime($this->concessao->concessao_data);
            if (!$concessao_data) {
                throw new Escola_Exception("Não foi possível identificar a data da concessão!!");
            }

            $agora = new DateTime();
            $diff = $agora->diff($concessao_data);

            return $diff->y;
        } catch (Exception $ex) {
            throw new Escola_Exception("Não foi possível identificar a data da concessão!");
        }
    }

    public function validarEmitir()
    {
        if (!(isset($this->pj) && $this->pj)) {
            return ["Nenhuma Empresa Identificada!"];
        }

        if (!(isset($this->registro) && $this->registro)) {
            return ["Nenhum Serviço Identificado!"];
        }

        if (!(isset($this->transporte) && $this->transporte)) {
            return ["Nenhum Transporte Identificado!"];
        }

        if (!(isset($this->proprietario_pessoa_pf) && $this->proprietario_pessoa_pf)) {
            return ["Proprietário não Identificado!"];
        }

        try {
            $concessao_anos = $this->getConcessaoAnos();
            if ($concessao_anos < 2) {
                return ["Não é permitido emitir este documentos para Concessões de menos de 02 (dois) anos."];
            }
        } catch (Exception $ex) {
            return [$ex->getMessage()];
        }
    }

    public function toPDF()
    {

        $txt_transporte_codigo = $this->transporte->codigo;

        $txt_codigo = $this->registro->codigo;
        $txt_ano = $this->registro->ano_referencia;

        $txt_sttrans = $this->pj->razao_social;
        $txt_sttrans_cnpj = Escola_Util::formatCnpj($this->pj->cnpj);

        $txt_autorizatario = $this->proprietario_pessoa_pf->nome;
        $txt_rg = $this->proprietario_pessoa_pf->mostrar_identidade();
        $txt_cpf = Escola_Util::formatCpf($this->proprietario_pessoa_pf->cpf);
        $txt_endereco = $this->proprietario_pessoa->mostrar_endereco();

        $concessao = $this->transporte->get_concessao();
        if (!$concessao) {
            throw new Escola_Exception("Concessão do Transporte não identificada!");
        }

        $validade = $concessao->getValidade();
        if (!$validade) {
            throw new Escola_Exception("Não foi possível detectar a validade da concessão!");
        }

        $txt_decreto = $concessao->decreto;

        $anos = $this->getConcessaoAnos();

        $txt_validade = str_pad($anos, 2, "0", STR_PAD_LEFT) . " anos";

        $txt_data_completa = $this->mostrar_data_completa($this->pf);

        $this->AddPage();
        ob_start();
        $this->css();
        ?>
        <div class="termo-de-autorizacao">
            <div class="font_16pt negrito centro">CONTRATO DE PERMISSÃO</div>
            <div class="font_16pt negrito centro">N°. <?= $txt_codigo ?>/<?= $txt_ano ?></div>
            <div class="font_16pt negrito justificado">CONTRATO DE DELEGAÇÃO PARA A EXECUÇÃO DO SERVIÇO PÚBLICO DE
                TRANSPORTE INDIVIDUAL, POR TÁXI, QUE ENTRE SI FAZEM A
                PREFEITURA MUNICIPAL DE SANTANA POR INTERMÉDIO DA
                <?= $txt_sttrans ?> E
                <?= $txt_autorizatario ?>.
            </div>
            <div class="justificado linha_130">Contrato de Delegação de Permissão, que entre si fazem, de um lado, a PREFEITURA
                MUNICIPAL DE SANTANA (AP), por intermédio da <?= $txt_sttrans ?>, inscrita no CNPJ sob o número <?= $txt_sttrans_cnpj ?>, denominada
                PERMITENTE e, de outro, <?= $txt_autorizatario ?>, doravante denominado
                (a) AUTORIZATÁRIO (A), portador (a) da Cédula de Identidade n°. <?= $txt_rg ?>, CPF
                n°. <?= $txt_cpf ?>, residente na <?= $txt_endereco ?>, pelas cláusulas e condições a seguir apresentadas.</div>

            <div class="font_16pt negrito centro">CLÁUSULA PRIMEIRA – DO OBJETO</div>

            <div class="justificado linha_130">1.1 O objeto deste contrato é a expedição de TERMO DE AUTORIZAÇÃO/PERMISSÃO
                para a execução do Serviço Público de Transporte Individual, por táxi, no município de
                Santana.<br class="linha_70">

                <br>1.2 A PERMITENTE reconhece para todos os fins legais que o AUTORIZATÁRIO (A)
                possui <?= $txt_validade ?> atuando como autorizatário na exploração do transporte público
                por meio de táxi, por ocasião da outorga do Decreto n°. <?= $txt_decreto ?>, (<?= $txt_transporte_codigo ?>).</div>

            <div class="font_16pt negrito centro">CLÁUSULA SEGUNDA – DA LEGISLAÇÃO APLICÁVEL</div>

            <div class="justificado linha_130">2.1 Aplicam-se a este contrato as Leis Federais no. 8.666/1993, 8.987/1995 e 9.503/1997 bem
                como a Lei 1.008/2013 do Município de Santana e ainda o Regulamento do Serviço Público
                de Transporte Individual, por táxi, do Município de Santana.<br class="linha_70">

                <br>2.2 Fazem parte integrante deste contrato, independentemente de tanscrição:</div>

            <div class="font_16pt negrito centro">CLÁUSULA TERCEIRA – DOS PRAZOS</div>

            <div class="justificado linha_130">3.1 O AUTORIZATÁRIO (A) poderá executar o serviço previsto na cláusula 1a (primeira)
                deste Contrato pelo prazo de 20 (vinte) anos, limitadas, no entanto, às condições pessoais de
                capacidade do autorizatário e ao cumprimento dos requisitos legais vigentes e suas alterações
                no curso do tempo.</div>

            <div class="font_16pt negrito centro">CLÁUSULA QUARTA – DA EXECUÇÃO DO SERVIÇO</div>

            <div class="justificado linha_130">4.1 É indispensável que na prestação de serviço sejam rigorosamente observados os
                requisitos de pontualidade, regularidade, continuidade, eficiência, segurança, atualidade,
                generalidade, moralidade, higiene, cortesia e pessoalidade.</div>

            <div class="font_16pt negrito centro">CLÁUSULA QUINTA – DAS TARIFAS COBRADAS DOS USUÁRIOS</div>

            <div class="justificado linha_130">5.1 As tarifas a serem cobradas dos usuários do serviço de transporte individual, por táxi,
                serão fixadas pela Prefeitura Municipal de Santana, por intermédio da Superintendência de
                Transporte e Trânsito de Santana em função da justa remuneração dos investimentos e do
                custo operacional.</div>

            <div class="font_16pt negrito centro">CLÁUSULA SEXTA – DIREITOS E DEVERES DOS USUÁRIOS</div>

            <div class="justificado linha_130">6.1 Os usuários poderão, pessoalmente, ou através de Associação regularmente constituída,
                apresentar reclamações ou sugestões à Prefeitura Municipal referente à prestação de serviços
                objeto do presente contrato.<br class="linha_70">
                <br>6.1.1 As reclamações serão apuradas em conformidade com o regulamento e o Código de
                Trânsito Brasileiro.<br class="linha_70">
                <br>6.1.2 São atribuídos aos usuários todos os direitos e deveres contidos na Lei no. 1.008/2013 e
                no Código Civil Brasileiro, desde que pertinentes ao serviço prestado, bem como aqueles
                previstos no Regulamento e na legislação aplicável, inclusive as portarias da STTRANS.</div>

            <div class="font_16pt negrito centro">CLÁUSULA SÉTIMA – DA FISCALIZAÇÃO E DAS PENALIDADES</div>
            <div class="justificado linha_130">7.1 O AUTORIZATÁRIO submeterá seu veículo a vistorias periódicas, na forma
                estabelecida no Regulamento próprio e atenderá às convocações extraordinárias para vistoria,
                sempre que se fizer necessário, a critério da STTRANS.<br class="linha_70">

                <br>7.2 A PERMITENTE poderá fiscalizar o veículo e a documentação do AUTORIZATÁRIO
                em qualquer local e hora onde o mesmo se encontre.<br class="linha_70">

                <br>7.3 O AUTORIZATÁRIO cumprirá, rigorosamente, as normas de conduta estipulada no
                Regulamento próprio, no Código de Trânsito Brasileiro e em legislações complementares,
                inclusive Portarias da STTRANS, sujeitando-se, em caso de infração, às punições nelas
                previstas nas respectivas normas.<br class="linha_70">

                <br>7.4 O AUTORIZATÁRIO que for preso em flagrante delito ou por ordem escrita e
                fundamentada de autoridade judiciária competente, terá sua permissão suspensa
                automaticamente, enquanto perdurar a prisão.<br class="linha_70">

                <br>7.5 O AUTORIZATÁRIO que for denunciado pelo Ministério Público pela prática de
                infração penal, poderá, a critério da Superintendência de Transportes e Trânsito de Santana,
                ter sua permissão suspensa durante toda a tramitação do processo criminal.<br class="linha_70">

                <br>7.6 A sentença criminal condenatória, transitada em julgado, implicará na imediata cassação
                da permissão.<br class="linha_70">

                <br>7.7 A sentença criminal absolutória, transitada em julgado, terá os mesmos efeitos
                administrativamente.<br class="linha_70">

                <br>7.8 O AUTORIZATÁRIO que tiver sua carteira de habilitação cassada ou apreendida terá
                sua permissão suspensa até que toda tramitação seja feita e sua carteira devolvida.<br class="linha_70">

                <br>7.9 O AUTORIZATÁRIO, que na execução do serviço, deixar de atender os requisitos
                contidos na Cláusula Quinta, deste contrato e os deveres contidos na legislação municipal,
                poderá, a juízo da Superintendência de Transportes e Trânsito de Santana, ter sua permissão
                cassada.<br class="linha_70">

                <br>7.10 O AUTORIZATÁRIO que não comparecer a 02 (duas) vistorias anuais consecutivas,
                terá sua permissão imediatamente revogada.</div>

            <div class="font_16pt negrito centro">CLÁUSULA OITAVA – DO FORO</div>
            <div class="justificado linha_130">10.1 Fica eleito o foro da Comarca de Santana/AP para dirimir as controvérsias oriundas
                deste Contrato, desde que esgotadas todas as vias administrativas necessárias à composição
                do litígio.<br class="linha_70">

                <br>Assim, por estarem justas e contratadas, as partes assinam e rubricam todas as folhas das 03
                (três) vias deste Contrato, de igual forma e teor para um só efeito, na presença das
                testemunhas abaixo.</div>

            <div class="linha_130 centro"><?= $txt_data_completa ?></div>

            <div class="centro">___________________________________________________<br>
                PERMITENTE
            </div>

            <div class="centro">___________________________________________________<br>
                AUTORIZATÁRIO
            </div>

            <div>Testemunhas:</div>
            <div></div>
            <table>
                <tr>
                    <td class="linha_130 fonte_12pt">________________________________</td>
                    <td class="linha_130 fonte_12pt">________________________________</td>
                </tr>
                <tr>
                    <td class="linha_130 fonte_12pt">Nome:</td>
                    <td class="linha_130 fonte_12pt">Nome:</td>
                </tr>
                <tr>
                    <td class="linha_130 fonte_12pt">Endereço:</td>
                    <td class="linha_130 fonte_12pt">Endereço:</td>
                </tr>
            </table>
        </div>
    <?php
            $html = ob_get_contents();
            ob_end_clean();
            $this->writeHTML($html, true, false, true, false, '');
            $this->lastPage();
            $this->download();
            // $this->show();
        }

        public function css()
        {
            parent::css();
            ?>
        <style type="text/css">
            div {
                font-size: 12pt;
            }

            .termo-de-autorizacao {
                font-family: 'Times New Roman', Times, serif;
            }

            .linha_70 {
                line-height: 110%;
            }
        </style>
<?php
    }
}
