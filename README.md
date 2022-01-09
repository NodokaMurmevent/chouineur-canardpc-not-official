## CHOUINEURS ! CHOUINEUSE ! JE VOUS AI, COMPRIS !

Tentative de rassembler les chouinneries infinis des canards en manque de contenu de manier plus centralisé

N’est PAS soutenu par la rédaction de Canard PC c’est un projet uniquement personnel


## MISE EN GARDE AVANT TOUT USAGE !

Ce projet a comme défaut d’envoyez des requettes plein la gueule à site de canard PC

**MERCI d’y aller ULTRA molo** si vous vous preniez à l’idée de faire tourner le controller /populate

Si vous vous amusez à DDOS le site de CPC ça vas pas le faire !

### Pour installer le machin : 

1. installez symfony <https://symfony.com/download>
1. installez yarn
2. clonez ce github
3
    A. faites `composer install` dans le dossier cloné
    B. faites `yarn install` dans le dossier cloné (peut etre remplacé par npm mais demerdez vous :D )
    C. faites `yarn build` dans le dossier cloné (peut etre remplacé par npm mais demerdez vous :D )
4. lancez le server local de dev symfony : `symfony server:start`
5. `symfony console doctrine:database:create`
6. `symfony console doctrine:schema:update`
7. vous avez un serveur de dev qui fonctionne ici : https://127.0.0.1:8000/


### TODO : 

- [ ] Automatiser la mise à jour journalière des articles de 2 derniers mois.
- [ ] Automatiser la mise à jour hebdomadaire des anciens articles.
- [ ] Faire un design. Ça peut etre bien aussi.
- [ ] Trouver une solution alternative à l‘update des chouineurs.