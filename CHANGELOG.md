# v0.4.1
- Fix: Atualização no link do boleto gerado, agora o link encaminha para um PDF.

# v0.4.0
- Delete: Bandeiras jcb, aura e discover do checkout da Gerencianet.
- Add: Bandeira hipertcard no checkout da Gerencianet.

# v0.3.0
- Add: Metodo de pagamento por boleto no painel administrativo.

# v0.2.6
- Fix: Converte preço de produto para Real brasileiro caso o preço base não esteja configurado com moeda BRL.

# v0.2.5
- Fix: Pacotes e kits de produtos passam a ser considerados nas emissões.

# v0.2.4
- Added: Configuração de multa e Juros para Boleto Bancário
- Fix: Gera transa~ção apenas com itens visíveis (não considera os produtos pais).

# v0.2.3
- Fix: validação e autopreenchimento de CPF/CNPJ

# v0.2.2
- Removida obrigatoriedade de CPF para cobranças pagas com CNPJ

# v0.2.1
- Fix: Compatibilidade com IE11
- Fix: Recarregar parcelas automaticamente

# v0.2.0
- Novo layout para pagamentos
- Fix: Validação de campos
- Fix: Preenchimento automático de dados

# v0.1.9

- Fix: Validação de campos
- Fix: Preenchimento automático de dados
- Fix: Compatibilidade com Checkout Venda Mais

# v0.1.8

- Added: Pagamento com CNPJ para Boleto e Cartão
- Added: Identificação automática se cliente é pessoa física ou jurídica
- Fix: Campos não obrigatórios para Boleto
- Fix: Validação de campos
- Fix: Tratamento de mensagens de erro
- Fix: Solicitar preenchimento de dados caso não seja digitado nos passos anteriores
- Fix: Validação do valor mínimo para pagar com a Gerencianet

# v0.1.0

- Primeira versão estável.
- Fix: Bug ao pagar com cartão onde o payment_token não era inserido corretamente.
- Fix: Gerar boleto sem configurar linhas digitáveis causava problema no módulo.
- Added: Adicionado logs em /[project_dir]/var/log/gerencianet.log
