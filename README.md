**Adote um Amigo**

Sistema Web desenvolvido como Projeto Integrador da faculdade UCEFF. O objetivo é facilitar a adoção de animais de estimação, conectando doadores e adotantes de forma rápida, segura e moderna.

**Funcionalidades**

- **Cadastro de Pets:** Permite cadastrar animais com múltiplas fotos, raça, idade (meses/anos), porte, cidade e WhatsApp para contato.
- **Busca e Filtros:** Visitantes podem buscar pets por nome, raça, espécie e porte.
- **Integração com IBGE:** Preenchimento automático de Cidades e Estados (UF) consumindo a API pública do IBGE.
- **Contato Inteligente:** Botão que gera um link dinâmico para o WhatsApp do doador com mensagem pré-formatada.
- **Painel Administrativo:** Área restrita com login para o administrador visualizar, cadastrar e excluir os pets.

**Tecnologias Utilizadas**

- **Back-end:** PHP (Puro)
- **Banco de Dados:** MySQL
- **Front-end:** HTML, CSS, JavaScript e Bootstrap 5
- **Integrações:** API de Localidades do IBGE
- **Ambiente:** XAMPP (Apache)

**Como rodar o projeto localmente**

1. Instale o [XAMPP](https://www.apachefriends.org/).
2. Inicie os serviços **Apache** e **MySQL** no painel do XAMPP.
3. Clone este repositório na pasta `htdocs` do XAMPP:
   `git clone https://github.com/boitovsky/adocao.git`
4. Abra o phpMyAdmin e crie um banco de dados chamado `adocao_pets`.
5. Importe o arquivo SQL (disponível na pasta `banco_de_dados` do projeto) ou crie as tabelas manualmente.
6. Acesse no navegador: `http://localhost/adocao`

**Acesso ao Painel Admin**

- **E-mail:** admin@admin.com
- **Senha:** 123456

---
Desenvolvido por Gabriel Henrique Boita (boitovsky).
