<?php

class DesenvolvimentoController extends Escola_Controller_Logado
{

    public function indexAction()
    {
        if ($this->_request->isPost()) {
            $action = $this->_request->getPost("operacao");
            if ($action) {
                $this->_redirect($this->_request->getControllerName() . "/{$action}");
                die();
            }
        }
        $button = Escola_Button::getInstance();
        $button->setTitulo("DESENVOLVIMENTO");
        $button->addScript("Salvar", "salvarFormulario('formulario')", "icon-save");
        $button->addFromArray(array(
            "titulo" => "Voltar",
            "controller" => "intranet",
            "action" => "index",
            "img" => "icon-reply",
            "params" => array("id" => 0)
        ));
    }

    public function corrigelicencaAction()
    {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("t" => "transporte"), array("t.id_transporte", "total" => "count(tv.id_transporte_veiculo)"));
        $sql->join(array("tv" => "transporte_veiculo"), "t.id_transporte = tv.id_transporte", array());
        $sql->join(array("tvs" => "transporte_veiculo_status"), "tv.id_transporte_veiculo_status = tvs.id_transporte_veiculo_status", array());
        $sql->where("tvs.chave = 'A'");
        $sql->group("t.id_transporte");
        $sql->having("total = 1");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $transporte = TbTransporte::pegaPorId($obj->id_transporte);
                $tvs = $transporte->pegaTransporteVeiculoAtivos();
                if (count($tvs) == 1) {
                    $tv = $tvs->current();
                    $licenca_ativa = $tv->pegaLicencaAtiva();
                    if (!$licenca_ativa) {
                        $licenca_ativa = $transporte->pega_licenca_trafego_ativa();
                        if ($licenca_ativa) {
                            $veiculo = $tv->findParentRow("TbVeiculo");
                            if ($veiculo) {
                                Zend_Debug::dump($veiculo->toArray());
                            }
                            Zend_Debug::dump($licenca_ativa->toArray());
                            $licenca_ativa->id_transporte = $transporte->getId();
                            $licenca_ativa->tipo = "TV";
                            $licenca_ativa->chave = $tv->getId();
                            $licenca_ativa->save();
                        }
                    }
                }
            }
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
        die();
    }

    public function agrupatransportepjAction()
    {
        $db = Zend_Registry::get("db");
        $sql = "select c.id_pessoa, f.cnpj, f.razao_social, d.id_transporte_grupo, d.descricao, count(a.id_transporte) as total
                from transporte a, transporte_pessoa b, pessoa c, transporte_grupo d, pessoa_tipo e, pessoa_juridica f, transporte_pessoa_tipo g, transporte_pessoa_status h
                where (a.id_transporte = b.id_transporte)
                and (b.id_pessoa = c.id_pessoa)
                and (a.id_transporte_grupo = d.id_transporte_grupo)
                and (c.id_pessoa_tipo = e.id_pessoa_tipo)
                and (c.id_pessoa = f.id_pessoa)
                and (e.chave = 'PJ')
                and (b.id_transporte_pessoa_tipo = g.id_transporte_pessoa_tipo)
                and (b.id_transporte_pessoa_status = h.id_transporte_pessoa_status)
                and (g.chave = 'PR')
                and (h.chave = 'A')

                and (f.razao_social like '%REBELO%')

                group by c.id_pessoa, f.cnpj, f.razao_social, d.id_transporte_grupo, d.descricao
                having count(a.id_transporte) > 1
                order by f.razao_social";
        $stmt = $db->query($sql);
        Escola_Util::agrupaTransporte($stmt);
        $this->addMensagem("PROCESSAMENTO EFETUADO!");
        $this->_redirect($this->_request->getControllerName() . "/index");
        die();
    }

    public function servicosemrelatorioAction()
    {
        $tb = new TbServicoTransporteGrupo();
        $sql = $tb->select();
        $sql->order("id_servico");
        $sql->order("id_transporte_grupo");
        $objs = $tb->fetchAll($sql);
        if ($objs) {
            echo '"chave_servico";"servico";"chave_transporte_grupo";"transporte_grupo"' . PHP_EOL;
            foreach ($objs as $obj) {
                $relatorio = $obj->pegaRelatorioSolicitacao();
                if (!$relatorio) {
                    $servico = $obj->findParentRow("TbServico");
                    $tg = $obj->findParentRow("TbTransporteGrupo");
                    $item = array();
                    $item["chave_servico"] = "";
                    $item["servico"] = "";
                    $item["chave_transporte_grupo"] = "";
                    $item["transporte_grupo"] = "";
                    if ($servico) {
                        $item["chave_servico"] = $servico->codigo;
                        $item["servico"] = $servico->toString();
                    }
                    if ($tg) {
                        $item["chave_transporte_grupo"] = $tg->chave;
                        $item["transporte_grupo"] = $tg->toString();
                    }
                    echo '"' . implode('";"', $item) . '"' . PHP_EOL;
                }
            }
            die();
        }
        $this->_redirect($this->_request->getControllerName() . "/index");
        die();
    }

    public function agrupavinculocoordenadorAction()
    {
        $tb = new TbVinculo();
        $tb_vp = new TbVinculoPessoa();
        $tb_vpt = new TbVinculoPessoaTipo();
        $vpt = $tb_vpt->getPorChave("CO");
        if ($vpt) {
            $vinculos = $tb->listar();
            if ($vinculos) {
                foreach ($vinculos as $vinculo) {
                    $coordenador = $vinculo->pega_coordenador();
                    if (!$coordenador) {
                        $vp = $tb_vp->createRow();
                        $vp->id_vinculo = $vinculo->getId();
                        $vp->id_pessoa_fisica = $vinculo->id_pessoa_fisica;
                        $vp->id_vinculo_pessoa_tipo = $vpt->getId();
                        $vp->save();
                    }
                }
            }
        }
        $this->addMensagem("OPERAÇÃO FINALIZADA!");
        $this->_redirect($this->_request->getControllerName() . "/index");
        die();
    }

    public function gera_motorista_excel()
    {
        try {
            $linhas = $campos = array();

            $campos[] = "transporte_grupo";
            $campos[] = "data_cadastro";
            $campos[] = "matricula";
            $campos[] = "cpf";
            $campos[] = "nome";
            $campos[] = "cnh_numero";
            $campos[] = "cnh_categoria";
            $campos[] = "cnh_validade";

            //título
            $linha = array();
            foreach ($campos as $campo) {
                $linha[$campo] = $campo;
            }

            include_once("PHPExcel/Classes/PHPExcel.php");
            $objPHPExcel = new PHPExcel();
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

            $planilha = $objPHPExcel->getActiveSheet();

            $arquivo_nome = "motoristas_" . date("Ymd") . "_" . date("His") . ".xlsx";
            $planilha->setTitle("motorista");
            $filename = "file/" . $arquivo_nome;
            //$filename = "/Users/zeluis/Downloads/" . $arquivo_nome;

            $linhas[] = $linha;

            $tb = new TbMotorista();
            $objs = $tb->listar();
            if (!$objs) {
                throw new Exception("Falha, Nenhum Motorista!");
            }
            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {

                $pf = $obj->pegaPessoaFisica();
                if (!$pf) {
                    throw new Exception("Falha, Nenhuma Pessoa Física!");
                }

                //total
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $txt = array();
                $txt["percentual"] = Escola_Util::number_format($percentual) . " %";
                $txt["qtd"] = $qtd;
                $txt["cpf"] = Escola_Util::formatCpf($pf->cpf);
                $txt["nome"] = $pf->nome;

                echo implode(" - ", $txt) . PHP_EOL;

                $linha = array();
                foreach ($campos as $campo) {
                    $linha[$campo] = "";
                }

                //configurar linhas
                $pm = $obj->findParentRow("TbPessoaMotorista");
                $tg = $obj->findParentRow("TbTransporteGrupo");
                if ($tg) {
                    $linha["transporte_grupo"] = $tg->toString();
                }

                $linha["cpf"] = Escola_Util::formatCpf($pf->cpf);
                $linha["nome"] = $pf->nome;

                $linha["matricula"] = $obj->matricula;
                $linha["data_cadastro"] = Escola_Util::formatData($obj->data_cadastro);

                if ($pm) {
                    $linha["cnh_numero"] = $pm->cnh_numero;
                    $linha["cnh_validade"] = Escola_Util::formatData($pm->cnh_validade);
                    $cnh_categoria = $pm->findParentRow("TbCnhCategoria");
                    if ($cnh_categoria) {
                        $linha["cnh_categoria"] = $cnh_categoria->codigo;
                    }
                }

                $linhas[] = $linha;
            }

            $linha_numero = 0;
            foreach ($linhas as $linha) {
                $linha_numero++;
                $coluna_numero = 0;
                foreach ($linha as $coluna => $valor) {
                    $planilha->getCellByColumnAndRow($coluna_numero, $linha_numero)->setValue($valor);
                    $coluna_numero++;
                }
            }

            $objWriter->save($filename);

            echo "Arquivo ... {$filename} criado!" . PHP_EOL;
            die();
        } catch (Exception $ex) {
            if (isset($obj)) {
                print_r($obj);
            }
            throw $ex;
        }
    }

    public function gera_moto_taxi_excel()
    {
        try {
            $db = Zend_Registry::get("db");

            $linhas = $campos = array();

            $campos[] = "codigo";
            $campos[] = "nome";

            //título
            $linha = array();
            foreach ($campos as $campo) {
                $linha[$campo] = $campo;
            }

            include_once("PHPExcel/Classes/PHPExcel.php");
            $objPHPExcel = new PHPExcel();
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

            $planilha = $objPHPExcel->getActiveSheet();

            $arquivo_nome = "moto_taxi_" . date("Ymd") . "_" . date("His") . ".xlsx";
            $planilha->setTitle("moto_taxi");
            //$filename = "file/" . $arquivo_nome;
            $filename = "/Users/zeluis/Downloads/" . $arquivo_nome;

            $linhas[] = $linha;

            $sql = "select a.id_transporte, a.codigo, e.nome
                    from transporte a 
                        inner join transporte_pessoa b on a.id_transporte = b.id_transporte 
                        inner join pessoa c on b.id_pessoa = c.id_pessoa 
                        inner join pessoa_tipo d on c.id_pessoa_tipo = d.id_pessoa_tipo and d.chave = 'PF' 
                        left outer join pessoa_fisica e on c.id_pessoa = e.id_pessoa 
                    where (a.id_transporte_grupo = 21)
                    order by e.nome";
            $stmt = $db->query($sql);
            if (!($stmt && $stmt->rowCount())) {
                throw new Exception("Falha, Nenhum Moto-Taxi!");
            }

            $objs = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {

                //total
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $txt = array();
                $txt["percentual"] = Escola_Util::number_format($percentual) . " %";
                $txt["qtd"] = $qtd;
                $txt["nome"] = $obj->nome;

                echo implode(" - ", $txt) . PHP_EOL;

                $linha = array();
                foreach ($campos as $campo) {
                    $linha[$campo] = "";
                }

                $linha["codigo"] = $obj->codigo;
                $linha["nome"] = $obj->nome;


                $linhas[] = $linha;
            }

            foreach ($linhas as $linha) {
                $linha_numero = $planilha->getHighestRow() + 1;
                $coluna_numero = 0;
                foreach ($linha as $coluna => $valor) {
                    $planilha->getCellByColumnAndRow($coluna_numero, $linha_numero)->setValue($valor);
                    $coluna_numero++;
                }
            }

            $objWriter->save($filename);

            echo "Arquivo ... {$filename} criado!" . PHP_EOL;
            die();
        } catch (Exception $ex) {
            if (isset($obj)) {
                print_r($obj);
            }
            throw $ex;
        }
    }

    public function relatorio_turismo()
    {
        try {
            $db = Zend_Registry::get("db");

            $linhas = $campos = array();

            $campos[] = "id_transporte";
            $campos[] = "codigo";
            $campos[] = "proprietario";
            $campos[] = "placa";
            $campos[] = "marca";
            $campos[] = "modelo";
            $campos[] = "situacao";

            //título
            $linha = array();
            foreach ($campos as $campo) {
                $linha[$campo] = $campo;
            }

            include_once("PHPExcel/Classes/PHPExcel.php");
            $objPHPExcel = new PHPExcel();
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

            $planilha = $objPHPExcel->getActiveSheet();

            $arquivo_nome = "turismo_" . date("Ymd") . "_" . date("His") . ".xlsx";
            $planilha->setTitle("turismo");
            //$filename = "file/" . $arquivo_nome;
            $filename = "/Users/zeluis/Downloads/" . $arquivo_nome;

            $linhas[] = $linha;

            $sql = "select a.id_transporte, a.codigo
                    from transporte a 
                    where (a.id_transporte_grupo = 9)
                    order by a.codigo";
            $stmt = $db->query($sql);
            if (!($stmt && $stmt->rowCount())) {
                throw new Exception("Falha, Nenhum Moto-Taxi!");
            }

            $objs = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {

                //total
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $txt = array();
                $txt["percentual"] = Escola_Util::number_format($percentual) . " %";
                $txt["qtd"] = $qtd;
                $txt["codigo"] = $obj->codigo;

                echo implode(" - ", $txt) . PHP_EOL;

                $linha = array();
                foreach ($campos as $campo) {
                    $linha[$campo] = "--";
                }

                $transporte = TbTransporte::pegaPorId($obj->id_transporte);
                if (!$transporte) {
                    continue;
                }

                $linha["id_transporte"] = $transporte->getId();
                $linha["codigo"] = $transporte->codigo;

                $tp_pessoa = $transporte->pegaProprietario();
                if (!$tp_pessoa) {
                    continue;
                }

                $pessoa = $tp_pessoa->getPessoa();
                if (!$pessoa) {
                    continue;
                }

                $linha["proprietario"] = $pessoa->toString();

                $tvs = $transporte->getVeiculos();
                if ($tvs && $tvs->count()) {
                    foreach ($tvs as $tv) {
                        $veiculo = $tv->pegaVeiculo();
                        if (!$veiculo) {
                            continue;
                        }
                        $linha["placa"] = $veiculo->placa;
                        if ($veiculo->modelo) {
                            $linha["modelo"] = $veiculo->modelo;
                        }
                        $fab = $veiculo->getFabricante();
                        if ($fab) {
                            $linha["marca"] = $fab->toString();
                        }
                        $tvs = $tv->getTransporteVeiculoStatus();
                        if ($tvs) {
                            $linha["situacao"] = $tvs->toString();
                        }
                        $linhas[] = $linha;

                        $linha["id_transporte"] = "";
                        $linha["codigo"] = "";
                        $linha["proprietario"] = "";
                    }
                } else {
                    $linhas[] = $linha;
                }
            }

            foreach ($linhas as $linha) {
                $linha_numero = $planilha->getHighestRow() + 1;
                $coluna_numero = 0;
                foreach ($linha as $coluna => $valor) {
                    $planilha->getCellByColumnAndRow($coluna_numero, $linha_numero)->setValueExplicit($valor, PHPExcel_Cell_DataType::TYPE_STRING);
                    $coluna_numero++;
                }
            }

            $objWriter->save($filename);

            echo "Arquivo ... {$filename} criado!" . PHP_EOL;
            die();
        } catch (Exception $ex) {
            $erro = new stdClass();
            $erro->erro = $ex->getMessage();
            print_r($erro);

            throw $ex;
        }
    }
}
