# Magento Module Gerencianet #

## Instalação

- Baixe a [última versão]() do módulo.
- Descompacte o arquivo baixado e copie as pastas app, lib e skin para dentro do diretório principal do Magento*.
- Defina as permissões 755 e 644 para as pastas code e etc, respectivamente.
- Atualize o cache da sua loja acessando `Sistema > Gerenciador de Cache > Atualizar Cache`.

*Ao substituir as pastas no seu projeto, o sistema pode informar que alguns arquivos serão sobrescritos. Não se preocupe, pode confirmar o procedimento pois a instalação não afeterá nenhum arquivo já existente em seu projeto.

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



