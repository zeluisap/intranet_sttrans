<?php
class LoginForm extends Zend_Form
{

	public function init()
	{
		$this->setMethod("post");
		$this->setAttrib("id", "formulario");
		$this->addElement("text", "login_cpf", array("label" => "C.P.F.:"));
		$cpf = $this->getElement("login_cpf");
		$cpf->setRequired(true);
		$cpf->addValidator("NotEmpty", true, array("messages" => "CAMPO OBRIGATÓRIO!"));
		$cpf->addValidator("alnum", false, array("messages" => "NUMERO DE CPF INVALIDO!"));
		$cpf->addFilter("StringToLower");
		$cpf->addPrefixPath("Escola_Validate", "Escola/Validate/", "validate");
		$cpf->addValidator("Authorise");
		$this->addElement("password", "login_senha", array("label" => "Senha:"));
		$senha = $this->getElement("login_senha");
		$senha->setRequired(true);
		$senha->addValidator("NotEmpty", true, array("messages" => "CAMPO OBRIGATÓRIO!"));
		$senha->addFilter("stringTrim");
		$this->addElement("submit", "Efetuar Login");
	}
}
