# Módulo Oficial da Gerencianet para o Magento 1.9 / OpenMage

**Em caso de dúvidas, você pode verificar a [Documentação](https://docs.gerencianet.com.br) da API na Gerencianet e, necessitando de mais detalhes ou informações, entre em contato com nossa consultoria técnica, via nossos [Canais de Comunicação](https://gerencianet.com.br/central-de-ajuda).**

## Requisitos

 - PHP >= 7.0.0
 - Magento 1.9.x  OU  OpenMage LTS 19.4.x

## Instalação

### Instalar usando o [modgit](https://github.com/jreinke/modgit):

    $ cd /path/to/magento
    $ modgit init
    $ modgit add gerencianet https://github.com/gerencianet/gn-api-magento.git

- Atualize o cache da sua loja acessando `Sistema > Gerenciador de Cache > Atualizar Cache`.

### Instalar manualmente:

- Baixe a [última versão](https://github.com/gerencianet/gn-api-magento/archive/master.zip) do módulo.
- Descompacte o arquivo baixado e copie as pastas app, lib e skin para dentro do diretório principal do Magento*.
- Execute os comandos:

    ```sudo find . -type d -exec chmod 755 {} \;
    sudo find . -type f -exec chmod 644 {} \;
    sudo chmod 777 -R app/etc/;
    sudo chmod 777 -R var/;
    sudo chmod 777 -R media/;

- Atualize o cache da sua loja acessando `Sistema > Gerenciador de Cache > Atualizar Cache`.

*Ao substituir as pastas no seu projeto, o sistema pode informar que alguns arquivos serão sobrescritos. Não se preocupe, pode confirmar o procedimento pois a instalação não afeterá nenhum arquivo já existente em seu projeto.

## Configuração

Acessando `Sistema > Configuração > Formas de Pagamento`, 4 novos menus serão mostrados:

- Gerencianet Pagamentos - Configurações Gerais
- Gerencianet - Boleto
- Gerencianet - Cartão de Cŕedito
- Gerencianet - Pix

## **Gerencianet Pagamentos - Configurações Gerais**
![Config](https://i.imgur.com/iVxDlsd.png)

- **Habilitado:** Serve para habilitar ou desabilitar o módulo.
- **Ambiente:** Serve para descrever se as transações acontecerão em ambiente de Produção ou Desenvolvimento.
- **Modo debug:** Habilita o modo debug do módulo.
- **Identificador da conta:** Identificador de Conta da Gerencianet.
- **Credenciais de Desenvolvimento ou Produção:** Aqui você informa as suas credenciais, Client Id e Client Secret do ambiente selecionado.

### **Gerencianet - Boleto**
![Boleto](https://i.imgur.com/NkbkCoE.png)

- **Habilitado:** Serve para habilitar ou desabilitar a funcionalidade de Boletos.
- **Título:** Altera o nome do método de pagamento no checkout.
- **Dias para vencimento:** Validade do Boleto.
- **Multa após vencimento:** Valor da multa a ser cobrada após o vencimento.
- **Juros após vencimento:** Valor de juros a ser cobrado.
- **Instruções no boleto:** Aqui você tem quatro campos que podem ser preenchido com mensagens no boleto, desde que as opções de juros e multa estejam zeradas.

### **Gerencianet - Cartão de Crédito**
![Cartao](https://i.imgur.com/nGfS06C.png)
- **Habilitado:** Serve para habilitar ou desabilitar a funcionalidade de cartão de crédito.
- **Título:** Altera o nome do método de pagamento no checkout.

### **Gerencianet - Pix**
![Pix](https://i.imgur.com/6j2IvPP.png)

- **Habilitado:** Serve para habilitar ou desabilitar a funcionalidade de Pix.
- **Título:** Altera o nome do método de pagamento no checkout.
- **Chave Pix:** Sua chave Pix cadastrada no aplicativo da Gerencianet.
- **Certificado Pix:** Certificado gerado no painel da Gerencianet.
- **Tempo de validade do Pix:** Validade do Pix.
- **Validar mTLS:** Habilita o mTLS.



