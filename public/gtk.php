<?php
include "../application/bootstrap.php";
$configSection = getenv('TESTE_CONFIG') ? getenv('TESTE_CONFIG') : "general";
$bootstrap = new Bootstrap($configSection);

ini_set("memory_limit", "900M");

class JanelaProgresso extends GtkWindow
{

    private $progresso;
    private $buttons;
    private $nome_arquivo;
    private $labels = array();
    private $dados = array();

    public function __construct()
    {
        parent::__construct();

        $this->set_title("PROGRAMA DE PROCESSAMENTO EM MASSA - INTRANET");
        $this->set_position(GTK::WIN_POS_CENTER);
        $this->connect_simple('destroy', array('Gtk', 'main_quit'));

        $vbox = new GtkVBox();
        $box_file = new GtkFixed();

        $this->buttons = array();
        $this->buttons["atualiza_bairros"] = new GtkRadioButton(null, "Atualiza Bairros");

        $this->buttons["carteira_unimulher"] = new GtkRadioButton($this->buttons["atualiza_bairros"], "Carteirinha UNIMULHER");
        $this->buttons["carteira_unimulher"]->set_active(true);

        foreach ($this->buttons as $button) {
            $vbox->pack_start($button);
        }

        $frame = new GtkFrame("Op��es de Processamento: ");
        $frame->set_size_request(680, 25 * count($this->buttons));
        $frame->add($vbox);
        $size = $frame->get_size_request();

        $frame_file = new GtkFrame("Sele��o do Arquivo: ");
        $frame_file->set_size_request(680, 60);
        $frame_file->add($box_file);

        $this->nome_arquivo = new GtkEntry();
        $this->nome_arquivo->set_size_request(600, 30);
        $this->nome_arquivo->set_text("/home/zeluis/Documentos/projetos/umap/unimulher_turma_a.csv");
        $box_file->put($this->nome_arquivo, 5, 5);
        $button_file = new GtkButton("...");
        $button_file->set_size_request(50, 30);
        $button_file->connect("clicked", array($this, "fileOpen"));
        $box_file->put($button_file, 610, 5);

        $box = new GtkFixed();
        $this->add($box);

        $button = new GtkButton("EXECUTAR PROCESSAMENTO");
        $button->connect("clicked", array($this, "processar"));
        $button->set_size_request(250, 50);

        $this->progresso = new Escola_Gtk_MeuProgresso();
        $this->progresso->set_size_request(680, 100);
        $this->progresso->set_valor_total(500);

        $this->dados["progresso"] = $this->progresso;

        $this->labels[] = new GtkLabel("In�cio: ");
        $this->labels[] = new GtkLabel("T�rmino: ");

        $box->put($frame, 10, 10);
        $box->put($frame_file, 10, 10 + $size[1]);
        $box->put($this->progresso, 10, 75 + $size[1]);
        $box->put($button, 220, 190 + $size[1]);
        $box->put($this->labels[0], 10, 190 + $size[1]);
        $box->put($this->labels[1], 490, 190 + $size[1]);

        $this->set_default_size(700, 260 + $size[1]);

        $this->show_all();

        $this->dados["filtro_cod_opcao"] = 12;

        $session = Escola_Session::getInstance();

        Gtk::main();
    }

    public function processar()
    {
        $this->set_sensitive(false);
        $this->labels[0]->set_text("In�cio: " . date("h:i:s"));
        foreach ($this->buttons as $k => $button) {
            if ($button->get_active()) {
                $metodo = "roda_" . $k;
                $this->$metodo();
                break;
            }
        }
        $this->set_sensitive(true);
        $this->labels[1]->set_text("T�rmino: " . date("h:i:s"));
        $this->progresso->set_progresso(0);
        $this->progresso->set_text("PROCESSAMENTO FINALIZADO!");
    }

    public function fileOpen()
    {
        $dialog = new GtkFileChooserDialog("Selecione o Arquivo: ", null, Gtk::FILE_CHOOSER_ACTION_OPEN, array(
            Gtk::STOCK_OK, Gtk::RESPONSE_OK,
            Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
        ));
        $response = $dialog->run();
        if ($response == Gtk::RESPONSE_OK) {
            $this->nome_arquivo->set_text($dialog->get_filename());
        }
        $dialog->destroy();
    }

    public function corrige_telegone($telefone)
    {
        $telefone = trim(Escola_Util::limpaNumero($telefone));
        switch (strlen($telefone)) {
            case 7:
                $telefone = '9' . $telefone;
                break;
        }
        return $telefone;
    }

    public function pega_pessoa($cpfcnpj)
    {
        $obj = false;
        $cpfcnpj = trim(Escola_Util::limpaNumero($cpfcnpj));
        if ($cpfcnpj) {
            if (Escola_Util::validaCPF($cpfcnpj)) {
                $tb = new TbPessoaFisica();
                $obj = $tb->getPorCPF($cpfcnpj);
            } elseif (Escola_Util::isCnpjValid($cpfcnpj)) {
                $tb = new TbPessoaJuridica();
                $obj = $tb->getPorCNPJ($cpfcnpj);
            }
        }
        if ($obj) {
            return $obj;
        }
        return false;
    }

    public function roda_atualiza_bairros()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $linhas = Escola_Util::carregaArquivoDados($this->nome_arquivo->get_text());
        if ($linhas) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total(count($linhas));
            foreach ($linhas as $linha) {
                if (trim($linha["novo_bairro"])) {
                    $linha["novo_bairro"] = $linha["novo_bairro"];
                    $sql = "update public.pessoa set bairro = '{$linha["novo_bairro"]}' where (bairro = '{$linha["bairro"]}')";
                    $dbemtu->query($sql);
                    $this->progresso->progresso("PROCESSANDO ...");
                }
            }
        }
    }

    public function roda_importar_pessoa_fisica()
    {
        $tb_uf = new TbUf();
        $tb_municipio = new TbMunicipio();
        $tb_bairro = new TbBairro();
        $tb = new TbPessoaFisica();
        $tb_pj = new TbPessoaJuridica();
        $db = Zend_Registry::get("db");
        $dbemtu = Zend_Registry::get("dbemtu");
        $sql = "select a.*, b.municipio
                from public.pessoa a, public.municipio b
                where (a.fkmunicipio = b.id)
                order by a.id";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                try {
                    $db->beginTransaction();
                    if ($obj->cpfcnpj) {
                        $filter = new Escola_Validate_Cpf();
                        if ($filter->isValid($obj->cpfcnpj)) {
                            $pf = $tb->getPorCPF($obj->cpfcnpj);
                            if (!$pf) {
                                $dados = array();
                                $dados["cpf"] = $obj->cpfcnpj;
                                $dados["nome"] = $obj->nome;
                                if (trim($obj->ci)) {
                                    $dados["identidade_numero"] = $obj->ci;
                                }
                                if (trim($obj->ciemissor)) {
                                    $dados["identidade_orgao_expedidor"] = $obj->ciemissor;
                                }
                                if (trim($obj->email)) {
                                    $dados["email"] = $obj->email;
                                }
                                $pf = $tb->createRow();
                                $pf->setFromArray($dados);
                                $pf->save();
                            }
                            if (!$pf->getId()) {
                                echo "nenhuma pessoa fisica cadastrada";
                                Zend_Debug::dump($obj);
                                die();
                            }
                            $pessoa = $pf->pega_pessoa();
                            if (trim($obj->cnh)) {
                                $pm = $pf->pegaPessoaMotorista();
                                if (!$pm) {
                                    $dados_cnh = array(
                                        "id_pessoa_fisica" => $pf->getId(),
                                        "cnh_numero" => $obj->cnh
                                    );
                                    if ($obj->cnhcat) {
                                        $tb_cnh_categoria = new TbCnhCategoria();
                                        $cnh_categoria = $tb_cnh_categoria->getPorChave($obj->cnhcat);
                                        if ($cnh_categoria) {
                                            $dados_cnh["id_cnh_categoria"] = $cnh_categoria->getId();
                                        }
                                    }
                                    if ($obj->cnhuf) {
                                        $uf = $tb_uf->getPorSigla($obj->cnhuf);
                                        if ($uf) {
                                            $dados_cnh["id_uf"] = $uf->getId();
                                        }
                                    }
                                    $tb_pm = new TbPessoaMotorista();
                                    $pm = $tb_pm->createRow();
                                    $pm->setFromArray($dados_cnh);
                                    $errors = $pm->getErrors();
                                    if (!$errors) {
                                        $pm->save();
                                    }
                                }
                            }
                        } elseif (Escola_Util::isCnpjValid($obj->cpfcnpj)) {
                            $pj = $tb_pj->getPorCNPJ($obj->cpfcnpj);
                            if (!$pj) {
                                $pj = $tb_pj->createRow();
                                $dados = array(
                                    "cnpj" => $obj->cpfcnpj,
                                    "razao_social" => $obj->nome,
                                    "nome_fantasia" => $obj->nome
                                );
                                $pj->setFromArray($dados);
                                $errors = $pj->getErrors(false);
                                if (!$errors) {
                                    $pj->save();
                                } else {
                                    Zend_Debug::dump($errors);
                                    die();
                                }
                            }
                            if ($pj) {
                                $pessoa = $pj->pega_pessoa();
                            }
                        } else {
                            Zend_Debug::dump($obj);
                        }
                        if (isset($pessoa) && $pessoa) {
                            $dados = array();
                            if (trim($obj->endereco)) {
                                $dados["logradouro"] = $obj->endereco;
                            }
                            if (trim($obj->cep)) {
                                $dados["cep"] = $obj->cep;
                            }
                            if (trim($obj->enderecouf) && trim($obj->municipio) && trim($obj->bairro)) {
                                $ufs = $tb_uf->fetchAll("sigla = '{$obj->enderecouf}'");
                                if ($ufs && count($ufs)) {
                                    $uf = $ufs->current();
                                    $sql = $tb_municipio->select();
                                    $sql->where("id_uf = {$uf->getId()}");
                                    $sql->where("descricao = '{$obj->municipio}'");
                                    $muns = $tb_municipio->fetchAll($sql);
                                    if ($muns && count($muns)) {
                                        $municipio = $muns->current();
                                    } else {
                                        $municipio = $tb_municipio->createRow();
                                        $municipio->setFromArray(array(
                                            "id_uf" => $uf->getId(),
                                            "descricao" => trim($obj->municipio)
                                        ));
                                        $municipio->save();
                                    }
                                    $bairros = $tb_bairro->fetchAll("id_municipio = {$municipio->getId()} and descricao = '{$obj->bairro}'");
                                    if ($bairros && count($bairros)) {
                                        $bairro = $bairros->current();
                                    } else {
                                        $bairro = $tb_bairro->createRow();
                                        $bairro->setFromArray(array("id_municipio" => $municipio->getId(), "descricao" => $obj->bairro));
                                        $bairro->save();
                                    }
                                    $dados["endereco_id_bairro"] = $bairro->getId();
                                }
                            }
                            $pessoa->setFromArray($dados);
                            $pessoa->save();
                            if (trim($obj->foneresdencial)) {
                                $obj->foneresdencial = $this->corrige_telegone($obj->foneresdencial);
                                $sql = $db->select();
                                $sql->from(array("pr" => "pessoa_ref"));
                                $sql->join(array("t" => "telefone"), "t.id_telefone = pr.chave", array());
                                $sql->where("pr.id_pessoa = {$pessoa->getId()}");
                                $sql->where("pr.tipo = 'T'");
                                $sql->where("numero = '{$obj->foneresdencial}'");
                                $rs = $db->query($sql);
                                if (!$rs  || !$rs->rowCount()) {
                                    $tb_tt = new TbTelefoneTipo();
                                    $tt = $tb_tt->getPorChave("F");
                                    if ($tt) {
                                        $tb_fone = new TbTelefone();
                                        $fone = $tb_fone->createRow();
                                        $fone->setFromArray(array(
                                            "ddd" => "96",
                                            "numero" => $obj->foneresdencial,
                                            "id_telefone_tipo" => $tt->getId()
                                        ));
                                        $fone->save();
                                        $pessoa->addTelefone($fone);
                                    }
                                }
                            }
                            if (trim($obj->fonecelular)) {
                                $obj->fonecelular = $this->corrige_telegone($obj->fonecelular);
                                $sql = $db->select();
                                $sql->from(array("pr" => "pessoa_ref"));
                                $sql->join(array("t" => "telefone"), "t.id_telefone = pr.chave", array());
                                $sql->where("pr.id_pessoa = {$pessoa->getId()}");
                                $sql->where("pr.tipo = 'T'");
                                $sql->where("numero = '{$obj->fonecelular}'");
                                $rs = $db->query($sql);
                                if (!$rs  || !$rs->rowCount()) {
                                    $tb_tt = new TbTelefoneTipo();
                                    $tt = $tb_tt->getPorChave("C");
                                    if ($tt) {
                                        $tb_fone = new TbTelefone();
                                        $fone = $tb_fone->createRow();
                                        $fone->setFromArray(array(
                                            "ddd" => "96",
                                            "numero" => $obj->fonecelular,
                                            "id_telefone_tipo" => $tt->getId()
                                        ));
                                        $fone->save();
                                        $pessoa->addTelefone($fone);
                                    }
                                }
                            }
                        }
                    }
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                }
                $this->progresso->progresso("Processando ... " . $obj->cpfcnpj);
            }
        }
    }

    public function roda_atualiza_cor()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $stmt = $dbemtu->query("select * from cor");
        if ($stmt && $stmt->rowCount()) {
            $tb = new TbCor();
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $cor = $tb->getPorDescricao($obj->cor);
                if (!$cor) {
                    $cor = $tb->createRow();
                    $cor->setFromArray(array("descricao" => $obj->cor));
                    $errors = $cor->getErrors();
                    if (!$errors) {
                        $cor->save();
                    } else {
                        Zend_Debug::dump($errors);
                        die();
                    }
                }
                $this->progresso->progresso("PROCESSANDO ... " . $obj->cor);
            }
        }
    }

    public function roda_atualiza_fabricante()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $stmt = $dbemtu->query("select * from fabricante");
        if ($stmt && $stmt->rowCount()) {
            $tb = new TbFabricante();
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $reg = $tb->getPorDescricao($obj->fabricante);
                if (!$reg) {
                    $reg = $tb->createRow();
                    $reg->setFromArray(array("descricao" => $obj->fabricante));
                    $errors = $reg->getErrors();
                    if (!$errors) {
                        $reg->save();
                    } else {
                        Zend_Debug::dump($errors);
                        die();
                    }
                }
                $this->progresso->progresso("PROCESSANDO ... " . $obj->fabricante);
            }
        }
    }

    public function roda_atualiza_categoria()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $stmt = $dbemtu->query("select * from tipoveiculo");
        if ($stmt && $stmt->rowCount()) {
            $tb = new TbVeiculoCategoria();
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $reg = $tb->getPorDescricao($obj->descricao);
                if (!$reg) {
                    $obj->descricao = trim($obj->descricao);
                    $reg = $tb->createRow();
                    $reg->setFromArray(array("descricao" => $obj->descricao));
                    $contador = 0;
                    $descricao = Escola_Util::maiuscula(str_replace(" ", "", $obj->descricao));
                    do {
                        $contador++;
                        if ($contador < strlen($descricao)) {
                            $reg->chave = substr($descricao, 0, $contador);
                            $errors = $reg->getErrors();
                            if (!$errors) {
                                $reg->save();
                                break;
                            }
                        } else {
                            Zend_Debug::dump($obj);
                        }
                    } while ($errors);
                }
                $this->progresso->progresso("PROCESSANDO ... " . $obj->descricao);
            }
        }
    }

    public function roda_atualiza_infracao()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $stmt = $dbemtu->query("select * from artigo");
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_al = new TbAmparoLegal();
            $tb_infracao = new TbInfracao();
            $tb_moeda = new TbMoeda();
            $ufir = $tb_moeda->getPorSimbolo("ufir");
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $al = $tb_al->getPorDescricao("INFRA��ES DE TR�NSITO");
                if ($al) {
                    if (trim($obj->codigo) && trim($obj->descartigo)) {
                        $infracao = $tb_infracao->getPorCodigo($obj->codigo);
                        if (!$infracao) {
                            $infracao = $tb_infracao->createRow();
                            $infracao->id_amparo_legal = $al->getId();
                            $infracao->codigo = $obj->codigo;
                        }
                        $dados = array(
                            "descricao" => $obj->descartigo,
                            "valor" => Escola_Util::number_format($obj->vlremufir),
                            "pontuacao" => $obj->pontuacao
                        );
                        if ($ufir) {
                            $dados["id_moeda"] = $ufir->getId();
                        }
                        $infracao->setFromArray($dados);
                        $errors = $infracao->getErrors();
                        if (!$errors) {
                            $infracao->save();
                        } else {
                            Zend_Debug::dump($errors);
                            die();
                        }
                    }
                }
                $this->progresso->progresso("Processando ... " . $obj->codigo);
            }
        }
    }

    public function roda_atualiza_veiculo()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $db = Zend_Registry::get("db");
        $sql = "select b.fabricante,
                        c.cor, 
                        d.cpfcnpj as proprietario, 
                        e.municipio,
                        f.descricao as categoria,
                        a.*
                 from veiculo a, fabricante b, cor c, pessoa d, municipio e, tipoveiculo f
                 where (a.fkfabricante = b.id)
                 and (a.fkcor = c.id)
                 and (a.fkpessoa = d.id)
                 and (a.fkmunicipio = e.id)
                 and (a.fktipoveiculo = f.id)
                 
                 and (a.chassi is null or a.chassi = '')

                 order by a.chassi";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_veiculo_tipo = new TbVeiculoTipo();
            $vt = $tb_veiculo_tipo->getPorChave("CA"); //carro
            $tb_veiculo = new TbVeiculo();
            $tb_pf = new TbPessoaFisica();
            $tb_pj = new TbPessoaJuridica();
            $tb_fabricante = new TbFabricante();
            $tb_cor = new TbCor();
            $tb_categoria = new TbVeiculoCategoria();
            $tb_combustivel = new TbCombustivel();
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $dados = array();
                $obj->chassi = trim($obj->chassi);
                if (!$obj->chassi) {
                    if ($obj->placa) {
                        $veiculo = $tb_veiculo->getPorPlaca($obj->placa);
                    }
                } else {
                    $veiculo = $tb_veiculo->getPorChassi($obj->chassi);
                }
                if (!$veiculo) {
                    $veiculo = $tb_veiculo->createRow();
                }
                //if ($obj->chassi) {
                $dados["chassi"] = $obj->chassi;
                if ($vt) {
                    $dados["id_veiculo_tipo"] = $vt->getId();
                }
                if (trim($obj->fabricante)) {
                    $fab = $tb_fabricante->getPorDescricao(trim($obj->fabricante));
                    if ($fab) {
                        $dados["id_fabricante"] = $fab->getId();
                    }
                }
                if (trim($obj->cor)) {
                    $cor = $tb_cor->getPorDescricao(trim($obj->cor));
                    if ($cor) {
                        $dados["id_cor"] = $cor->getId();
                    }
                }
                if ($obj->proprietario) {
                    $pf = $tb_pf->getPorCPF($obj->proprietario);
                    if ($pf) {
                        $dados["proprietario_id_pessoa"] = $pf->id_pessoa;
                    } else {
                        $pj = $tb_pj->getPorCNPJ($obj->proprietario);
                        if ($pj) {
                            $dados["proprietario_id_pessoa"] = $pj->id_pessoa;
                        }
                    }
                }
                if (trim($obj->municipio) && trim($obj->uf)) {
                    $filter = new Zend_Filter_CharConverter();
                    $obj->municipio = $filter->filter($obj->municipio);
                    $sql = $db->select();
                    $sql->from(array("m" => "municipio"));
                    $sql->join(array("u" => "uf"), "m.id_uf = u.id_uf", array());
                    $sql->where("u.sigla = '{$obj->uf}'");
                    $sql->where("m.descricao = '{$obj->municipio}'");
                    $stmt_municipio = $db->query($sql);
                    if ($stmt_municipio && $stmt_municipio->rowCount()) {
                        $municipio = $stmt_municipio->fetch(Zend_Db::FETCH_OBJ);
                        $dados["id_municipio"] = $municipio->id_municipio;
                        $dados["id_uf"] = $municipio->id_uf;
                    } else {
                        $tb_uf = new TbUf();
                        $uf = $tb_uf->getPorSigla(trim($obj->uf));
                        if (!$uf) {
                            die("UF N�O LOCALIZADO ... " . $obj->uf);
                        }
                        $tb_municipio = new TbMunicipio();
                        $municipio = $tb_municipio->createRow();
                        $municipio->setFromArray(array(
                            "descricao" => $obj->municipio,
                            "id_uf" => $uf->getId()
                        ));
                        $errors = $municipio->getErrors();
                        if (!$errors) {
                            $municipio->save();
                            $dados["id_municipio"] = $municipio->id_municipio;
                            $dados["id_uf"] = $municipio->id_uf;
                        } else {
                            echo "munic�pio n�o inserido \n";
                            Zend_Debug::dump($errors);
                            die();
                        }
                    }
                }
                if (trim($obj->categoria)) {
                    $categoria = $tb_categoria->getPorDescricao(trim($obj->categoria));
                    if ($categoria) {
                        $dados["id_veiculo_categoria"] = $categoria->getId();
                    }
                }
                if (trim($obj->placa)) {
                    $dados["placa"] = trim($obj->placa);
                }
                if ($obj->tpcombustivel) {
                    $array_combustivel = array("GAZOLINA", "ALCOOL", "DIESEL", "BICOMBUST�VEL");
                    $combustivel = $tb_combustivel->getPorDescricao($array_combustivel[$obj->tpcombustivel - 1]);
                    if ($combustivel) {
                        $dados["id_combustivel"] = $combustivel->getId();
                    }
                }
                if (trim($obj->modelo)) {
                    $dados["modelo"] = trim($obj->modelo);
                }
                if (trim($obj->renavam)) {
                    $dados["renavan"] = trim($obj->renavam);
                }
                if ($obj->anofabricacao) {
                    $dados["ano_fabricacao"] = $obj->anofabricacao;
                }
                if ($obj->anomodelo) {
                    $dados["ano_modelo"] = $obj->anomodelo;
                }
                if (trim($obj->dut)) {
                    $dados["dut"] = trim($obj->dut);
                }
                $veiculo->setFromArray($dados);
                $errors = $veiculo->getErrors();
                if (!$errors) {
                    $veiculo->save();
                } else {
                    Zend_Debug::dump($obj);
                    Zend_Debug::dump($errors);
                    die("erro");
                }
                //}
                $this->progresso->progresso("Atualizando Ve�culo - {$obj->chassi} - {$obj->placa}");
            }
        }
    }

    public function roda_atualiza_transporte_taxi()
    {
        $session = Escola_Session::getInstance();
        $dbemtu = Zend_Registry::get("dbemtu");
        $db = Zend_Registry::get("db");
        $sql = "select *
                from taxi.taxi
                order by numtaxi";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_transporte = new TbTransporte();
            $tb_tg = new TbTransporteGrupo();
            $tb_concessao = new TbConcessao();
            $tb_concessao_tipo = new TbConcessaoTipo();
            $tb_concessao_validade = new TbConcessaoValidade();
            $tb_transporte_pessoa = new TbTransportePessoa();
            $tb_transporte_pessoa_tipo = new TbTransportePessoaTipo();
            $pt_proprietario = $tb_transporte_pessoa_tipo->getPorChave("PR");
            $tb_transporte_pessoa_status = new TbTransportePessoaStatus();
            $ps_proprietario = $tb_transporte_pessoa_status->getPorChave("A");
            $cv = $tb_concessao_validade->getPorChave("I");
            $tg = $tb_tg->getPorChave("TX");
            $tb_taxi = new TbTaxi();
            $tb_veiculo = new TbVeiculo();
            $tb_tv = new TbTransporteVeiculo();
            $tb_tvs = new TbTransporteVeiculoStatus();
            $tb_baixa = new TbTransporteVeiculoBaixa();
            $tb_bm = new TbBaixaMotivo();
            if ($tg) {
                while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                    //Zend_Debug::dump($obj); 
                    $db->beginTransaction();
                    try {
                        $codigo = "RR" . Escola_Util::zero($obj->numtaxi, 5);
                        $transportes = $tb_transporte->listar(array("filtro_id_transporte_grupo" => $tg->getId(), "filtro_codigo" => $codigo));
                        if ($transportes) {
                            $transporte = $transportes->current();
                        } else {
                            $transporte = $tb_transporte->createRow();
                            $transporte->id_transporte_grupo = $tg->getId();
                        }
                        $concessao = $transporte->findParentRow("TbConcessao");
                        $sql = "select c.cpfcnpj, b.descricao as concessao_tipo, a.*
                                from taxi.concessao a, taxi.tipoconcessao b, public.pessoa c
                                where (a.concessaotipo = b.id)
                                and (a.fkpessoa = c.id)
                                and (a.fktaxi = {$obj->numtaxi})";
                        $stmt1 = $dbemtu->query($sql);
                        if ($stmt1 && $stmt1->rowCount()) {
                            if (!$concessao) {
                                $concessao = $tb_concessao->createRow();
                            }
                            $obj_concessao = $stmt1->fetch(Zend_Db::FETCH_OBJ);
                            $cdt = false;
                            $filter = new Zend_Validate_Date();
                            if ($filter->isValid($obj_concessao->concessaodt)) {
                                $cdt = new Zend_Date($obj_concessao->concessaodt);
                            }
                            $dados = array();
                            $dados["concessao_data"] = Escola_Util::formatData($obj_concessao->concessaodt);
                            $dados["numero"] = $obj_concessao->concessaonum;
                            $dados["decreto"] = $obj_concessao->concessaodecreto;
                            $dados["processo_numero"] = $obj_concessao->concessaodecreto;
                            if ($cdt) {
                                $dados["processo_ano"] = $cdt->toString("YYYY");;
                            }
                            $ct = $tb_concessao_tipo->getPorDescricao($obj_concessao->concessao_tipo);
                            if ($ct) {
                                $dados["id_concessao_tipo"] = $ct->getId();
                            }
                            if ($cv) {
                                $dados["id_concessao_validade"] = $cv->getId();
                            }
                            $concessao->setFromArray($dados);
                            $errors = $concessao->getErrors();
                            if (!$errors) {
                                $concessao->save();
                                $transporte->set_concessao($concessao);
                            }
                            $taxi = $transporte->getTransporteInstancia();
                            $dados = array("id_transporte_grupo" => $tg->getId(), "codigo" => $codigo, "id_transporte" => $transporte->id_transporte);
                            $transporte->setFromArray($dados);
                            $errors = $transporte->getErrors();
                            if (!$errors) {
                                $transporte->save();
                                $pessoa = $this->pega_pessoa($obj_concessao->cpfcnpj);
                                if ($pessoa) {
                                    $proprietario = $transporte->pegaProprietario();
                                    if (!$proprietario) {
                                        $proprietario = $tb_transporte_pessoa->createRow();
                                    }
                                    $proprietario->setFromArray(array(
                                        "id_transporte" => $transporte->getId(),
                                        "id_pessoa" => $pessoa->id_pessoa,
                                        "id_transporte_pessoa_tipo" => $pt_proprietario->getId(),
                                        "id_transporte_pessoa_status" => $ps_proprietario->getId()
                                    ));
                                    $errors = $proprietario->getErrors();
                                    if (!$errors) {
                                        $proprietario->save();
                                    }
                                }
                                $sql = "select b.chassi, b.placa, a.*
                                        from taxi.veiculo a, public.veiculo b
                                        where (a.fkveiculo = b.id)
                                        and (a.fktaxi = {$obj->numtaxi})
                                        order by dtcad";
                                $stmt2 = $dbemtu->query($sql);
                                if ($stmt2 && $stmt2->rowCount()) {
                                    while ($obj_veiculo = $stmt2->fetch(Zend_Db::FETCH_OBJ)) {
                                        $veiculo = false;
                                        if ($obj_veiculo->chassi) {
                                            $veiculo = $tb_veiculo->getPorChassi($obj_veiculo->chassi);
                                        } elseif ($obj_veiculo->placa) {
                                            $veiculo = $tb_veiculo->getPorPlaca($obj_veiculo->placa);
                                        }
                                        if ($veiculo) {
                                            $tvs = $tb_tv->listar(array(
                                                "id_transporte" => $transporte->getId(),
                                                "id_veiculo" => $veiculo->getId()
                                            ));
                                            if ($tvs) {
                                                $tv = $tvs->current();
                                            } else {
                                                $tv = $tb_tv->createRow();
                                                $tv->id_transporte = $transporte->getId();
                                                $tv->id_veiculo = $veiculo->getId();
                                            }
                                            $dados = array();
                                            $sits = array("A", "B", "I");
                                            if ($obj_veiculo->situacao) {
                                                $tvs = $tb_tvs->getPorChave($sits[$obj_veiculo->situacao - 1]);
                                                if ($tvs) {
                                                    $dados["id_transporte_veiculo_status"] = $tvs->getId();
                                                }
                                            }
                                            if (Escola_Util::validaData($obj_veiculo->dtcadastro)) {
                                                $dados["data_cadastro"] = $obj_veiculo->dtcadastro;
                                            }
                                            if ($obj_veiculo->fkprotocolo) {
                                                $dados["processo"] = $obj_veiculo->fkprotocolo;
                                            }
                                            if (Escola_Util::validaData($obj_veiculo->fkprotocolodt)) {
                                                $dados["processo_data"] = $obj_veiculo->fkprotocolodt;
                                            }
                                            $tv->setFromArray($dados);
                                            $errors = $tv->getErrors();
                                            if (!$errors) {
                                                $tv->save();
                                                if ($tv->baixa() && Escola_Util::validaData($obj_veiculo->baixadt)) {
                                                    $baixa = $tv->pegaBaixa();
                                                    if (!$baixa) {
                                                        $baixa = $tb_baixa->createRow();
                                                        $baixa->id_transporte_veiculo = $tv->getId();
                                                        $baixa->id_usuario = 1;
                                                    }
                                                    $bts = array("PR", "SAV");
                                                    if ($obj_veiculo->baixamotivo) {
                                                        $bm = $tb_bm->getPorChave($bts[$obj_veiculo->baixamotivo - 1]);
                                                        if ($bm) {
                                                            $baixa->id_baixa_motivo = $bm->getId();
                                                        }
                                                        $baixa->baixa_data = $obj_veiculo->baixadt;
                                                        $baixa->motivo = $bm->toString();
                                                        $errors = $baixa->getErrors();
                                                        if (!$errors) {
                                                            $baixa->save();
                                                        }
                                                    } else {
                                                        Zend_Debug::dump("nenhum motivo informado!");
                                                        Zend_Debug::dump($obj_veiculo);
                                                    }
                                                }
                                            } else {
                                                Zend_Debug::dump($errors);
                                            }
                                        }
                                    }
                                }
                            } else {
                                Zend_Debug::dump($errors);
                                die();
                            }
                        }
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollBack();
                        echo $e->getMessage();
                        echo "\n" . $e->getFile();
                        echo "\n" . $e->getLine();
                        die();
                    }
                    $this->progresso->progresso("Atualizando Transporte > Taxi ... " . $obj->numtaxi);
                }
            }
        }
    }

    public function roda_atualiza_taxi_licenca()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $tb_tg = new TbTransporteGrupo();
        $tg = $tb_tg->getPorChave("TX");
        $tb_servico = new TbServico();
        $tb_stg = new TbServicoTransporteGrupo();
        $servico = $tb_servico->getPorCodigo("LT");
        $tb_sss = new TbServicoSolicitacaoStatus();
        $tb_ss = new TbServicoSolicitacao();
        $tb_transporte = new TbTransporte();
        if ($tg && $servico) {
            $rs = $tb_stg->listar(array("id_transporte_grupo" => $tg->getId(), "id_servico" => $servico->getId()));
            if ($rs && count($rs)) {
                $stg = $rs->current();
                $sql = "select * from taxi.licenca order by id";
                $stmt = $dbemtu->query($sql);
                if ($stmt && $stmt->rowCount()) {
                    $this->progresso->set_progresso(0);
                    $this->progresso->set_valor_total($stmt->rowCount());
                    while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                        Zend_Debug::dump($obj);

                        $codigo = "RR" . Escola_Util::zero($obj->fktaxi, 5);
                        $ano_referencia = substr($obj->licencanum, 0, 4);
                        $licenca_numero = (int) substr($obj->licencanum, 4, 6);
                        $transportes = $tb_transporte->listar(array(
                            "filtro_id_transporte_grupo" => $tg->getId(),
                            "filtro_codigo" => $codigo
                        ));
                        if ($transportes && count($transportes)) {
                            $transporte = $transportes->current();
                            $dados = array(
                                "id_transporte" => $transporte->getId(),
                                "id_servico_transporte_grupo" => $stg->getId(),
                                "ano_referencia" => $ano_referencia,
                                "codigo" => $licenca_numero
                            );
                            $rs_sss = $tb_ss->listar($dados);
                            if ($rs_sss && count($rs_sss)) {
                                $ss = $rs_sss->current();
                            } else {
                                $ss = $tb_ss->createRow();
                            }
                            $sss = false;
                            switch ($obj->licencasit) {
                                case 1:
                                    $sss = $tb_sss->getPorChave("PG");
                                    break;
                                case 2:
                                    $sss = $tb_sss->getPorChave("CA");
                                    break;
                            }
                            if ($sss) {
                                $dados = array(
                                    "data_solicitacao" => $obj->licencadt,
                                    "id_servico_transporte_grupo" => $stg->getId(),
                                    "id_servico_solicitacao_status" => $sss->getId(),
                                    "tipo" => "TR",
                                    "chave" => $transporte->getId(),
                                    "data_inicio" => $obj->licencadt,
                                    "data_validade" => Escola_Util::formatData($obj->dtvalidade),
                                    "ano_referencia" => $ano_referencia,
                                    "codigo" => $licenca_numero,
                                    "valor" => "12,00"
                                );
                                $ss->setFromArray($dados);
                                $errors = $ss->getErrors();
                                if (!$errors) {
                                    $ss->save();
                                } else {
                                    Zend_Debug::dump($errors);
                                    die();
                                }
                            }
                        }
                        $this->progresso->progresso("Processando ... " . $obj->licencanum);
                    }
                }
            }
        }
    }

    public function roda_atualiza_motorista()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $db = Zend_Registry::get("db");
        $sql = "select b.cpfcnpj, a.*
                from taxi.motorista a, pessoa b
                where (a.fkpessoa = b.id)";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_pm = new TbPessoaMotorista();
            $tb_cnh_categoria = new TbCnhCategoria();
            $tb_uf = new TbUf();
            $tb_tg = new TbTransporteGrupo();
            $tb_motorista = new TbMotorista();
            $tb_tp = new TbTransportePessoa();
            $tb_tpt = new TbTransportePessoaTipo();
            $tpt = $tb_tpt->getPorChave("MO");
            $tb_tps = new TbTransportePessoaStatus();
            $tps = $tb_tps->getPorChave("A");
            $tb_transporte = new TbTransporte();
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $erro = false;
                $pf = $this->pega_pessoa($obj->cpfcnpj);
                if ($pf) {
                    $db->beginTransaction();
                    try {
                        $pm = $pf->pegaPessoaMotorista();
                        if (!$pm) {
                            $pm = $tb_pm->createRow();
                            $pm->id_pessoa_fisica = $pf->getId();
                        }
                        $cnh_categoria = $tb_cnh_categoria->getPorCodigo(trim($obj->cnhcategoria));
                        if (!$cnh_categoria) {
                            Zend_Debug::dump($obj);
                            Zend_Debug::dump("categoria inv�lida!");
                            die();
                        }
                        $uf = $tb_uf->getPorSigla($obj->cnhuf);
                        if (!$uf) {
                            Zend_Debug::dump("uf inv�lida!");
                            die();
                        }
                        $pm->setFromArray(array(
                            "cnh_numero" => $obj->cnhnumero,
                            "cnh_registro" => $obj->cnhregistro,
                            "cnh_validade" => Escola_Util::formatData($obj->cnhdtvalidade),
                            "id_cnh_categoria" => $cnh_categoria->getId(),
                            "id_uf" => $uf->getId()
                        ));
                        $errors = $pm->getErrors();
                        if (!$errors) {
                            $pm->save();
                            if ($obj->matricula) {
                                $tg = $tb_tg->getPorChave("TX");
                                if ($tg) {
                                    $rs_motorista = $tb_motorista->listar(array(
                                        "filtro_id_transporte_grupo" => $tg->getId(),
                                        "filtro_id_pessoa_motorista" => $pm->getId()
                                    ));
                                    if ($rs_motorista && count($rs_motorista)) {
                                        $motorista = $rs_motorista->current();
                                    } else {
                                        $motorista = $tb_motorista->createRow();
                                        $motorista->id_pessoa_motorista = $pm->getId();
                                        $motorista->id_transporte_grupo = $tg->getId();
                                    }
                                    $motorista->setFromArray(array(
                                        "matricula" => $obj->matricula,
                                        "data_cadastro" => Escola_Util::formatdata($obj->dtcad)
                                    ));
                                    $errors = $motorista->getErrors();
                                    if (!$errors) {
                                        $motorista->save();
                                        if ($tpt && $tps) {
                                            if ($obj->fktaxi) {
                                                $codigo = "RR" . Escola_Util::zero($obj->fktaxi, 5);
                                                $rs_transporte = $tb_transporte->listar(array(
                                                    "filtro_id_transporte_grupo" => $tg->getId(),
                                                    "filtro_codigo" => $codigo
                                                ));
                                                if ($rs_transporte && count($rs_transporte)) {
                                                    $transporte = $rs_transporte->current();
                                                    $dados = array(
                                                        "id_transporte" => $transporte->getId(),
                                                        "id_pessoa" => $pf->id_pessoa
                                                    );
                                                    $rs_tp = $tb_tp->listar($dados);
                                                    if (!($rs_tp && count($rs_tp))) {
                                                        $tp = $tb_tp->createRow();
                                                        $tp->setFromArray(array(
                                                            "id_transporte" => $transporte->getId(),
                                                            "id_pessoa" => $pf->id_pessoa,
                                                            "id_transporte_pessoa_tipo" => $tpt->getId(),
                                                            "id_transporte_pessoa_status" => $tps->getId()
                                                        ));
                                                        $errors = $tp->getErrors();
                                                        if (!$errors) {
                                                            $tp->save();
                                                        } else {
                                                            $erro = true;
                                                            Zend_Debug::dump($errors);
                                                            Zend_Debug::dump("errors transporte pessoa");
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $erro = true;
                                        Zend_Debug::dump($errors);
                                        Zend_Debug::dump("errors motorista");
                                    }
                                }
                            }
                        } else {
                            $erro = true;
                            Zend_Debug::dump($errors);
                            Zend_Debug::dump("erro pessoa motorista");
                        }
                        if ($erro) {
                            Zend_Debug::dump($obj);
                            die();
                        }
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollBack();
                    }
                }
                $this->progresso->progresso("Processando ... " . $obj->cnhnumero);
            }
        }
    }

    public function roda_limpa_motorista_matricula_duplicada()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $sql = "select matricula, count(*) as quant
                from taxi.motorista 
                where (matricula > 0)
                group by matricula
                having count(*) > 1
                order by quant desc";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $sql = "select *
                        from taxi.motorista
                        where (matricula = {$obj->matricula})
                        order by fkpessoa desc";
                $stmt1 = $dbemtu->query($sql);
                if ($stmt1 && $stmt1->rowCount()) {
                    $contador = 0;
                    while ($obj1 = $stmt1->fetch(Zend_Db::FETCH_OBJ)) {
                        $contador++;
                        if ($contador < $stmt1->rowCount()) {
                            $dbemtu->query("update taxi.motorista set matricula = 0 where matricula = {$obj1->matricula} and fkpessoa = {$obj1->fkpessoa}");
                        }
                    }
                }
                $this->progresso->progresso("Processando ... " . $obj->matricula);
            }
        }
    }

    public function roda_atualiza_agente()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $db = Zend_Registry::get("db");
        $sql = "select b.cpfcnpj, b.nome, a.*
                from agente a, pessoa b
                where (a.fkpessoa = b.id)";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_agente = new TbAgente();
            $tb_func = new TbFuncionario();
            $tb_cargo = new TbCargo();
            $cargo = $tb_cargo->getPorDescricao("AGENTE DE TR�NSITO");
            $tb_fs = new TbFuncionarioSituacao();
            $fs = $tb_fs->getPorChave("A");
            $tb_ft = new TbFuncionarioTipo();
            $ft = $tb_ft->getPorChave("EF");
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $erro = false;
                $db->beginTransaction();
                try {
                    if ($cargo) {
                        $pf = $this->pega_pessoa($obj->cpfcnpj);
                        if ($pf) {
                            $funcionario = $tb_func->getPorPessoaFisica($pf);
                            if (!$funcionario) {
                                $funcionario = $tb_func->createRow();
                                $funcionario->set_pessoa_fisica($pf);
                                $funcionario->id_cargo = $cargo->getId();
                                $funcionario->matricula = $obj->matricula;
                                if ($fs) {
                                    $funcionario->id_funcionario_situacao = $fs->getId();
                                }
                                if ($ft) {
                                    $funcionario->id_funcionario_tipo = $ft->getId();
                                }
                                $errors = $funcionario->getErrors(false);
                                if (!$errors) {
                                    $funcionario->save();
                                    $rs_agente = $tb_agente->listar(array("filtro_id_funcionario" => $funcionario->getId()));
                                    if (!($rs_agente && count($rs_agente))) {
                                        $agente = $tb_agente->createRow();
                                    }
                                    $dados = array();
                                    $dados["id_funcionario"] = $funcionario->getId();
                                    $dados["codigo"] = $obj->matricula;
                                    $agente->setFromArray($dados);
                                    $errors = $agente->getErrors();
                                    if (!$errors) {
                                        $agente->save();
                                    } else {
                                        $erro = true;
                                        Zend_Debug::dump($errors);
                                        Zend_Debug::dump("erros agente");
                                    }
                                } else {
                                    $erro = true;
                                    Zend_Debug::dump($errors);
                                    Zend_Debug::dump("erros funcionario");
                                }
                            }
                        } else {
                            $erro = true;
                            Zend_Debug::dump("pf n�o localizada!");
                        }
                        if ($erro) {
                            Zend_Debug::dump($obj);
                        }
                    }
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    die($e->getMessage());
                }
                $this->progresso->progresso("Processando ... " . $obj->cpfcnpj);
            }
        }
    }

    public function roda_validar_pessoa()
    {
        $f = fopen("../application/file/teste.csv", "w+");
        fwrite($f, '"cpfcnpj";"nome";"ci";"ciemissor";' . PHP_EOL);
        $dbemtu = Zend_Registry::get("dbemtu");
        $sql = "select a.*, b.municipio
                from public.pessoa a, public.municipio b
                where (a.fkmunicipio = b.id)
                order by a.nome";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                if (trim($obj->cpfcnpj)) {
                    if (!Escola_Util::validaCPF($obj->cpfcnpj) && !Escola_Util::isCnpjValid($obj->cpfcnpj)) {
                        $linha = array();
                        $linha[] = $obj->cpfcnpj;
                        $linha[] = $obj->nome;
                        $linha[] = $obj->ci;
                        $linha[] = $obj->ciemissor;
                        fwrite($f, '"' . implode('";"', $linha) . '"' . PHP_EOL);
                    }
                }
                $this->progresso->progresso("Progresso ... " . $obj->cpfcnpj);
            }
        }
        fclose($f);
    }

    public function roda_corrige_bairro()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $tb = new TbPessoaFisica();
        $tb_bairro = new TbBairro();
        $sql = "select a.enderecouf, b.municipio, bairro, a.cpfcnpj, a.nome
                from pessoa a, municipio b
                where (a.fkmunicipio = b.id)
                order by a.fkmunicipio, a.bairro";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $obj->bairro = trim($obj->bairro);
                if (!$obj->bairro) {
                    $obj->bairro = "CENTRO";
                }
                $filho = $this->pega_pessoa($obj->cpfcnpj);
                if ($filho) {
                    $pessoa = $filho->findParentRow("TbPessoa");
                    if ($pessoa) {
                        $endereco = $pessoa->getEndereco();
                        $sql = $tb_bairro->select();
                        $sql->from(array("b" => "bairro"));
                        $sql->join(array("m" => "municipio"), "b.id_municipio = m.id_municipio", array());
                        $sql->join(array("u" => "uf"), "u.id_uf = m.id_uf", array());
                        $sql->where("b.descricao = '{$obj->bairro}'");
                        $sql->where("m.descricao = '{$obj->municipio}'");
                        $sql->where("u.sigla = '{$obj->enderecouf}'");
                        $sql->order("b.id_bairro");
                        $stmt_bairro = $tb_bairro->fetchAll($sql);
                        if ($stmt_bairro && count($stmt_bairro)) {
                            $bairro = $stmt_bairro->current();
                            if ($bairro->getId() != $endereco->id_bairro) {
                                Zend_Debug::dump($bairro->toArray());
                                Zend_Debug::dump($endereco->toArray());
                                Zend_Debug::dump($obj);
                                Zend_Debug::dump($pessoa->toArray());

                                $endereco->id_bairro = $bairro->getId();
                                $endereco->save();

                                //die();
                            }
                        }
                    }
                }
                $this->progresso->progresso("Progresso ... " . $obj->nome);
            }
        }
    }

    public function roda_atualiza_transporte_dtce()
    {
        $session = Escola_Session::getInstance();
        $dbemtu = Zend_Registry::get("dbemtu");
        $db = Zend_Registry::get("db");
        //$sql = "select * from dtce.dtce order by numdtce";
        $sql = "select * from dtce.dtce where dtcad >= '2013-11-1' order by dtatu desc";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_transporte = new TbTransporte();
            $tb_tg = new TbTransporteGrupo();
            $tb_concessao = new TbConcessao();
            $tb_concessao_tipo = new TbConcessaoTipo();
            $tb_concessao_validade = new TbConcessaoValidade();
            $tb_transporte_pessoa = new TbTransportePessoa();
            $tb_transporte_pessoa_tipo = new TbTransportePessoaTipo();
            $pt_proprietario = $tb_transporte_pessoa_tipo->getPorChave("PR");
            $tb_transporte_pessoa_status = new TbTransportePessoaStatus();
            $ps_proprietario = $tb_transporte_pessoa_status->getPorChave("A");
            $cv = $tb_concessao_validade->getPorChave("I");
            $tg = $tb_tg->getPorChave("DT");
            $tb_taxi = new TbTaxi();
            $tb_veiculo = new TbVeiculo();
            $tb_tv = new TbTransporteVeiculo();
            $tb_tvs = new TbTransporteVeiculoStatus();
            $tb_baixa = new TbTransporteVeiculoBaixa();
            $tb_bm = new TbBaixaMotivo();
            if ($tg) {
                while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                    $db->beginTransaction();
                    try {
                        $codigo = $obj->numdtce;
                        $transportes = $tb_transporte->listar(array("filtro_id_transporte_grupo" => $tg->getId(), "filtro_codigo" => $codigo));
                        if ($transportes) {
                            $transporte = $transportes->current();
                        } else {
                            $transporte = $tb_transporte->createRow();
                            $transporte->id_transporte_grupo = $tg->getId();
                        }
                        $concessao = $transporte->findParentRow("TbConcessao");
                        $sql = "select b.cpfcnpj, a.*
                                from dtce.concessao a, public.pessoa b
                                where (a.fkpessoa = b.id)
                                and (a.fkdtce = {$obj->numdtce})";
                        $stmt1 = $dbemtu->query($sql);
                        if ($stmt1 && $stmt1->rowCount()) {
                            if (!$concessao) {
                                $concessao = $tb_concessao->createRow();
                            }
                            $obj_concessao = $stmt1->fetch(Zend_Db::FETCH_OBJ);
                            $cdt = false;
                            $filter = new Zend_Validate_Date();
                            $dados = array();
                            /*
                            if ($filter->isValid($obj_concessao->concessaodt)) {
                                $cdt = new Zend_Date($obj_concessao->concessaodt);
                            }
                            $dados["concessao_data"] = Escola_Util::formatData($obj_concessao->concessaodt);
                             */
                            $dados["concessao_data"] = Escola_Util::formatData($obj_concessao->dtcad);
                            if ($obj_concessao->concessaonum) {
                                $dados["numero"] = $obj_concessao->concessaonum;
                            } else {
                                $dados["numero"] = "S/N";
                            }
                            $dados["decreto"] = $obj_concessao->concessaodecreto;
                            $dados["processo_numero"] = $obj_concessao->concessaodecreto;
                            if ($cdt) {
                                $dados["processo_ano"] = $cdt->toString("YYYY");;
                            }
                            $ct = $tb_concessao_tipo->getPorChave("P");
                            if ($ct) {
                                $dados["id_concessao_tipo"] = $ct->getId();
                            }
                            if ($cv) {
                                $dados["id_concessao_validade"] = $cv->getId();
                            }
                            $concessao->setFromArray($dados);
                            $errors = $concessao->getErrors();
                            if (!$errors) {
                                $concessao->save();
                                $transporte->set_concessao($concessao);
                            } else {
                                Zend_Debug::dump($obj_concessao);
                                Zend_Debug::dump($errors);
                                Zend_Debug::dump("erros d concessao");
                                die();
                            }
                            $dtce = $transporte->getTransporteInstancia();
                            $dados = array("id_transporte_grupo" => $tg->getId(), "codigo" => $codigo, "id_transporte" => $transporte->id_transporte);
                            $transporte->setFromArray($dados);
                            $errors = $transporte->getErrors();
                            if (!$errors) {
                                $transporte->save();
                                $pessoa = $this->pega_pessoa($obj_concessao->cpfcnpj);
                                if ($pessoa) {
                                    $proprietario = $transporte->pegaProprietario();
                                    if (!$proprietario) {
                                        $proprietario = $tb_transporte_pessoa->createRow();
                                    }
                                    $proprietario->setFromArray(array(
                                        "id_transporte" => $transporte->getId(),
                                        "id_pessoa" => $pessoa->id_pessoa,
                                        "id_transporte_pessoa_tipo" => $pt_proprietario->getId(),
                                        "id_transporte_pessoa_status" => $ps_proprietario->getId()
                                    ));
                                    $errors = $proprietario->getErrors();
                                    if (!$errors) {
                                        $proprietario->save();
                                    }
                                }
                                $sql = "select b.chassi, b.placa, a.*
                                        from dtce.veiculo a, public.veiculo b
                                        where (a.fkveiculo = b.id)
                                        and (a.fkdtce = {$obj->numdtce})
                                        order by dtcad";
                                $stmt2 = $dbemtu->query($sql);
                                if ($stmt2 && $stmt2->rowCount()) {
                                    while ($obj_veiculo = $stmt2->fetch(Zend_Db::FETCH_OBJ)) {
                                        $veiculo = false;
                                        if ($obj_veiculo->chassi) {
                                            $veiculo = $tb_veiculo->getPorChassi($obj_veiculo->chassi);
                                        } elseif ($obj_veiculo->placa) {
                                            $veiculo = $tb_veiculo->getPorPlaca($obj_veiculo->placa);
                                        }
                                        if ($veiculo) {
                                            $tvs = $tb_tv->listar(array(
                                                "id_transporte" => $transporte->getId(),
                                                "id_veiculo" => $veiculo->getId()
                                            ));
                                            if ($tvs) {
                                                $tv = $tvs->current();
                                            } else {
                                                $tv = $tb_tv->createRow();
                                                $tv->id_transporte = $transporte->getId();
                                                $tv->id_veiculo = $veiculo->getId();
                                            }
                                            $dados = array();
                                            $sits = array("A", "B", "I");
                                            if ($obj_veiculo->situacao) {
                                                $tvs = $tb_tvs->getPorChave($sits[$obj_veiculo->situacao - 1]);
                                                if ($tvs) {
                                                    $dados["id_transporte_veiculo_status"] = $tvs->getId();
                                                }
                                            }
                                            if (Escola_Util::validaData($obj_veiculo->dtcadastro)) {
                                                $dados["data_cadastro"] = $obj_veiculo->dtcadastro;
                                            }
                                            /*
                                            if ($obj_veiculo->fkprotocolo) {
                                                $dados["processo"] = $obj_veiculo->fkprotocolo;
                                            }
                                            if (Escola_Util::validaData($obj_veiculo->fkprotocolodt)) {
                                                $dados["processo_data"] = $obj_veiculo->fkprotocolodt;
                                            }
                                             */
                                            $tv->setFromArray($dados);
                                            $errors = $tv->getErrors();
                                            if (!$errors) {
                                                $tv->save();
                                            } else {
                                                Zend_Debug::dump($errors);
                                            }
                                        }
                                    }
                                }
                            } else {
                                Zend_Debug::dump($errors);
                                die();
                            }
                        }
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollBack();
                        echo $e->getMessage();
                        echo "\n" . $e->getFile();
                        echo "\n" . $e->getLine();
                        die();
                    }
                    $this->progresso->progresso("Atualizando Transporte > Taxi ... " . $obj->numdtce);
                }
            }
        }
    }

    public function roda_atualiza_dtce_licenca()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $tb_tg = new TbTransporteGrupo();
        $tg = $tb_tg->getPorChave("DTCE");
        $tb_servico = new TbServico();
        $tb_stg = new TbServicoTransporteGrupo();
        $servico = $tb_servico->getPorCodigo("TU");
        $tb_sss = new TbServicoSolicitacaoStatus();
        $tb_ss = new TbServicoSolicitacao();
        $tb_transporte = new TbTransporte();
        if ($tg && $servico) {
            $rs = $tb_stg->listar(array("id_transporte_grupo" => $tg->getId(), "id_servico" => $servico->getId()));
            if ($rs && count($rs)) {
                $stg = $rs->current();
                $sql = "select * from dtce.licenca order by id";
                $stmt = $dbemtu->query($sql);
                if ($stmt && $stmt->rowCount()) {
                    $this->progresso->set_progresso(0);
                    $this->progresso->set_valor_total($stmt->rowCount());
                    while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                        Zend_Debug::dump($obj);
                        $codigo = $obj->fkdtce;
                        $ano_referencia = substr($obj->licencanum, 0, 4);
                        $licenca_numero = (int) substr($obj->licencanum, 4, 6);
                        $transportes = $tb_transporte->listar(array(
                            "filtro_id_transporte_grupo" => $tg->getId(),
                            "filtro_codigo" => $codigo
                        ));
                        if ($transportes && count($transportes)) {
                            $transporte = $transportes->current();
                            $dados = array(
                                "id_transporte" => $transporte->getId(),
                                "id_servico_transporte_grupo" => $stg->getId(),
                                "ano_referencia" => $ano_referencia,
                                "codigo" => $licenca_numero
                            );
                            $rs_sss = $tb_ss->listar($dados);
                            if ($rs_sss && count($rs_sss)) {
                                $ss = $rs_sss->current();
                            } else {
                                $ss = $tb_ss->createRow();
                            }
                            $sss = false;
                            switch ($obj->licencasit) {
                                case 1:
                                    $sss = $tb_sss->getPorChave("PG");
                                    break;
                                case 2:
                                    $sss = $tb_sss->getPorChave("CA");
                                    break;
                            }
                            if ($sss) {
                                $dados = array(
                                    "data_solicitacao" => $obj->licencadt,
                                    "id_servico_transporte_grupo" => $stg->getId(),
                                    "id_servico_solicitacao_status" => $sss->getId(),
                                    "tipo" => "TR",
                                    "chave" => $transporte->getId(),
                                    "data_inicio" => $obj->licencadt,
                                    "data_validade" => Escola_Util::formatData($obj->dtvalidade),
                                    "ano_referencia" => $ano_referencia,
                                    "codigo" => $licenca_numero,
                                    "valor" => "12,00"
                                );
                                $ss->setFromArray($dados);
                                $errors = $ss->getErrors();
                                if (!$errors) {
                                    $ss->save();
                                } else {
                                    Zend_Debug::dump($errors);
                                    die();
                                }
                            }
                        }
                        $this->progresso->progresso("Processando ... " . $obj->licencanum);
                    }
                }
            }
        }
    }

    public function roda_atualiza_dtce_motorista()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $db = Zend_Registry::get("db");
        $sql = "select b.cpfcnpj, a.*
                from dtce.motorista a, pessoa b
                where (a.fkpessoa = b.id)";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_pm = new TbPessoaMotorista();
            $tb_cnh_categoria = new TbCnhCategoria();
            $tb_uf = new TbUf();
            $tb_tg = new TbTransporteGrupo();
            $tb_motorista = new TbMotorista();
            $tb_tp = new TbTransportePessoa();
            $tb_tpt = new TbTransportePessoaTipo();
            $tpt = $tb_tpt->getPorChave("MO");
            $tb_tps = new TbTransportePessoaStatus();
            $tps = $tb_tps->getPorChave("A");
            $tb_transporte = new TbTransporte();
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $erro = false;
                $pf = $this->pega_pessoa($obj->cpfcnpj);
                if ($pf) {
                    $db->beginTransaction();
                    try {
                        $pm = $pf->pegaPessoaMotorista();
                        if (!$pm) {
                            $pm = $tb_pm->createRow();
                            $pm->id_pessoa_fisica = $pf->getId();
                        }
                        $cnh_categoria = $tb_cnh_categoria->getPorCodigo(trim($obj->cnhcategoria));
                        if (!$cnh_categoria) {
                            Zend_Debug::dump($obj);
                            Zend_Debug::dump("categoria inv�lida!");
                            die();
                        }
                        $uf = $tb_uf->getPorSigla($obj->cnhuf);
                        if (!$uf) {
                            Zend_Debug::dump("uf inv�lida!");
                            die();
                        }
                        $pm->setFromArray(array(
                            "cnh_numero" => $obj->cnhnumero,
                            "cnh_registro" => $obj->cnhregistro,
                            "cnh_validade" => Escola_Util::formatData($obj->cnhdtvalidade),
                            "id_cnh_categoria" => $cnh_categoria->getId(),
                            "id_uf" => $uf->getId()
                        ));
                        $errors = $pm->getErrors();
                        if (!$errors) {
                            $pm->save();
                            if ($obj->matricula) {
                                $tg = $tb_tg->getPorChave("DTCE");
                                if ($tg) {
                                    $rs_motorista = $tb_motorista->listar(array(
                                        "filtro_id_transporte_grupo" => $tg->getId(),
                                        "filtro_id_pessoa_motorista" => $pm->getId()
                                    ));
                                    if ($rs_motorista && count($rs_motorista)) {
                                        $motorista = $rs_motorista->current();
                                    } else {
                                        $motorista = $tb_motorista->createRow();
                                        $motorista->id_pessoa_motorista = $pm->getId();
                                        $motorista->id_transporte_grupo = $tg->getId();
                                    }
                                    $motorista->setFromArray(array(
                                        "matricula" => $obj->matricula,
                                        "data_cadastro" => Escola_Util::formatdata($obj->dtcad)
                                    ));
                                    $errors = $motorista->getErrors();
                                    if (!$errors) {
                                        $motorista->save();
                                        if ($tpt && $tps) {
                                            if ($obj->fkdtce) {
                                                $codigo = $obj->fkdtce;
                                                $rs_transporte = $tb_transporte->listar(array(
                                                    "filtro_id_transporte_grupo" => $tg->getId(),
                                                    "filtro_codigo" => $codigo
                                                ));
                                                if ($rs_transporte && count($rs_transporte)) {
                                                    $transporte = $rs_transporte->current();
                                                    $dados = array(
                                                        "id_transporte" => $transporte->getId(),
                                                        "id_pessoa" => $pf->id_pessoa,
                                                        "id_transporte_pessoa_tipo" => $tpt->getId()
                                                    );
                                                    $rs_tp = $tb_tp->listar($dados);
                                                    if (!($rs_tp && count($rs_tp))) {
                                                        $tp = $tb_tp->createRow();
                                                        $tp->setFromArray(array(
                                                            "id_transporte" => $transporte->getId(),
                                                            "id_pessoa" => $pf->id_pessoa,
                                                            "id_transporte_pessoa_tipo" => $tpt->getId(),
                                                            "id_transporte_pessoa_status" => $tps->getId()
                                                        ));
                                                        $errors = $tp->getErrors();
                                                        if (!$errors) {
                                                            $tp->save();
                                                        } else {
                                                            $erro = true;
                                                            Zend_Debug::dump($errors);
                                                            Zend_Debug::dump("errors transporte pessoa");
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $erro = true;
                                        Zend_Debug::dump($errors);
                                        Zend_Debug::dump("errors motorista");
                                    }
                                }
                            }
                        } else {
                            $erro = true;
                            Zend_Debug::dump($errors);
                            Zend_Debug::dump("erro pessoa motorista");
                        }
                        if ($erro) {
                            Zend_Debug::dump($obj);
                            die();
                        }
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollBack();
                    }
                }
                $this->progresso->progresso("Processando ... " . $obj->cnhnumero);
            }
        }
    }

    public function roda_atualiza_dtce_ajudante()
    {
        $dbemtu = Zend_Registry::get("dbemtu");
        $db = Zend_Registry::get("db");
        $sql = "select b.cpfcnpj, a.*
                from dtce.ajudante a, pessoa b
                where (a.fkpessoa = b.id)";
        $stmt = $dbemtu->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total($stmt->rowCount());
            $tb_tg = new TbTransporteGrupo();
            $tg = $tb_tg->getPorChave("DTCE");
            $tb_tp = new TbTransportePessoa();
            $tb_tpt = new TbTransportePessoaTipo();
            $tpt = $tb_tpt->getPorChave("AU");
            $tb_tps = new TbTransportePessoaStatus();
            $tps = $tb_tps->getPorChave("A");
            $tb_transporte = new TbTransporte();
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $erro = false;
                $pf = $this->pega_pessoa($obj->cpfcnpj);
                if ($pf) {
                    $db->beginTransaction();
                    try {
                        if ($tpt && $tps) {
                            if ($obj->fkdtce) {
                                $codigo = $obj->fkdtce;
                                $rs_transporte = $tb_transporte->listar(array(
                                    "filtro_id_transporte_grupo" => $tg->getId(),
                                    "filtro_codigo" => $codigo
                                ));
                                if ($rs_transporte && count($rs_transporte)) {
                                    $transporte = $rs_transporte->current();
                                    $dados = array(
                                        "id_transporte" => $transporte->getId(),
                                        "id_pessoa" => $pf->id_pessoa,
                                        "id_transporte_pessoa_tipo" => $tpt->getId()
                                    );
                                    $rs_tp = $tb_tp->listar($dados);
                                    if (!($rs_tp && count($rs_tp))) {
                                        $tp = $tb_tp->createRow();
                                        $tp->setFromArray(array(
                                            "id_transporte" => $transporte->getId(),
                                            "id_pessoa" => $pf->id_pessoa,
                                            "id_transporte_pessoa_tipo" => $tpt->getId(),
                                            "id_transporte_pessoa_status" => $tps->getId()
                                        ));
                                        $errors = $tp->getErrors();
                                        if (!$errors) {
                                            $tp->save();
                                        } else {
                                            $erro = true;
                                            Zend_Debug::dump($errors);
                                            Zend_Debug::dump("errors transporte pessoa");
                                        }
                                    }
                                }
                            }
                        }
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollBack();
                    }
                }
                $this->progresso->progresso("Processando ... " . $obj->cpfcnpj);
            }
        }
    }

    public function roda_relatorio_dtce()
    {
        $f = fopen("/var/www/intranet/intranet/application/file/relatorio_dtce.csv", "w+");
        fwrite($f, '"ID";"C�DIGO";"CONCESS�O N�MERO";"NOME PROPRIETARIO";"PLACA VE�CULO";"CATEGORIA";"NOVO GRUPO DE TRANSPORTE"' . PHP_EOL);
        $tb = new TbTransporteGrupo();
        $tg = $tb->getPorChave("DTCE");
        $tb = new TbTransporte();
        $transportes = $tb->listar(array("filtro_id_transporte_grupo" => $tg->getId()));
        if ($transportes && count($transportes)) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total(count($transportes));
            foreach ($transportes as $transporte) {
                $id = $codigo = $concessao = $nome = $placa = $categoria = "";
                $id = $transporte->getId();
                $codigo = $transporte->codigo;
                $tc = $transporte->findParentRow("TbConcessao");
                if ($tc) {
                    $concessao = $tc->numero;
                }
                $proprietario = $transporte->pegaProprietario();
                if ($proprietario) {
                    $nome = $proprietario->toString();
                }
                $tvs = $transporte->pegaTransporteVeiculoAtivos();
                if ($tvs) {
                    $placas = array();
                    foreach ($tvs as $tv) {
                        $veiculo = $tv->findParentRow("TbVeiculo");
                        if ($veiculo) {
                            $placas[] = $veiculo->placa;
                            /*
                            $vc = $veiculo->findParentRow("TbVeiculoCategoria");
                            if ($vc) {
                                $categoria = $vc->toString();
                            }
                             */
                        }
                    }
                }
                $fields = array();
                $fields[] = $id;
                $fields[] = $codigo;
                $fields[] = $concessao;
                $fields[] = $nome;
                $fields[] = $placa;
                $fields[] = $categoria;
                fwrite($f, '"' . implode('";"', $fields) . '"' . PHP_EOL);
                $this->progresso->progresso("Processando ... " . $placa);
            }
        }
        fclose($f);
    }

    public function roda_atualiza_transporte_grupo_dtce()
    {
        $tb = new TbTransporte();
        $transportes = $tb->listar();
        if ($transportes && count($transportes)) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total(count($transportes));
            foreach ($transportes as $transporte) {
                $transporte->save();
                $this->progresso->progresso("Processando " . $transporte->toString());
            }
        }
    }

    public function roda_carteira_unimulher()
    {
        $linhas = Escola_Util::carregaArquivoDados($this->nome_arquivo->get_text());
        if ($linhas) {
            $this->progresso->set_progresso(0);
            $this->progresso->set_valor_total(count($linhas));
            $rel = new Escola_Relatorio_Unimulher();
            $rel->toPDF($linhas);
        }
    }
}

$jan = new JanelaProgresso();
