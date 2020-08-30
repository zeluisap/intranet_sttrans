<?php

class Endereco extends Escola_Entidade
{

    protected $id_uf;
    protected $id_municipio;
    protected $id_municipio_desc;
    protected $id_bairro_desc;

    public function init()
    {
        $bairro = $this->findParentRow("TbBairro");
        if ($bairro) {
            $municipio = $bairro->findParentRow("TbMunicipio");
            if ($municipio) {
                $this->id_municipio = $municipio->getId();
                $uf = $municipio->findParentRow("TbUf");
                if ($uf) {
                    $this->id_uf = $uf->getId();
                }
            }
        }
    }

    public function save()
    {
        if ($this->id_bairro_desc) {
            $this->id_bairro = 0;
        }
        if ($this->id_municipio_desc) {
            $this->id_municipio = 0;
        }
        if (!$this->id_bairro && $this->id_bairro_desc) {
            if (!$this->id_municipio && $this->id_municipio_desc) {
                if ($this->id_uf) {
                    $flag = array(
                        "id_uf" => $this->id_uf,
                        "descricao" => $dados["id_municipio_desc"]
                    );
                    $tb = new TbMunicipio();
                    $row = $tb->createRow();
                    $row->setFromArray($flag);
                    $this->id_municipio = $row->save();
                }
            }
            if ($this->id_municipio) {
                $flag = array(
                    "id_municipio" => $this->id_municipio,
                    "descricao" => $this->id_bairro_desc
                );
                $tb = new TbBairro();
                $linhas = $tb->listar($flag);
                if ($linhas) {
                    $row = $linhas->current();
                } else {
                    $row = $tb->createRow();
                    $row->setFromArray($flag);
                }
                $row->save();
                $this->id_bairro = $row->getId();
            }
        }
        if (!$this->id_bairro) {
            $tb = new TbBairro();
            $bairro = $tb->getPorDescricao("CENTRAL");
            if ($bairro) {
                $this->id_bairro = $bairro->getId();
                $this->id_municipio = $bairro->id_municipio;
            }
        }
        parent::save();
    }

    public function getErrors()
    {
        $msgs = array();
        if (empty($this->logradouro)) {
            $msgs[] = "CAMPO LOGRADOURO OBRIGATÓRIO!";
        }
        if (empty($this->numero)) {
            $msgs[] = "CAMPO NÚMERO OBRIGATÓRIO!";
        }
        if (empty($this->cep)) {
            $msgs[] = "CAMPO CEP OBRIGATÓRIO!";
        }
        if (empty($this->id_bairro) && empty($this->id_bairro_desc)) {
            $msgs[] = "CAMPO BAIRRO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function setFromArray(array $dados)
    {
        $filter = new Zend_Filter_StringToUpper();
        if (isset($dados["logradouro"]) && $dados["logradouro"]) {
            $dados["logradouro"] = $filter->filter($dados["logradouro"]);
        }
        if (isset($dados["numero"]) && $dados["numero"]) {
            $dados["numero"] = $filter->filter($dados["numero"]);
        }
        if (isset($dados["complemento"]) && $dados["complemento"]) {
            $dados["complemento"] = $filter->filter($dados["complemento"]);
        }
        $filter = new Zend_Filter_Digits();
        if (isset($dados["cep"]) && $dados["cep"]) {
            $dados["cep"] = $filter->filter($dados["cep"]);
        }
        if (isset($dados["endereco_id_bairro"]) && $dados["endereco_id_bairro"]) {
            $dados["id_bairro"] = $dados["endereco_id_bairro"];
        } elseif (isset($dados["endereco_bairro"]) && $dados["endereco_bairro"]) {
            $dados["id_bairro_desc"] = $dados["endereco_bairro"];
            if (isset($dados["endereco_id_municipio"]) && $dados["endereco_id_municipio"]) {
                $dados["id_municipio"] = $dados["endereco_id_municipio"];
            } else {
                if (isset($dados["endereco_municipio"]) && $dados["endereco_municipio"]) {
                    if (isset($dados["endereco_id_uf"]) && $dados["endereco_id_uf"]) {
                        $dados["id_uf"] = $dados["endereco_id_uf"];
                        $dados["id_municipio_desc"] = $dados["endereco_municipio"];
                    }
                }
            }
        }
        parent::setFromArray($dados);
        if (isset($dados["id_uf"]) && $dados["id_uf"]) {
            $this->id_uf = $dados["id_uf"];
        }
        if (isset($dados["id_municipio"]) && $dados["id_municipio"]) {
            $this->id_municipio = $dados["id_municipio"];
        }
        if (isset($dados["id_municipio_desc"]) && $dados["id_municipio_desc"]) {
            $this->id_municipio_desc = $dados["id_municipio_desc"];
        }
        if (isset($dados["id_bairro_desc"]) && $dados["id_bairro_desc"]) {
            $this->id_bairro_desc = $dados["id_bairro_desc"];
        }
    }

    public function render(Zend_View_Interface $view = null)
    {
        ob_start();
?>
        <fieldset>
            <legend>ENDEREÇO: </legend>
            <div class="control-group">
                <label for="logradouro" class="control-label">Logradouro:</label>
                <div class="controls">
                    <input type="text" name="logradouro" id="logradouro" maxlength="60" value="<?php echo $this->logradouro; ?>" class="span5" />
                </div>
            </div>
            <div class="control-group">
                <label for="numero" class="control-label">Número:</label>
                <div class="controls">
                    <input type="text" name="numero" id="numero" maxlength="10" value="<?php echo $this->numero; ?>" class="span1" />
                </div>
            </div>
            <div class="control-group">
                <label for="complemento" class="control-label">Complemento:</label>
                <div class="controls">
                    <input type="text" name="complemento" id="complemento" maxlength="40" value="<?php echo $this->complemento; ?>" class="span5" />
                </div>
            </div>
            <div class="control-group">
                <label for="cep" class="control-label">CEP:</label>
                <div class="controls">
                    <input type="text" name="cep" id="cep" maxlength="10" value="<?php echo $this->cep; ?>" class="cep span2" />
                </div>
            </div>
            <?php
            $bairro = $this->findParentRow("TbBairro");
            $uf = false;
            $tb = new TbUf();
            if ($this->id_uf) {
                $uf = $tb->getPorId($this->id_uf);
            } else {
                if ($bairro) {
                    $municipio = $bairro->findParentRow("TbMunicipio");
                    if ($municipio) {
                        $uf = $tb->getPorId($municipio->id_uf);
                    }
                }
            }
            $ctrl = new Escola_Form_Element_Select_Table("endereco_id_uf");
            if ($uf) {
                $ctrl->setValue($uf->id_uf);
            }
            $ctrl->setPkName("id_uf");
            $ctrl->setModel("TbUf");
            $ctrl->setLabel("Unidade Federativa: ");
            echo $ctrl->render($view);

            /*
              $ctrl = new Escola_Form_Element_Select_Table_Crud_Municipio("endereco_id_municipio");
              $ctrl->setLabel("Município: ");
              $ctrl->setFlag("endereco_municipio");
              $id_municipio = $this->id_municipio;
              if (!$id_municipio && $this->id_bairro) {
              if ($bairro) {
              $id_municipio = $bairro->id_municipio;
              }
              }
              if ($id_municipio) {
              $ctrl->setIdValue($id_municipio);
              }
              $ctrl->setTable("TbMunicipio");
              $ctrl->setIdParents(array("endereco_id_uf"));
              $ctrl->setUrl($view->baseUrl() . "/municipio/listar/format/json/");
              $ctrl->setDados(array("id_uf" => "$('#endereco_id_uf').val()", "descricao" => "$('#endereco_municipio').val()"));
              echo $ctrl->render($view);
             */
            $ctrl = new Escola_Form_Element_Select_Table_Crud_Municipio("endereco_id_municipio");
            $ctrl->setPkName("id_municipio");
            $ctrl->setModel("TbMunicipio");
            $ctrl->setValue($this->id_municipio);
            $ctrl->setLabel("Município: ");
            $ctrl->set_id_uf("endereco_id_uf");
            echo $ctrl->render($view);
            /*
              $ctrl = new Escola_Form_Element_Select_Dinamic("endereco_id_bairro");
              $ctrl->setLabel("Bairro: ");
              $ctrl->setFlag("endereco_bairro");
              if ($this->id_bairro) {
              $ctrl->setIdValue($this->id_bairro);
              }
              $ctrl->setTable("TbBairro");
              $ctrl->setIdParents(array("endereco_municipio"));
              $ctrl->setUrl($view->baseUrl() . "/bairro/listar/format/json/");
              $ctrl->setDados(array("id_municipio" => "$('#endereco_id_municipio').val()", "descricao" => "$('#endereco_bairro').val()"));
              echo $ctrl->render($view);
             */
            $ctrl = new Escola_Form_Element_Select_Table_Crud_Bairro("endereco_id_bairro");
            $ctrl->setPkName("id_bairro");
            $ctrl->setModel("TbBairro");
            $ctrl->setValue($this->id_bairro);
            $ctrl->setLabel("Bairro: ");
            $ctrl->set_id_municipio("endereco_id_municipio");
            echo $ctrl->render($view);
            ?>
        </fieldset>
<?php
        $res = ob_get_contents();
        ob_end_clean();
        return $res;
    }

    public function toString()
    {
        $txt = array();
        $txt[] = $this->logradouro;
        if ($this->numero) {
            $txt[] = "N.: {$this->numero}";
        }
        if ($this->complemento) {
            $txt[] = $this->complemento;
        }
        $bairro = $this->findParentRow("TbBairro");
        if ($bairro) {
            $txt[] = "Bairro: {$bairro->toString()}";
            $municipio = $bairro->findParentRow("TbMunicipio");
            if ($municipio) {
                $txt[] = $municipio->toString();
                $uf = $municipio->findParentRow("TbUf");
                if ($uf) {
                    $txt[] = $uf->sigla;
                }
            }
        }
        if (Escola_Util::limpaNumero($this->cep)) {
            $txt[] = Escola_Util::formatCep($this->cep);
        }
        return implode(" - ", $txt);
    }

    public function getDados()
    {
        $obj = new stdClass();
        $obj->logradouro = "";
        $obj->numero = "";
        $obj->complemento = "";
        $obj->bairro = "";
        $obj->municipio = "";
        $obj->uf = "";
        $obj->cep = "";

        if ($this->logradouro) {
            $obj->logradouro = $this->logradouro;
        }

        if ($this->numero) {
            $obj->numero = $this->numero;
        }

        if ($this->complemento) {
            $obj->complemento = $this->complemento;
        }

        $bairro = $this->findParentRow("TbBairro");
        if ($bairro) {
            $obj->bairro = $bairro->toString();
            $municipio = $bairro->findParentRow("TbMunicipio");
            if ($municipio) {
                $obj->municipio = $municipio->toString();
                $uf = $municipio->findParentRow("TbUf");
                if ($uf) {
                    $obj->uf = $uf->sigla;
                }
            }
        }

        if (Escola_Util::limpaNumero($this->cep)) {
            $obj->cep = Escola_Util::formatCep($this->cep);
        }

        return $obj;
    }

    public function extenso()
    {
        $dados = $this->getDados();
        if (!$dados) {
            return "";
        }

        if (!$dados->logradouro) {
            return "";
        }

        $txt = $dados->logradouro;
        if ($dados->numero) {
            $txt .= ", N°.: " . $dados->numero;
        }

        if ($dados->bairro) {
            $txt .= " - Bairro: " . $dados->bairro;
        }

        if ($dados->cep) {
            $txt .= " - CEP.: " . $dados->cep;
        }

        if ($dados->municipio) {
            $txt .= " - Município: " . $dados->municipio;
            if ($dados->uf) {
                $txt .= " / " . $dados->uf;
            }
        }
    }

    public function toArray()
    {
        $end_array = parent::toArray();

        $bairro = $this->findParentRow("TbBairro");
        if ($bairro) {
            $end_array["bairro"] = $bairro->toArray();
        }

        $end_array["id_municipio"] = Escola_Util::valorOuCoalesce($end_array, "bairro->municipio->id", "");
        $end_array["id_uf"] = Escola_Util::valorOuCoalesce($end_array, "bairro->municipio->uf->id", "");

        return $end_array;
    }
}
