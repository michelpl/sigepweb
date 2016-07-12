<?php



/**
 * Classe de integração com o webservice do sistema de gestão de postagem dos Correios
 */
class Sigepweb{
    /* Produção */
    /*private $url = "https://apps.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente?wsdl";
    private $usuario = "";
    private $senha = "";
    private $codAdministrativo = "";
    private $idContrato = "";
    private $cnpj = "";
    private $idCartaoPostagem = "";
    private $idPlpCliente = "";
    private $numDiretoria = ""; 
    private $servicos;
    private $idServico;
    private $etiquetas;
    private $xml;
    private $execucaoId;
    private $valorFrete;
    private $peso;
    private $altura;
    private $largura;
    private $comprimento;*/
     

    /* Desenv */
    private $url = "https://apphom.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente?wsdl";
    private $usuario = "";
    private $senha = "";
    private $codAdministrativo = "";
    private $idContrato = "";
    private $cnpj = "";
    private $idCartaoPostagem = "";
    private $idPlpCliente = "";
    private $numDiretoria = ""; 
    private $servicos;
    private $idServico;
    private $etiquetas;
    private $xml;
    private $execucaoId;
    private $valorFrete;
    private $peso;
    private $altura;
    private $largura;
    private $comprimento;

    private function getUrl(){
        return $this->url;
    }

    private function getUsuario(){
        return $this->usuario;
    }

    private function getSenha(){
        return $this->senha;
    }

    private function getCodigoAdministrativo(){
        return $this->codAdministrativo;
    }

    public function getIdContrato(){
        return $this->idContrato;
    }

    private function getIdCartaoPostagem(){
        return $this->idCartaoPostagem;
    }

    public function getNumDiretoria(){
        return $this->numDiretoria;
    }

    public function getServicos(){
        return $this->servicos;
    }

    public function setServicos($valor){
        $this->servicos = $valor;
    }

    public function setIdServico($valor){
        $this->idServico = $valor;
    }

    public function getIdServico(){
        return $this->idServico;
    }

    public function setXml($valor){
        $this->xml = $valor;
        return $this;
    }

    public function getXml(){
        return $this->xml;
    }

    public function getCnpj(){
        return $this->cnpj;
    }

    public function getEtiquetas(){
        return $this->etiquetas;
    }

    public function setEtiquetas($valor){
        $this->etiquetas = $valor;
    }
    
    public function setExecucaoId($valor){
        $this->execucaoId = $valor;
        return $this;
    }
    
    public function getExecucaoId(){
        return $this->execucaoId;
    }

     public function setPeso($valor){
        $this->peso = $valor;
        return $this;
    }
    
    public function getPeso(){
        return $this->peso;
    }
    
    public function setValorFrete($valor){
        $this->valorFrete = $valor;
        return $this;
    }
    
    public function getValorFrete(){
        return $this->valorFrete;
    }
    
     public function setAltura($valor){
        $this->altura = $valor;
        return $this;
    }
    
    public function getAltura(){
        return $this->altura;
    }
    
    public function setLargura($valor){
        $this->largura = $valor;
        return $this;
    }
    
    public function getLargura(){
        return $this->largura;
    }
    
    public function setComprimento($valor){
        $this->comprimento = $valor;
        return $this;
    }
    
    public function getComprimento(){
        return $this->comprimento;
    }

    public function __construct(){
        
    }

    /**
     * Pega as informações sobre os serviços disponíveis para o CNPJ cadastrado
     * @return Array Array com todos os dados de cada serviço disponível
     */
    public function getServicosContratados(){
        $dados = array(
            "idContrato" => $this->getIdContrato(),
            "idCartaoPostagem" => $this->getIdCartaoPostagem(),
            "usuario" => $this->getUsuario(),
            "senha" => $this->getSenha()
        );

        $client = new nusoap_client($this->getUrl(), true);
        $client->soap_defencoding = 'UTF-8';
        $client->debug_flag = true;

        $servicos = $client->call('buscaCliente', $dados);
        if(!validar($servicos['faultcode']) && validar($servicos['return']['contratos']['cartoesPostagem']['servicos'])){
            $this->setServicos($servicos['return']['contratos']['cartoesPostagem']['servicos']);
            return $this->getServicos();
        } else{
            return FALSE;
        }
    }

    /**
     * Faz a requisição de uma etiqueta ao webservice
     * @param int $idServico Id do serviço nos Correios
     * @return String Número da etiqueta com um espaço em branco, que é a posição do dígito verificador. O dígito verificador é gerado
     * pelo método geraDigitoVerificadorEtiquetas.
     */
    public function solicitaEtiquetas($idServico){
        $this->setIdServico($idServico);
        $dados = [
            "tipoDestinatario" => "C",
            "identificador" => $this->getCnpj(),
            "idServico" => $this->getIdServico(),
            "qtdEtiquetas" => 1,
            "usuario" => $this->getUsuario(),
            "senha" => $this->getSenha()
        ];
        $client = new nusoap_client($this->getUrl(), true);
        $client->soap_defencoding = 'UTF-8';
        $client->debug_flag = true;
        $etiquetas = $client->call('solicitaEtiquetas', $dados);
        if(!validar($etiquetas['faultcode']) && validar($etiquetas['return'])){
            $etiquetas = explode(",", $etiquetas['return']);
            $this->setEtiquetas($etiquetas[0]);
            return $this->getEtiquetas();
        } else{
            return FALSE;
        }
    }

    /**
     *  Solicita o número que valida o código de uma etiqueta
     * @return int Dígito verificador
     */
    public function geraDigitoVerificadorEtiquetas(){
        $dados = [
            "etiquetas" => $this->getEtiquetas(),
            "usuario" => $this->getUsuario(),
            "senha" => $this->getSenha()
        ];
        $client = new nusoap_client($this->getUrl(), true);
        $client->soap_defencoding = 'UTF-8';
        $client->debug_flag = true;
        $digitoVerificador = $client->call('geraDigitoVerificadorEtiquetas', $dados);

        if(!validar($digitoVerificador['faultcode']) && validar($digitoVerificador['return'])){
            return $digitoVerificador['return'];
        } else{
            return FALSE;
        }
    }

    /**
     * Principal método do webservice. Envia todas as informações para os Correios
     * @param int $idPedido Número do pedido
     * @return int Número da PLP
     */
    public function fechaPlp($idPedido){
        $dados = [
            "xml" => $this->getXml(),
            "idPlpCliente" => $idPedido,
            "cartaoPostagem" => $this->getIdCartaoPostagem(),
            "listaEtiquetas" => str_replace(" ", "", $this->getEtiquetas()),
            "usuario" => $this->getUsuario(),
            "senha" => $this->getSenha()
        ];
        //print_r(htmlentities($this->getXml()));
        $client = new nusoap_client($this->getUrl(), true);
        $client->soap_defencoding = 'UTF-8';
        $client->debug_flag = true;
        $plp = $client->call('fechaPlpVariosServicos', $dados);
        if(!validar($plp['faultcode']) && validar($plp['return'])){
            return $plp['return'];
        } else{
            return FALSE;
        }
    }
    
    /**
     * Retorna os dados aferidos pelos os Correios no ato da postagem
     * @param int $idPlp
     * @return Array
     */
    public function getDadosPostagem($idPlp){
        $dados = array(
            "idPlpMaster" => $idPlp,
            "usuario" => $this->getUsuario(),
            "senha" => $this->getSenha()
        );
        $client = new nusoap_client($this->getUrl(), true);
        $client->soap_defencoding = 'UTF-8';
        $client->debug_flag = true;
        $plp = $client->call('solicitaXmlPlp', $dados);
        if(!validar($plp['faultcode']) && validar($plp['return'])){
            $xml = simplexml_load_string($plp['return'], null, LIBXML_NOCDATA);
            $this->setValorFrete(current($xml->plp->valor_global));
            $this->setPeso(current($xml->objeto_postal->peso));
            $this->setAltura(current($xml->objeto_postal->dimensao_objeto->dimensao_altura));
            $this->setLargura(current($xml->objeto_postal->dimensao_objeto->dimensao_largura));
            $this->setComprimento(current($xml->objeto_postal->dimensao_objeto->dimensao_comprimento));
            $dadosPostagem = [
                "valorFrete" => $this->getValorFrete(),
                "peso" => $this->getPeso(),
                "altura" => $this->getAltura(),
                "largura" => $this->getLargura(),
                "comprimento" =>$this->getComprimento()
            ];
            return $dadosPostagem;
        }else{
            return "erro:".$client->getError();
        }
    }
    
    /**
     * Retorna o endereço baseado no CEP
     * @param String $cep Cep sem traço
     * @return boolean
     */
    public function buscaEnderecoPeloCep($cep){
        $dados = array("cep" => str_replace("-", "", $cep));

        $client = new nusoap_client($this->getUrl(), true);
        $client->soap_defencoding = 'UTF-8';
        $client->debug_flag = true;

        $endereco = $client->call('consultaCEP', $dados);
        if(!validar($endereco['faultcode']) && validar($endereco['return'])){
            return $endereco['return'];
        } else{
            return FALSE;
        }
    }
    
    public function validaDados($etiqueta, $codigoServico, $dadosEncomenda){
        if(strlen($etiqueta) != 13){
            return FALSE;
        }
        if(strlen($codigoServico) < 5){
            return FALSE;
        }
        
        if(!$this->validaDadosEncomenda($dadosEncomenda)){
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Checa todas as chaves obrigatórias para os Correios
     * @param Array $endereco
     */
    public function validaEndereco($endereco){
         if(
             !is_array($endereco) 
            || !array_key_exists("nome", $endereco)
            || strlen($endereco["nome"]) > 50
            || strlen($endereco["nome"]) < 2
            || !array_key_exists("logradouro", $endereco)
            || strlen($endereco["logradouro"]) > 40
            || strlen($endereco["logradouro"]) < 2
            || !array_key_exists("numero", $endereco)
            || strlen($endereco["numero"]) > 5
            || strlen($endereco["numero"]) < 1
            || !array_key_exists("complemento", $endereco)
            || strlen($endereco["complemento"]) > 20
            || !array_key_exists("bairro", $endereco)
            || strlen($endereco["bairro"]) > 20
            || strlen($endereco["bairro"]) < 2
            || !array_key_exists("cep", $endereco)
            || strlen($endereco["cep"]) != 8
            || !array_key_exists("cidade", $endereco)
            || strlen($endereco["cidade"]) > 30
            || strlen($endereco["cidade"]) < 2
            || !array_key_exists("estado", $endereco)
            || strlen($endereco["estado"]) != 2
         ){
            return FALSE;
         }
         return TRUE;
    }
    
    public function validaDadosEncomenda($dadosEncomenda){
         if(
                 !is_array($dadosEncomenda) 
                 || !array_key_exists("tipo", $dadosEncomenda)
                 || strlen($dadosEncomenda["tipo"]) != 3
                 || !array_key_exists("altura", $dadosEncomenda)
                 || !array_key_exists("largura", $dadosEncomenda)
                 || !array_key_exists("comprimento", $dadosEncomenda)
                 || !array_key_exists("diametro", $dadosEncomenda)
         ){
             return FALSE;
         }else{
             if($dadosEncomenda["tipo"] == "002"){
                 if($dadosEncomenda['altura'] <2 || $dadosEncomenda['altura'] >105){
                     return FALSE;
                 }
                 if($dadosEncomenda['largura'] <11 || $dadosEncomenda['largura'] >105){
                     return FALSE;
                 }
                 if($dadosEncomenda['comprimento'] <16 || $dadosEncomenda['comprimento'] >105){
                     return FALSE;
                 }
             }elseif($dadosEncomenda["tipo"] == "003"){
                 if($dadosEncomenda['diametro'] <1){
                     return FALSE;
                 }
             }
         }
        return TRUE;
    }
    
    /**
     * Coloca o dígito verificador na etiqueta passada
     * @param String $etiqueta
     * @param int $digitoVerificador
     * @return String
     */
    public function setDigitoNaEtiqueta($etiqueta, $digitoVerificador){
        return str_replace(" ", $digitoVerificador, $etiqueta);
    }
    
    /**
     * Retira o dígito verificador da etiqueta e retorna sua string sem ele
     * @param String $etiqueta
     * @return String
     */
    public function removeDigitoDaEtiqueta($etiqueta){
        return substr_replace($etiqueta, ' ', -3, 1);
    }
    
}
