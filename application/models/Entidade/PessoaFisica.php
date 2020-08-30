<?php
class PessoaFisica extends Escola_Entidade
{

    protected $_pessoa;

    public function init()
    {
        parent::init();
        $this->_pessoa = $this->getPessoa();
        if (!$this->id_estado_civil) {
            $tb = new TbEstadoCivil();
            $ec = $tb->getPorDescricao("INDEFINIDO");
            if ($ec) {
                $this->id_estado_civil = $ec->getId();
            }
        }
        if (!$this->nascimento_id_municipio) {
            $tb = new TbMunicipio();
            $muns = $tb->listar(array("descricao" => "MACAPA"));
            if ($muns) {
                $this->nascimento_id_municipio = $muns->current()->id_municipio;
            }
        }
        if (!$this->identidade_id_uf) {
            $tb = new TbUf();
            $uf = $tb->getPorSigla("AP");
            if ($uf) {
                $this->identidade_id_uf = $uf->getId();
            }
        }
    }

    public function pega_pessoa()
    {
        return $this->_pessoa;
    }

    public function setFromArray(array $dados)
    {

        $maiuscula = new Zend_Filter_StringToUpper();

        if (isset($dados["cpf"])) {
            $filter = new Zend_Filter_Digits();
            $dados["cpf"] = $filter->filter($dados["cpf"]);
            // $tb = new TbPessoaFisica();
            // $rs = $tb->fetchAll("cpf = '{$dados["cpf"]}'");
            // if ($rs && count($rs)) {
            //     $pf = $rs->current();
            //     $this->id_pessoa_fisica = $pf->getId();
            //     $this->refresh();
            // }
        }

        if (isset($dados["nome"])) {
            $dados["nome"] = $maiuscula->filter($dados["nome"]);
        }
        if (isset($dados["nome_pai"])) {
            $dados["nome_pai"] = $maiuscula->filter($dados["nome_pai"]);
        }
        if (isset($dados["nome_mae"])) {
            $dados["nome_mae"] = $maiuscula->filter($dados["nome_mae"]);
        }
        if (isset($dados["identidade_numero"])) {
            $dados["identidade_numero"] = $maiuscula->filter($dados["identidade_numero"]);
        }
        if (isset($dados["identidade_orgao_expedidor"])) {
            $dados["identidade_orgao_expedidor"] = $maiuscula->filter($dados["identidade_orgao_expedidor"]);
        }
        if (isset($dados["id_pessoa"]) && $dados["id_pessoa"]) {
            $tb_pessoa = new TbPessoa();
            $this->_pessoa = $tb_pessoa->getPorId($dados["id_pessoa"]);
        }

        $this->_pessoa->setFromArray($dados);

        unset($dados["id_pessoa_tipo"]);
        unset($dados["email"]);
        unset($dados["id"]);

        parent::setFromArray($dados);
    }

    public function getPessoa()
    {
        $pessoa = $this->findParentRow("TbPessoa");
        if ($pessoa) {
            return $pessoa;
        }
        if ($this->_pessoa) {
            return $this->_pessoa;
        }
        $tb = new TbPessoa();
        $row = $tb->createRow();
        $tb = new TbPessoaTipo();
        $pt = $tb->getPorChave("PF");
        if ($pt) {
            $row->id_pessoa_tipo = $pt->id_pessoa_tipo;
        }
        return $row;
    }

    public function save()
    {
        $this->id_pessoa = $this->_pessoa->save();
        $date = new Zend_Date($this->data_nascimento);
        $this->data_nascimento = $date->get("Y-MM-dd");
        return parent::save();
    }

    public function getErrors($flag = true)
    {
        $msgs = array();
        $val = new Escola_Validate_Cpf();
        if (!$val->isValid($this->cpf)) {
            $msgs[] = "CAMPO CPF INVÁLIDO!";
        }
        if (empty($this->nome)) {
            $msgs[] = "CAMPO NOME OBRIGATÓRIO!";
        }
        if (empty($this->nome_mae)) {
            $msgs[] = "CAMPO NOME DA MÃE OBRIGATÓRIO!";
        }
        if ($flag) {
            if (empty($this->data_nascimento)) {
                $msgs[] = "CAMPO DATA DE NASCIMENTO INVÁLIDO!";
            } else {
                $date = new Zend_Date($this->data_nascimento);
                $val = new Zend_Validate_Date();
                if (!$val->isValid($date->get("yyyy-MM-dd"))) {
                    $msgs[] = "CAMPO DATA DE NASCIMENTO INVÁLIDO!";
                }
            }
            if (empty($this->nascimento_id_municipio)) {
                $msgs[] = "CAMPO MUNICÍPIO DE NASCIMENTO OBRIGATÓRIO!";
            }
            if (empty($this->identidade_numero)) {
                $msgs[] = "CAMPO NÚMERO DE IDENTIDADE OBRIGATÓRIO!";
            }
            if (empty($this->identidade_orgao_expedidor)) {
                $msgs[] = "CAMPO ÓRGÃO EXPEDIDOR OBRIGATÓRIO!";
            }
            if (empty($this->identidade_id_uf)) {
                $msgs[] = "CAMPO UF DA IDENTIDADE OBRIGATÓRIO!";
            }
            if (!$this->id_estado_civil) {
                $msgs[] = "CAMPO ESTADO CIVIL OBRIGATÓRIO!";
            }
        }
        if (!count($msgs)) {
            if ($this->_pessoa->getId()) {
                $id = "0";
                if ($this->getId()) {
                    $id = $this->getId();
                }
                $tb = new TbPessoaFisica();
                $sql = $tb->select();
                $sql->where(" cpf = '{$this->cpf}' ");
                $sql->where(" id_pessoa_fisica <> {$id} ");
                $objs = $tb->fetchAll($sql);
                if (count($objs)) {
                    $msgs[] = "CPF JÁ CADASTRADO PARA OUTRA PESSOA FÍSICA!!";
                }

                $tb = new TbPessoaFisica();
                $sql = $tb->select();
                $sql->where("id_pessoa = {$this->_pessoa->getId()}");
                $sql->where("id_pessoa_fisica <> {$id}");
                $objs = $tb->fetchAll($sql);
                if (count($objs)) {
                    $msgs[] = "FALHA AO EXECUTAR OPERAÇÃO, DADOS DE PESSOA INVÁLIDO!!";
                }
            }
        }
        $err = $this->_pessoa->getErrors($flag);
        if ($err) {
            $msgs = array_merge($msgs, $err);
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function mostrarDataNascimento()
    {
        $date = new Zend_Date($this->data_nascimento);
        return $date->toString("dd/MM/Y");
    }

    public function toString()
    {
        //return Escola_Util::formatCPF($this->cpf) . " - " . $this->nome;
        return $this->nome;
    }

    public function pegaIdentidadeUf()
    {
        $tb = new TbUf();
        $uf = $tb->getPorId($this->identidade_id_uf);
        if ($uf) {
            return $uf;
        }
        return false;
    }

    public function getFoto()
    {
        $p = $this->pega_pessoa();
        if (!$p) {
            return null;
        }
        $foto = $p->getFoto();
        if (!($foto && $foto->existe())) {
            return null;
        }

        return $foto;
    }

    public function mostrarFoto($tamanho = 80, $align = "right", $link = false)
    {
        $foto = $this->getFoto();
        if ($foto && $foto->existe()) {
            return $foto->miniatura(array("width" => $tamanho, "align" => $align, "link" => $link));
        }
    }

    public function mostrar_documento()
    {
        return Escola_Util::formatCpf($this->cpf);
    }

    public function mostrar_nome()
    {
        return $this->nome;
    }

    public function mostrar_identidade()
    {
        if (empty($this->identidade_numero)) {
            return "";
        }

        $items = array();
        $items[] = $this->identidade_numero;
        if ($this->identidade_orgao_expedidor) {
            $items[] = $this->identidade_orgao_expedidor;
        }
        $uf = $this->pegaIdentidadeUf();
        if ($uf) {
            $items[] = $uf->sigla;
        }
        return implode(" - ", $items);
    }

    public function view()
    {
        $pessoa = $this->findParentRow("TbPessoa");
        $pessoa_motorista = $this->pegaPessoaMotorista();
        ob_start();
?>
        <dl class="dl-horizontal">
            <dt>C.P.F.:</dt>
            <dd><?php echo Escola_Util::formatCpf($this->cpf); ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Nome:</dt>
            <dd><?php echo $this->nome; ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>E-mail:</dt>
            <dd><?php echo $pessoa->email; ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Data de Nascimento:</dt>
            <dd><?php echo Escola_Util::formatData($this->data_nascimento); ?></dd>
        </dl>
        <?php
        $txt = "--";
        $mun = $this->findParentRow("TbMunicipio");
        if ($mun) {
            $txt = $mun->toString() . " - " . $mun->findParentRow("TbUf")->sigla;
        }
        ?>
        <dl class="dl-horizontal">
            <dt>Município Nascimento:</dt>
            <dd><?php echo $txt; ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Estado Civil:</dt>
            <dd><?php echo $this->findParentRow("TbEstadoCivil")->toString(); ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Identidade:</dt>
            <dd><?php echo $this->mostrar_identidade(); ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Pis/Pasep:</dt>
            <dd><?php echo $this->pis_pasep; ?></dd>
        </dl>
        <?php if ($pessoa_motorista) { ?>
            <dl class="dl-horizontal">
                <dt>CNH:</dt>
                <dd><?php echo $pessoa_motorista->toString(); ?></dd>
            </dl>
        <?php } ?>
        <?php if ($pessoa) { ?>
            <dl class="dl-horizontal">
                <dt>Telefones:</dt>
                <dd><?php echo $pessoa->mostrarTelefones(); ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt>Endereço:</dt>
                <dd><?php echo $pessoa->mostrar_endereco(); ?></dd>
            </dl>
            <dl class="dl-horizontal">
                <dt>Informações Bancárias:</dt>
                <dd><?php echo $pessoa->mostrar_info_bancaria(); ?></dd>
            </dl>
        <?php } ?>
    <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render(Zend_View_Interface $view)
    {
        $id_uf = $id_pais = 0;
        $municipio = $this->findParentRow("TbMunicipio");
        if ($municipio) {
            $id_uf = $municipio->id_uf;
            $uf = $municipio->findParentRow("TbUf");
            if ($uf) {
                $id_pais = $uf->id_pais;
            }
        }
        ob_start();
    ?>
        <dl class="dl-horizontal">
            <dt>C.P.F.:</dt>
            <dd><?php echo Escola_Util::formatCpf($this->cpf); ?></dd>
        </dl>
        <div class="control-group">
            <label for="nome" class="control-label">Nome:</label>
            <div class="controls">
                <input type="text" name="nome" id="nome" class="span5 nome" value="<?php echo $this->nome; ?>" />
            </div>
        </div>
        <div class="control-group">
            <label for="data_nascimento" class="control-label">Data de Nascimento:</label>
            <div class="controls">
                <input type="text" name="data_nascimento" id="data_nascimento" class="span2 data" value="<?php echo Escola_Util::formatData($this->data_nascimento); ?>" />
            </div>
        </div>
        <div class="control-group">
            <label for="nome_pai" class="control-label">Nome do Pai:</label>
            <div class="controls">
                <input type="text" name="nome_pai" id="nome_pai" class="span5 nome" value="<?php echo $this->nome_pai; ?>" />
            </div>
        </div>
        <div class="control-group">
            <label for="nome_mae" class="control-label">Nome da Mãe:</label>
            <div class="controls">
                <input type="text" name="nome_mae" id="nome_mae" class="span5 nome" value="<?php echo $this->nome_mae; ?>" />
            </div>
        </div>
        <?php
        $ctrl = new Escola_Form_Element_Select_Table_Crud_Estadocivil("id_estado_civil");
        $ctrl->setPkName("id_estado_civil");
        $ctrl->setModel("TbEstadoCivil");
        $ctrl->setValue($this->id_estado_civil);
        $ctrl->setLabel("Estado Civil: ");
        echo $ctrl->render($view);

        $ctrl = new Escola_Form_Element_Select_Table_Crud_Pais("id_pais");
        $ctrl->setPkName("id_pais");
        $ctrl->setModel("TbPais");
        $ctrl->setLabel("País: ");
        $ctrl->setValue($id_pais);
        echo $ctrl->render($view);

        $ctrl = new Escola_Form_Element_Select_Table_Crud_Uf("id_uf");
        $ctrl->setPkName("id_uf");
        $ctrl->setModel("TbUf");
        $ctrl->setValue($id_uf);
        $ctrl->setLabel("Unidade Federativa: ");
        $ctrl->set_id_pais("id_pais");
        echo $ctrl->render($view);

        $ctrl = new Escola_Form_Element_Select_Table_Crud_Municipio("nascimento_id_municipio");
        $ctrl->setPkName("id_municipio");
        $ctrl->setModel("TbMunicipio");
        $ctrl->setValue($this->nascimento_id_municipio);
        $ctrl->setLabel("Município: ");
        $ctrl->set_id_uf("id_uf");
        echo $ctrl->render($view);
        ?>
        <div class="control-group">
            <label for="identidade_numero" class="control-label">Identidade - Número:</label>
            <div class="controls">
                <input type="text" name="identidade_numero" id="identidade_numero" class="span2" value="<?php echo $this->identidade_numero; ?>" size="30" />
            </div>
        </div>
        <div class="control-group">
            <label for="identidade_orgao_expedidor" class="control-label">Identidade - Órgão Emissor:</label>
            <div class="controls">
                <input type="text" name="identidade_orgao_expedidor" id="identidade_orgao_expedidor" class="span2" value="<?php echo $this->identidade_orgao_expedidor; ?>" size="20" />
            </div>
        </div>
        <?php
        $ctrl = new Escola_Form_Element_Select_Table_Crud_Uf("identidade_id_uf");
        $ctrl->setPkName("id_uf");
        $ctrl->setModel("TbUf");
        $ctrl->setValue($this->identidade_id_uf);
        $ctrl->setLabel("Identidade - UF: ");
        echo $ctrl->render($view);
        ?>
        <div class="control-group">
            <label for="pis_pasep" class="control-label">PIS/PASEP:</label>
            <div class="controls">
                <input type="text" name="pis_pasep" id="pis_pasep" class="span2" value="<?php echo $this->pis_pasep; ?>" size="11" />
            </div>
        </div>
    <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function pegaPessoaMotorista()
    {
        $id = $this->getId();

        if (!$id) {
            return false;
        }

        $tb = new TbPessoaMotorista();
        $sql = $tb->select();
        $sql->where("id_pessoa_fisica = {$this->getId()}");
        $rs = $tb->fetchAll($sql);
        if ($rs && count($rs)) {
            return $rs->current();
        }
    }

    public function pegaMotorista()
    {
        if (!$this->getId()) {
            return null;
        }

        $tb = new TbMotorista();
        $sql = $tb->select();
        $sql->from(array("m" => "motorista"));
        $sql->join(array("pm" => "pessoa_motorista"), "m.id_pessoa_motorista = pm.id_pessoa_motorista", array());
        $sql->join(array("pf" => "pessoa_fisica"), "pf.id_pessoa_fisica = pm.id_pessoa_fisica", array());
        $sql->where("pf.id_pessoa_fisica = ?", $this->getId());
        $rs = $tb->fetchAll($sql);
        if ($rs && count($rs)) {
            return $rs->current();
        }

        return null;
    }

    public function pegaAutoInfracaoNotificacao()
    {
        $tb = new TbAutoInfracaoNotificacao();
        $rs = $tb->listar(array("id_pessoa_fisica" => $this->getId()));
        if ($rs && count($rs)) {
            return $rs;
        }
        return false;
    }

    public function mostrarTabelaNotificacao()
    {
        $notificacoes = $this->pegaAutoInfracaoNotificacao();
        ob_start();
    ?>
        <?php if ($notificacoes) { ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th colspan="5">Notificações de Infração</th>
                    </tr>
                    <tr>
                        <th>Código</th>
                        <th width="500px">Infrações</th>
                        <th>Data / Hora Infração</th>
                        <th>Localização Infração</th>
                        <th>Veículo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($notificacoes as $notificacao) {
                        $ai = $notificacao->pegaAutoInfracao();
                        $veiculo = $notificacao->findParentRow("TbVeiculo");
                        $txt_infracao = "--";
                        $ids = array();
                        $infracoes = $notificacao->listarInfracao();
                        if ($infracoes) {
                            foreach ($infracoes as $infracao) {
                                $ids[] = $infracao->toString();
                            }
                        }
                        if (count($ids)) {
                            $txt_infracao = "<ul><li>" . implode("</li><li>", $ids) . "</li></ul>";
                        }
                    ?>
                        <tr>
                            <td><?php echo $ai->mostrar_codigo(); ?></td>
                            <td><?php echo $txt_infracao; ?></td>
                            <td><?php echo Escola_Util::formatData($notificacao->data_infracao); ?> / <?php echo $notificacao->hora_infracao; ?></td>
                            <td><?php echo $notificacao->local_infracao; ?></td>
                            <td><?php echo ($veiculo) ? $veiculo->toString() : "--"; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function getDeleteErrors()
    {
        $errors = array();

        if ($this->getId()) {
            $tb = new TbCredencial();
            $sql = $tb->select();
            $sql->where("(id_pessoa_fisica = {$id}) or (id_pessoa_fisica_responsavel = {$id})");
            $objs = $tb->fetchAll($sql);
            if ($objs && count($objs)) {
                $msgs[] = "Falha ao Executar Operação, Pessoa Vinculada a Credenciais!!";
            }
        }

        if (!count($errors)) {
            return false;
        }

        return $errors;
    }

    public function toArray()
    {

        $pessoa_array = [];
        $pessoa = $this->getPessoa();
        if ($pessoa) {
            $pessoa_array = $pessoa->toArray();
        }

        $pf_array = parent::toArray();

        $pf_array = array_merge($pessoa_array, $pf_array);

        $telefone_fixo = "";
        $telefones = $pessoa->getTelefones("F");
        if ($telefones) {
            $telefone_fixo = $telefones[0]->toString();
        }

        $telefone_celular = "";
        $telefones = $pessoa->getTelefones("C");
        if ($telefones) {
            $telefone_celular = $telefones[0]->toString();
        }

        $pf_array["telefone_fixo"] = $telefone_fixo;
        $pf_array["telefone_celular"] = $telefone_celular;

        $identidade_uf = $this->pegaIdentidadeUf();
        if ($identidade_uf) {
            $pf_array["identidade_uf"] = $identidade_uf->toArray();
        }

        return $pf_array;
    }

    // public function toObjeto()
    // {

    //     $obj = new stdClass();

    //     $pessoa = $this->pega_pessoa();

    //     $obj->id_pessoa_fisica = $this->getId();
    //     $obj->id_pessoa = $this->id_pessoa;
    //     $obj->cpf = Escola_Util::formatCpf($this->cpf);
    //     $obj->nome = $this->nome;
    //     $obj->email = $pessoa->email;
    //     $obj->id_estado_civil = $this->id_estado_civil;
    //     $obj->data_nascimento = Escola_Util::formatData($this->data_nascimento);
    //     $obj->identidade_numero = $this->identidade_numero;
    //     $obj->identidade_orgao_expedidor = $this->identidade_orgao_expedidor;
    //     $obj->identidade_id_uf = $this->identidade_id_uf;

    //     $obj->nome_pai = $this->nome_pai;
    //     $obj->nome_mae = $this->nome_mae;

    //     $obj->telefone_fixo = "";
    //     $telefones = $pessoa->getTelefones("F");
    //     if ($telefones) {
    //         $obj->telefone_fixo = $telefones[0]->toString();
    //     }
    //     $obj->telefone_celular = "";
    //     $telefones = $pessoa->getTelefones("C");
    //     if ($telefones) {
    //         $obj->telefone_celular = $telefones[0]->toString();
    //     }

    //     $obj->endereco = new stdClass();

    //     $endereco = $pessoa->getEndereco();

    //     $obj->endereco->logradouro = $endereco->logradouro;
    //     $obj->endereco->numero = $endereco->numero;
    //     $obj->endereco->complemento = $endereco->complemento;
    //     $obj->endereco->cep = Escola_Util::formatCep($endereco->cep);
    //     $bairro = $endereco->findParentRow("TbBairro");
    //     $obj->endereco->id_bairro = "";
    //     $obj->endereco->id_municipio = "";
    //     $obj->endereco->id_uf = "";

    //     if ($bairro) {
    //         $obj->endereco->id_bairro = $bairro->getId();
    //         $municipio = $bairro->findParentRow("TbMunicipio");
    //         if ($municipio) {
    //             $obj->endereco->id_municipio = $municipio->getId();
    //             $obj->endereco->id_uf = $municipio->id_uf;
    //         }
    //     }

    //     return $obj;
    // }
}
