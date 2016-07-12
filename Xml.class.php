<?php
/**
 * Faz o tratamento do XML que será enviado pro webservice
 *
 * @author michelpl
 */
namespace Sigepweb;

class Xml{
     /**
     * Monta o xml para envio da PLP
     * @param String $etiqueta Número da etiqueta COM o dígito verificador
     * @param int $codigoServico Código do serviço nos Correios
     * @param Array $enderecoRemetente Endereço do remetente
     *  array(
     *      nome =>
     *      logradouro =>,
     *      numero =>,
     *      complemento =>,
     *      bairro =>,
     *      cidade =>,
     *      uf =>,
     *      cep =>
     * )
     * @param Array $enderecoDestinatario
     *  array(
     *      nome =>
     *      logradouro =>,
     *      numero =>,
     *      complemento =>,
     *      bairro =>,
     *      cidade =>,
     *      uf =>,
     *      cep =>
     * )
     * @param Array $dadosEncomenda 
     * array(
     *      tipo => ,
     *      altura =>,
     *      largura =>
     *      comprimento =>,
     *      diametro =>0
     * )
     */
    public  function montaXml($etiqueta, $codigoServico, $enderecoRemetente, $enderecoDestinatario, $dadosEncomenda){
        $xml = $this->montaXmlCabecalho();
        $xml .= $this->montaXmlRemetente($enderecoRemetente);
        $xml .= $this->montaXmlObjeto($etiqueta, $codigoServico, $enderecoDestinatario, $dadosEncomenda);
        $xml .= "</correioslog>";
        //trace(htmlentities($xml));
        $this->setXml($xml);
    }

    /**
     * Monta o cabeçalho do XML que será enviado.
     * @return string
     */
    private function montaXmlCabecalho(){
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>
            <correioslog>
                <tipo_arquivo>Postagem</tipo_arquivo>
                <versao_arquivo>2.3</versao_arquivo>
                <plp>
                    <id_plp />
                    <valor_global />
                    <mcu_unidade_postagem />
                    <nome_unidade_postagem />
                    <cartao_postagem>" . $this->getIdCartaoPostagem() . "</cartao_postagem>
                </plp>";
        return $xml;
    }

    /**
     * Monta o xml com os dados de endereço e contrato do remetente.
     * @param Array $remetente Dados de endereço do remetente
     * @return string
     */
    private function montaXmlRemetente($remetente){
        $xml = "
        <remetente>
                <numero_contrato>" . $this->getIdContrato() . "</numero_contrato>
                <numero_diretoria>" . $this->getNumDiretoria() . "</numero_diretoria>
                <codigo_administrativo>" . $this->getCodigoAdministrativo() . " </codigo_administrativo>
                <nome_remetente><![CDATA[" . $remetente['nome'] . "]]></nome_remetente>
                <logradouro_remetente><![CDATA[" . $remetente['logradouro'] . "]]></logradouro_remetente>
                <numero_remetente><![CDATA[" . $remetente['numero'] . "]]></numero_remetente>
                <complemento_remetente><![CDATA[" . $remetente['complemento'] . "]]></complemento_remetente>
                <bairro_remetente><![CDATA[" . $remetente['bairro'] . "]]></bairro_remetente>
                <cep_remetente><![CDATA[" . $remetente['cep'] . "]]></cep_remetente>
                <cidade_remetente><![CDATA[" . $remetente['cidade'] . "]]></cidade_remetente>
                <uf_remetente><![CDATA[" . $remetente['estado'] . "]]></uf_remetente>
                <telefone_remetente></telefone_remetente>
                <fax_remetente></fax_remetente>
                <email_remetente></email_remetente>
            </remetente>
            <forma_pagamento />";
        return $xml;
    }

    /**
     * Monta o xml do objeto postal(encomenda)
     * @param String $numEtiqueta Número da etiqueta retornada pelo webservice COM o dígito verificador.
     * @param int $codigoServico Código dos Correios para o serviço utilizado(PAC, SEDEX, E-sedex, etc).
     * @param Array $enderecoDestinatario dados de endereço do destinatário.
     * @param Array $dadosObjeto Tipo(caixa, envelope, cilindro), altura, largura, comprimento, diâmetro.
     * @return string
     */
    private function montaXmlObjeto($numEtiqueta, $codigoServico, $enderecoDestinatario, $dadosObjeto){
        $xml .=
                "<objeto_postal>
                    <numero_etiqueta>" . $numEtiqueta . "</numero_etiqueta>
                    <codigo_objeto_cliente />
                    <codigo_servico_postagem>" . $codigoServico . "</codigo_servico_postagem>
                    <cubagem></cubagem>
                    <peso>" . $dadosObjeto['peso'] . "</peso>
                    <rt1 />
                    <rt2 />";

        $xml .= $this->montaXmlObjetoDestinatario($enderecoDestinatario);
        $xml .= $this->montaXmlObjetoServicoAdicional();
        $xml .= $this->montaXmlObjetoDimensoes($dadosObjeto);

        $xml .="<data_postagem_sara />
                          <status_processamento>0</status_processamento>
                          <numero_comprovante_postagem />
                          <valor_cobrado />
            </objeto_postal>";
        return $xml;
    }

    /**
     * Monta os dados do destinatário para colocar no objeto postal
     * @param Array $endereco Dados de endereço do destinatário
     * @return String
     */
    private function montaXmlObjetoDestinatario($endereco){
        $xml = "
                <destinatario>
                    <nome_destinatario><![CDATA[" . $endereco['nome'] . "]]></nome_destinatario>
                    <telefone_destinatario></telefone_destinatario>
                    <celular_destinatario></celular_destinatario>
                    <email_destinatario></email_destinatario>
                    <logradouro_destinatario><![CDATA[" . $endereco['logradouro'] . "]]></logradouro_destinatario>
                    <complemento_destinatario><![CDATA[" . $endereco['complemento'] . "]]></complemento_destinatario>
                    <numero_end_destinatario><![CDATA[" . $endereco['numero'] . "]]></numero_end_destinatario>
                </destinatario>
                <nacional>
                    <bairro_destinatario><![CDATA[" . $endereco['bairro'] . "]]></bairro_destinatario>
                    <cidade_destinatario><![CDATA[" . $endereco['cidade'] . "]]></cidade_destinatario>
                    <uf_destinatario><![CDATA[" . $endereco['estado'] . "]]></uf_destinatario>
                    <cep_destinatario><![CDATA[" . $endereco['cep'] . "]]></cep_destinatario>
                    <codigo_usuario_postal />
                    <centro_custo_cliente />
                    <numero_nota_fiscal></numero_nota_fiscal>
                    <serie_nota_fiscal />
                    <valor_nota_fiscal />
                    <natureza_nota_fiscal />
                    <descricao_objeto></descricao_objeto>
                    <valor_a_cobrar></valor_a_cobrar>
                </nacional>";
        return $xml;
    }

    /**
     * Monta o xml de serviços adicionais para o objeto postal. O código 025 é obrigatório
     * @return string
     */
    private function montaXmlObjetoServicoAdicional(){
        $xml = "<servico_adicional>
                        <codigo_servico_adicional>025</codigo_servico_adicional>
                        <valor_declarado></valor_declarado>
                    </servico_adicional>";
        return $xml;
    }

    /**
     * Monta o xml com as dimensões do pacote que será postado para o objeto postal.
     * @param Array $dados Dimensões e tipo do objeto(caixa, envelope, cilindro).
     * @return string
     */
    private function montaXmlObjetoDimensoes($dados){
        $xml = "<dimensao_objeto>
                    <tipo_objeto>" . $dados['tipo'] . "</tipo_objeto>
                    <dimensao_altura>" . $dados['altura'] . "</dimensao_altura>
                    <dimensao_largura>" . $dados['largura'] . "</dimensao_largura>
                    <dimensao_comprimento>" . $dados['comprimento'] . "</dimensao_comprimento>
                    <dimensao_diametro>" . $dados['diametro'] . "</dimensao_diametro>
                </dimensao_objeto>";
        return $xml;
    }
}
