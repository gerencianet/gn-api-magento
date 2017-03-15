# Módulo Oficial da Gerencianet para o Magento - Versão 0.2.6

**Em caso de dúvidas, você pode verificar a [Documentação](https://docs.gerencianet.com.br) da API na Gerencianet e, necessitando de mais detalhes ou informações, entre em contato com nossa consultoria técnica, via nossos [Canais de Comunicação](https://gerencianet.com.br/central-de-ajuda).**

## Instalação

### Instalar usando o [modgit](https://github.com/jreinke/modgit):

    $ cd /path/to/magento
    $ modgit init
    $ modgit add gerencianet https://github.com/gerencianet/gn-api-magento.git

### Instalar manualmente:

- Baixe a [última versão](https://github.com/gerencianet/gn-api-magento/archive/master.zip) do módulo.
- Descompacte o arquivo baixado e copie as pastas app, lib e skin para dentro do diretório principal do Magento*.
- Defina as seguintes permissões:
 
    775 para todos os diretórios;
    644 para todos os arquivos; 
    777 para app/etc/, var/ e media/

isso seria equivalente a executar os comandos:

    sudo find . -type d -exec chmod 755 {} \;
    sudo find . -type f -exec chmod 644 {} \;
    sudo chmod 777 -R app/etc/;
    sudo chmod 777 -R var/;
    sudo chmod 777 -R media/;

- Atualize o cache da sua loja acessando `Sistema > Gerenciador de Cache > Atualizar Cache`.

*Ao substituir as pastas no seu projeto, o sistema pode informar que alguns arquivos serão sobrescritos. Não se preocupe, pode confirmar o procedimento pois a instalação não afeterá nenhum arquivo já existente em seu projeto.

## Requisitos

 - PHP >= 5.4.0
 - Magento 1.7.x, 1.8.x ou 1.9.x

## Configuração

Acessando `Sistema > Configuração > Formas de Pagamento`, 3 novos menus serão mostrados:

- Checkout Transparente Gerencianet
- Boleto Bancário - Gerencianet
- Cartão de Cŕedito - Gerencianet

No **Checkout Transparente Gerencianet**, informe as credenciais da sua aplicação e o identificador da conta, obtidos a partir da sua conta Gerencianet.

Para habilitar e configurar informações do boleto, como as linhas de instrução e a quantidade de dias para vencimento, veja **Boleto Bancário - Gerencianet**.

Para habilitar a opção de cartão de crédito, veja **Cartão de Crédito - Gerencianet**.

Por padrão, o modulo utiliza sempre 4 linhas de endereço (Acesse `Sistema > Configuração > Configuração do cliente > Opções de Nome e Endereço`. Marque 4 no campo Número de linhas), respectivamente **street**, **number**, **complement** e **neighborhood**.

Além disso, a **data de nascimento** é obrigatória para cobranças via cartão e o **cpf** é obrigatório para qualquer tipo de pagamento. Acesse `Sistema > Configuração > Configuração do cliente > Opções de Nome e Endereço`. Marque "obrigatório" para "Exibir Data de Nascimento" e "Exibir CPF/CNPJ".



