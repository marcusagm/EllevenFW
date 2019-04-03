# Elleven Framework

O Elleven Framework é um framework PHP com o intuito de possibilitar a criação de
projetos PHP de pequeno ou grande porte. Foi criado para lidar com a arquitetura
básica de projetos WEB, não para agregar uma vasta variedade de recursos. Sempre
procurando atender as seguintes premissas:

 - Flexibilidade
 - Produtividade
 - Simplicidade
 - Performance

A **flexibilidade** é garantida por ser simples definir a organização do projeto e
codificar sem se preocupar com implementações nativas do framework, permitindo a
escolha e utilização de qualquer biblioteca disponível sem perder horas trabalhando
em configurações.

A **produtividade** é aumentada devido as ferramentas para auxiliar na criação dos
requisitos do projeto em todas as camadas. Alem de possuir ferramentas auxiliares da
própria framework, existe tambem o Elleven Tools que fornece interface gráfica para
apoio ao desenvolvimento, como a inicialização do projeto, geração de códigos,
analise de performance, backup, analise de logs, etc.

A **simplicidade** se dá por não possuir demasiadas bibliotecas mantendo uma
estrutura fácil de entender e utilizar. Apesar de não oferecer alguns recursos comuns
à maioria dos projetos, permite a fácil utilização de bibliotecas de terceiros
dando maior conforto durante o desenvolvimento e planejamento e viabilizando um
aprendizado rápido e sólido.

A **performance** apresenta bons resultados, pois implementa muitas das recomendações
que visam a otimização tanto do *back-end* quanto do *front-end*, alem de possuir uma
arquitetura leve e bem definida.


phpunit --bootstrap tests/bootstrap.php  --configuration phpunit.xml