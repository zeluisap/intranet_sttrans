<?php

class Escola_Relatorio_Servico extends Escola_Relatorio
{

    protected $pj = null;

    public function __construct()
    {
        parent::__construct("relatorio_solicitacao_servico");

        $this->setFilename($this->getFilename());
    }

    public function getFilhos()
    {
        return [];
    }

    public function getLicencaCodigo()
    {
        return "lt"; //código de licença padrão!
    }

    public function getFilhosRecursivo()
    {
        $filhos = $this->getFilhos();
        if (!Escola_Util::isResultado($filhos)) {
            return [];
        }

        $rec_filhos = [];
        foreach ($filhos as $filho) {
            // $class_name = get_class($this) . "_" . $filho;
            $class_name = $filho;
            if (!Zend_Loader_Autoloader::autoload($class_name)) {
                continue;
            }
            $obj = new $class_name();
            $obj->set_registro($this->registro);
            $rec_filhos = array_merge($obj->getFilhosRecursivo(), $rec_filhos);
            $rec_filhos[] = $obj;
        }

        return $rec_filhos;
    }

    public function enabled()
    {
        return true;
    }

    public function getEnabled()
    {
        $filhos = $this->getFilhosRecursivo();

        if (!Escola_Util::isResultado($filhos)) {
            return $this;
        }

        foreach ($filhos as $filho) {
            if ($filho->enabled()) {
                return $filho;
            }
        }

        return $this;
    }

    public function get($field_name)
    {
        if (!isset($this->$field_name)) {
            return null;
        }

        if ($this->$field_name) {
            return null;
        }

        return $this->field_name;
    }

    public function setFilename($filename)
    {
        if (!(isset($this->registro->ano_referencia) && isset($this->registro->codigo))) {
            parent::setFilename($this->getFilename());
            return;
        }
        parent::setFilename($this->getFilename() . "_" . $this->registro->ano_referencia . "_" . Escola_Util::zero($this->registro->codigo, 4));
    }

    public function set_registro($registro)
    {
        if (!$registro) {
            $this->registro = null;
            return;
        }

        $this->registro = $registro;

        $transporte = $registro->pegaTransporte();
        $this->setTransporte($transporte);
    }

    public function setRegistro($registro)
    {
        $this->set_registro($registro);
    }

    public function setTransporte($transporte)
    {

        $this->concessao = null;

        if (!$transporte) {
            $this->transporte = null;
            $this->transporte_veiculo = null;
            $this->transporte_grupo = null;
            $this->setProprietario(null);
            return;
        }

        $this->transporte = $transporte;

        $this->transporte_grupo = $transporte->getTransporteGrupo();
        $proprietario = $transporte->pegaProprietario();
        $this->setProprietario($proprietario);

        $tv = null;
        $obj = $this->registro->pegaReferencia();
        if ($this->registro->veiculo()) {
            $tv = $obj;

            $tp = $this->transporte->pegaProprietario();
            $this->setPessoa($tp, "tp");
        } elseif ($this->registro->pessoa()) {
            $this->setPessoa($obj, "tp");
        }

        if (!$tv) {
            $tv = $transporte->pegaTransporteVeiculoAtivo();
        }

        $this->setTransporteVeiculo($tv);

        $concessao = $this->transporte->get_concessao();
        if ($concessao) {
            $this->concessao = $concessao;
        }
    }

    public function setProprietario($obj)
    {
        $this->setPessoa($obj);
    }

    public function setTransporteVeiculo($tv)
    {
        if (!$tv) {
            $this->transporte_veiculo = null;
            $this->licenca_ativa = null;
            $this->veiculo = null;
            return;
        }

        $veiculo = $tv->findParentRow("TbVeiculo");
        $this->veiculo = $veiculo;
        $this->transporte_veiculo = $tv;
    }

    public function setPessoa($objeto, $prefixo = "proprietario")
    {

        try {
            $obj = $objeto;

            if (!$obj) {
                $this->$prefixo = null;
                return;
            }

            $this->$prefixo = $obj;

            try {
                $this->transporte = $obj->getTransporte();
            } catch (Exception $ex) {
                $this->transporte = null;
            }

            $pessoa = $obj->getPessoa();

            $nome_pessoa = $prefixo . "_pessoa";
            $nome_pessoa_pf = $prefixo . "_pessoa_pf";
            $nome_pessoa_pj = $prefixo . "_pessoa_pj";

            if (!$pessoa) {
                $this->$nome_pessoa = null;
                $this->$nome_pessoa_pf = null;
                $this->$nome_pessoa_pj = null;
                return;
            }

            $ref = $pessoa->pegaPessoaFilho();
            if (!$ref) {
                $this->$nome_pessoa = null;
                $this->$nome_pessoa_pf = null;
                $this->$nome_pessoa_pj = null;
                return;
            }

            if ($pessoa->pf()) {
                $this->$nome_pessoa_pf = $ref;
                $this->$nome_pessoa_pj = null;
            } elseif ($pessoa->pj()) {
                $this->$nome_pessoa_pf = null;
                $this->$nome_pessoa_pj = $ref;
            }

            $this->$nome_pessoa = $pessoa;
        } catch (Exception $ex) {
        }
    }

    public function getTransporte()
    {
        if (!isset($this->transporte)) {
            return null;
        }

        if (!$this->transporte) {
            return null;
        }

        return $this->transporte;
    }

    public function getRegistro()
    {
        return $this->registro;
    }

    public function toPDF()
    {
        return $this->imprimir();
    }

    public function imprimir()
    {
        return null;
    }

    public function validarEmitir()
    {
        $errors = array();

        if (!$this->registro) {
            $errors[] = "Nenhuma Solicitação de Serviço Definida!";
        }

        if (count($errors)) {
            return $errors;
        }
        return false;
    }

    public function showDataExtenso()
    {
        if (!$this->pj) {
            return;
        }
        $pessoa = $this->pj->pega_pessoa();

        $dia = date("d");
        $desc_mes = Escola_Util::pegaMes(date("n"));
        $ano = date("Y");
        $municipio = "Santana";
        $uf = "AP";

        if ($pessoa) {
            $endereco = $pessoa->getEndereco();
            $bairro = $endereco->findParentRow("TbBairro");
            if ($bairro) {
                $mun = $bairro->findParentRow("TbMunicipio");
                if ($mun) {
                    $municipio = $mun->descricao;
                    $obj_uf = $mun->findParentRow("TbUf");
                    if ($obj_uf) {
                        $uf = $obj_uf->sigla;
                    }
                }
            }
        }
?>
        <div class="direita"><?php echo $municipio; ?>-<?php echo $uf; ?>, <?php echo $dia; ?> de <?php echo $desc_mes; ?> de <?php echo $ano; ?>.</div>
    <?php
    }

    public function showValidade()
    {
        if (!isset($this->registro->data_validade)) {
            return "";
        }

        $validade = $this->registro->data_validade;

        if (!Escola_Util::validaData($validade)) {
            return "";
        }

        return Escola_Util::formatData($validade);
    }

    public function showVeiculoLista()
    {
        if (!$this->veiculo) {
            return "";
        }

        $pt_pessoa = $this->proprietario_pessoa;
        $pv_pessoa = $this->veiculo->getProprietario();
        $txt_proprietario_veiculo = "";
        if ($pt_pessoa && $pv_pessoa) {
            if ($pv_pessoa && ($pv_pessoa->getId() != $pt_pessoa->getId())) {
                $txt_proprietario_veiculo = $pv_pessoa->mostrar_documento() . " - " . $pv_pessoa->toString();
            }
        }
    ?>
        <div class="normal">PLACA: <?php echo $this->veiculo->mostrar_placa(); ?><br />
            <?php
            if ($txt_proprietario_veiculo) { ?>
                Proprietário: <strong><?php echo $txt_proprietario_veiculo; ?></strong><br />
            <?php } ?>
            Marca: <?php echo $this->veiculo->findParentRow("TbFabricante")->toString(); ?><br />
            Modelo: <?php echo $this->veiculo->modelo; ?><br />
            Ano de Fabricação: <?php echo $this->veiculo->ano_fabricacao; ?><br />
            Cor: <?php echo $this->veiculo->findParentRow("TbCor"); ?><br />
            Chassi: <?php echo $this->veiculo->chassi; ?></div>
<?php
    }
}
