<?php

class PessoaJuridica extends Escola_Entidade {

    protected $_pessoa;

    /**
     * 
     * @return Pessoa
     */
    public function pega_pessoa() {
        return $this->_pessoa;
    }

    public function init() {
        parent::init();
        $this->_pessoa = $this->getPessoa();
    }

    public function setFromArray(array $dados) {
        if (isset($dados["cnpj"])) {
            $dados["cnpj"] = Escola_Util::limparNumero($dados["cnpj"]);
            $tb = new TbPessoaJuridica();
            $rs = $tb->fetchAll("cnpj = '{$dados["cnpj"]}'");
            if ($rs && count($rs)) {
                $pf = $rs->current();
                $this->id_pessoa_juridica = $pf->getId();
                $this->refresh();
            }
        }

        if (isset($dados["sigla"])) {
            $dados["sigla"] = Escola_Util::maiuscula($dados["sigla"]);
        }
        if (isset($dados["nome_fantasia"])) {
            $dados["nome_fantasia"] = Escola_Util::maiuscula($dados["nome_fantasia"]);
        }
        if (isset($dados["razao_social"])) {
            $dados["razao_social"] = Escola_Util::maiuscula($dados["razao_social"]);
        }
        $this->_pessoa->setFromArray($dados);
        parent::setFromArray($dados);
    }

    public function getPessoa() {
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
        $pt = $tb->getPorChave("PJ");
        if ($pt) {
            $row->id_pessoa_tipo = $pt->id_pessoa_tipo;
        }
        return $row;
    }

    public function __toString() {
        $txt = array();
        if ($this->cnpj) {
            $txt[] = Escola_Util::formatCnpj($this->cnpj);
        }
        if ($this->nome_fantasia) {
            $txt[] = $this->nome_fantasia;
        }
        if ($this->sigla) {
            $txt[] = $this->sigla;
        }
        return implode(" - ", $txt);
    }

    public function toString() {
        return $this->__toString();
    }

    public function save() {
        $id = $this->_pessoa->save();
        $this->id_pessoa = $id;
        return parent::save();
    }

    public function getErrors($flag = true) {
        $errors = array();
        if ($flag) {
            if (!trim($this->sigla)) {
                $errors[] = "CAMPO SIGLA OBRIGATÓRIO!";
            }
        }
        if (!trim($this->razao_social)) {
            $errors[] = "CAMPO RAZÃO SOCIAL OBRIGATÓRIO!";
        }
        if (!trim($this->nome_fantasia)) {
            $errors[] = "CAMPO NOME FANTASIA OBRIGATÓRIO!";
        }
        $sql = $this->getTable()->select();
        $sql->where("cnpj = '{$this->cnpj}' and id_pessoa_juridica <> " . $this->getId());
        $objs = $this->getTable()->fetchAll($sql);
        if ($objs && count($objs)) {
            $errors[] = "PESSOA JURÍDICA JÁ CADASTRADA!";
        }
        $err = $this->_pessoa->getErrors($flag);
        if ($err) {
            $errors = array_merge($errors, $err);
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }

    public function mostrar_documento() {
        return Escola_Util::formatCnpj($this->cnpj);
    }

    public function mostrar_nome() {
        return $this->nome_fantasia;
    }

    public function view() {
        $pessoa = $this->findParentRow("TbPessoa");
        ob_start();
        ?>
        <dl class="dl-horizontal">
            <dt>C.P.F.:</dt>
            <dd><?php echo Escola_Util::formatCnpj($this->cnpj); ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Sigla:</dt>
            <dd><?php echo $this->sigla; ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Razão Social:</dt>
            <dd><?php echo $this->razao_social; ?></dd>
        </dl>
        <dl class="dl-horizontal">
            <dt>Nome Fantasia:</dt>
            <dd><?php echo $this->nome_fantasia; ?></dd>
        </dl>
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

    public function render(Zend_View_Interface $view) {
        ob_start();
        ?>
        <dl class="dl-horizontal">
            <dt>C.N.P.J.:</dt>
            <dd><?php echo Escola_Util::formatCnpj($this->cnpj); ?></dd>
        </dl>
        <div class="control-group">
            <label for="sigla" class="control-label">Sigla:</label>
            <div class="controls">
                <input type="text" name="sigla" id="sigla" class="span2 nome" value="<?php echo $this->sigla; ?>" size="20" />
            </div>
        </div>
        <div class="control-group">
            <label for="razao_social" class="control-label">Razão Social:</label>
            <div class="controls">
                <input type="text" name="razao_social" id="razao_social" class="span5" value="<?php echo $this->razao_social; ?>" size="60" />
            </div>
        </div>
        <div class="control-group">
            <label for="nome_fantasia" class="control-label">Nome Fantasia:</label>
            <div class="controls">
                <input type="text" name="nome_fantasia" id="nome_fantasia" class="span5" value="<?php echo $this->nome_fantasia; ?>" size="60" />
            </div>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function mostrarTabelaNotificacao() {
        return "";
    }

    public function getEnderecoArray() {
        $txt = array();

        $pessoa = $this->pega_pessoa();
        if (!$pessoa) {
            throw new Exception("Falha ao Processar Dados de Pessoa Jurídica, Chame o Administrador!");
        }

        $end = $pessoa->getEndereco();
        if (!$end) {
            return $txt;
        }

        $endereco_1 = $end->logradouro;
        if ($end->numero) {
            $endereco_1.= ", " . $end->numero;
        }

        $bairro = $end->findParentRow("TbBairro");
        if ($bairro) {
            $endereco_1 .= " - " . $bairro->descricao;
            $municipio = $bairro->findParentRow("TbMunicipio");
            if ($municipio) {
                $endereco_1 .= " - " . $municipio->descricao;
                $uf = $municipio->findParentRow("TbUf");
            }
        }
        $txt[] = $endereco_1;

        $endereco_2 = "CEP " . Escola_Util::formatCep($end->cep);
        $fones = $pessoa->mostrarTelefones();
        if ($fones) {
            $endereco_2 .= " - Fone(s): " . $fones;
        }
        if ($municipio) {
            $endereco_2 .= " - " . $municipio->descricao;
        }
        if ($uf) {
            $endereco_2 .= " / " . $uf->sigla;
        }
        
        $txt[] = $endereco_2;
        
        return $txt;
    }
    
    public function getFoto() {
        $pessoa = $this->pega_pessoa();
        if (!$pessoa) {
            return false;
        }
        
        return $pessoa->getFoto();
    }
}