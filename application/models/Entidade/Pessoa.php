<?php

class Pessoa extends Escola_Entidade
{

    protected $_endereco;

    public function init()
    {
        parent::init();
        $this->_endereco = $this->getEndereco();
    }

    public function getErrors($flag = true)
    {
        $msgs = array();
        if (empty($this->id_pessoa_tipo)) {
            $msgs[] = "CAMPO TIPO DE PESSOA OBRIGATï¿½RIO!";
        }
        if ($flag) {
            $validate = new Zend_Validate();
            $validate->addValidator(new Zend_Validate_EmailAddress());
            if (!$validate->isValid($this->email)) {
                $msgs[] = "CAMPO E-MAIL INVï¿½LIDO!";
            }
        }

        if (!count($msgs)) {
            $id = '0';
            if ($this->getId()) {
                $id = $this->getId();
            }
            if ($this->email) {
                $tb = $this->getTable();
                $sql = $tb->select();
                $sql->where("id_pessoa_tipo = {$this->id_pessoa_tipo}");
                $sql->where("email = '{$this->email}'");
                $sql->where("id_pessoa <> {$id} ");
                $objs = $tb->fetchAll($sql);
                if ($objs && count($objs)) {
                    $msgs[] = "E-mail já cadastrado para outra pessoa!";
                }
            }
        }
        /* validaï¿½ï¿½o do endereï¿½o nï¿½o necessï¿½ria 
          $err = $this->_endereco->getErrors();
          if ($err) {
          $msgs = array_merge($msgs, $err);
          }
         */
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function addTelefone($telefone)
    {
        $dados = array(
            "id_pessoa" => $this->id_pessoa,
            "tipo" => "T",
            "chave" => $telefone->id_telefone
        );
        $tb = new TbPessoaRef();
        $pr = $tb->createRow();
        $pr->setFromArray($dados);
        if (!$pr->getErrors()) {
            return $pr->save();
        }
        return false;
    }

    public function getEndereco()
    {
        $id = $this->id_pessoa;
        if (!$this->id_pessoa) {
            $id = "null";
        }
        $tb = new TbPessoaRef();
        $prs = $tb->listar(array(
            "tipo" => "E",
            "id_pessoa" => "{$id}"
        ));
        if ($prs && $prs->count()) {
            $pr = $prs->current();
            return $pr->getObjeto();
        }
        if ($this->_endereco) {
            return $this->_endereco;
        }
        $tb = new TbEndereco();
        return $tb->createRow();
    }

    public function addEndereco($end)
    {
        $dados = array(
            "id_pessoa" => $this->id_pessoa,
            "tipo" => "E",
            "chave" => $end->id_endereco
        );
        $tb = new TbPessoaRef();
        $pr = $tb->createRow();
        $pr->setFromArray($dados);
        if (!$pr->getErrors()) {
            return $pr->save();
        }
        return false;
    }

    public function setFromArray(array $dados)
    {
        $this->_endereco->setFromArray($dados);
        parent::setFromArray($dados);
    }

    public function save()
    {
        $this->_endereco->save();
        $id = parent::save();
        $this->addEndereco($this->_endereco);
        return $id;
    }

    public function getDeleteErrors()
    {
        parent::getDeleteErrors();
    }

    public function delete()
    {
        $ibs = $this->get_info_bancaria();
        if ($ibs) {
            foreach ($ibs as $ib) {
                $ib->delete();
            }
        }
        $fones = $this->getTelefones();
        if ($fones) {
            foreach ($fones as $fone) {
                $fone->delete();
            }
        }
        $filho = $this->pegaPessoaFilho();
        if ($filho) {
            $filho->delete();
        }
        $tb = new TbPessoaRef();
        $prs = $tb->listar(array("id_pessoa" => $this->getId()));
        if ($prs) {
            foreach ($prs as $pr) {
                $pr->delete();
            }
        }
        parent::delete();
    }

    public function getTelefones($chave = "")
    {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("pr" => "pessoa_ref"), array("t.id_telefone"));
        $sql->join(array("t" => "telefone"), "pr.chave = t.id_telefone");
        $sql->where("pr.tipo = 'T'");
        $sql->where("pr.id_pessoa = " . $this->getId());
        if ($chave) {
            $tb = new TbTelefoneTipo();
            $tt = $tb->getPorChave($chave);
            $sql->where("t.id_telefone_tipo = " . $tt->getId());
        }
        $stmt = $sql->query();
        $rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
        if (count($rg)) {
            $tb = new TbTelefone();
            $items = array();
            foreach ($rg as $obj) {
                $items[] = $tb->getPorId($obj->id_telefone);
            }
            return $items;
        }
        return false;
    }

    public function getFoto()
    {
        if ($this->getId()) {
            $id = $this->getId();
            $tb = new TbPessoaRef();
            $prs = $tb->listar(array(
                "tipo" => "F",
                "id_pessoa" => "{$id}"
            ));
            if ($prs && $prs->count()) {
                $pr = $prs->current();
                return $pr->getObjeto();
            }
        }
        return false;
    }

    public function addFoto($foto)
    {
        $this->limparFoto();
        $dados = array(
            "id_pessoa" => $this->id_pessoa,
            "tipo" => "F",
            "chave" => $foto->getId()
        );
        $tb = new TbPessoaRef();
        $pr = $tb->createRow();
        $pr->setFromArray($dados);
        if (!$pr->getErrors()) {
            return $pr->save();
        }
        return false;
    }

    public function limparFoto()
    {
        $tb = new TbPessoaRef();
        $prs = $tb->listar(array("tipo" => "F", "id_pessoa" => $this->getId()));
        if ($prs && $prs->count()) {
            foreach ($prs as $pr) {
                $pr->delete();
            }
        }
        $foto = $this->getFoto();
        if ($foto) {
            $foto->delete();
        }
    }

    public function mostrarTelefones($tipo = "")
    {
        $telefones = $this->getTelefones($tipo);
        if ($telefones) {
            $telefone = array();
            foreach ($telefones as $fone) {
                $telefone[] = "({$fone->ddd}){$fone->numero}";
            }
            return implode(", ", $telefone);
        }
        return "";
    }

    public function mostrar_info_bancaria()
    {
        $ibs = $this->get_info_bancaria();
        if ($ibs) {
            $txt = array();
            foreach ($ibs as $ib) {
                $txt[] = $ib->toString();
            }
            return implode(", ", $txt);
        }
        return "";
    }

    public function get_info_bancaria()
    {
        $tb = new TbInfoBancariaRef();
        $ibs = false;
        $ibrs = $tb->listar(array("tipo" => "P", "chave" => $this->getId()));
        if ($ibrs) {
            $ibs = array();
            foreach ($ibrs as $ibr) {
                $ib = $ibr->findParentRow("TbInfoBancaria");
                if ($ib) {
                    $ibs[] = $ib;
                }
            }
        }
        if ($ibs && count($ibs)) {
            return $ibs;
        }
        return false;
    }

    public function add_info_bancaria($ib)
    {
        $tb = new TbInfoBancariaRef();
        $sql = $tb->select();
        $sql->where("tipo = 'P'");
        $sql->where("chave = {$this->getId()}");
        $sql->where("id_info_bancaria = {$ib->getId()}");
        $rows = $tb->fetchAll($sql);
        if (!$rows || !count($rows)) {
            $tbr = $tb->createRow();
            $tbr->setFromArray(array("tipo" => "P", "chave" => $this->getId(), "id_info_bancaria" => $ib->getId()));
            if (!$tbr->getErrors()) {
                $tbr->save();
            }
        }
    }

    public function getTipo()
    {
        return $this->findParentRow("TbPessoaTipo");
    }

    public function pj()
    {
        $pt = $this->findParentRow("TbPessoaTipo");
        if (!$pt) {
            return false;
        }
        return $pt->pj();
    }

    public function pf()
    {
        $pt = $this->findParentRow("TbPessoaTipo");
        if (!$pt) {
            return false;
        }
        return $pt->pf();
    }

    public function pegaPessoaFilho()
    {
        $class_name = "";
        $pt = $this->findParentRow("TbPessoaTipo");
        if ($pt) {
            $tb = false;
            if ($pt->pf()) {
                $tb = new TbPessoaFisica();
            } elseif ($pt->pj()) {
                $tb = new TbPessoaJuridica();
            }
            if ($tb) {
                $sql = $tb->select();
                $sql->where("id_pessoa = {$this->getId()}");
                $rs = $tb->fetchAll($sql);
                if ($rs && count($rs)) {
                    return $rs->current();
                }
            }
        }
    }

    public function mostrar_documento()
    {
        $obj = $this->pegaPessoaFilho();
        if ($obj) {
            return $obj->mostrar_documento();
        }
        return "";
    }

    public function mostrar_nome()
    {
        $obj = $this->pegaPessoaFilho();
        if ($obj) {
            return $obj->mostrar_nome();
        }
        return "";
    }

    public function toString()
    {
        $obj = $this->pegaPessoaFilho();
        if ($obj) {
            return $obj->toString();
        }
        return "";
    }

    public function view()
    {
        ob_start();
?>
        <dl class="dl-horizontal">
            <dt>Tipo:</dt>
            <dd><?php echo $this->findParentRow("TbPessoaTipo")->toString(); ?></dd>
        </dl>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        $obj = $this->pegaPessoaFilho();
        if ($obj) {
            $html .= $obj->view();
        }
        return $html;
    }

    public function mostrar_endereco()
    {
        $endereco = $this->getEndereco();
        if ($endereco) {
            return $endereco->toString();
        }
        return "";
    }

    public function endereco_extenso()
    {
        $endereco = $this->getEndereco();
        if (!$endereco) {
            return null;
        }
        return $endereco->extenso();
    }

    public function pegaAutoInfracaoNotificacao()
    {
        if ($this->findParentRow("TbPessoaTipo")->pf()) {
            $pf = $this->pegaPessoaFilho();
            if ($pf) {
                return $pf->pegaAutoInfracaoNotificacao();
            }
        }
        return false;
    }

    public function mostrarTabelaNotificacao()
    {
        $obj = $this->pegaPessoaFilho();
        if ($obj) {
            return $obj->mostrarTabelaNotificacao();
        }
        return "";
    }

    public function toArray()
    {
        $pessoa_array = parent::toArray();

        $pessoa_array["nome"] = $this->mostrar_nome();

        $endereco = $this->getEndereco();
        if ($endereco) {
            $pessoa_array["endereco"] = $endereco->toArray();
        }

        return $pessoa_array;
    }
}
