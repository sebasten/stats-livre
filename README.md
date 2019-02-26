# Un script pour proposer un ebook en téléchargement dans plusieurs formats  avec des stats (minimales)
J'ai bricolé ce script pour la ressortie de mon ebook *[Call of Duty : les Coulisses d'une usine à succès](https://sebastien.delahaye.net/callofcoulisses/)*. C'est pensé pour mon usage mais peut-être que ça sera utile à d'autres.

## Sommaire
* [Qu'est-ce que ça fait]()
* [Comment ça marche ?]()
  * [index.php]()
  * [.htaccess]()
  * [stats/index.php]()
  * [stats/statistiques.csv]()
* [Et après ?]()
* [À l'aide ?!]()
* [Bonus : faire reconnaître son bouquin par les logiciels bibliographiques]()
* [Licence](#Licence)

## Qu'est-ce que ça fait ?
* gère plusieurs formats d'ebook (pdf, epub, mobi) en téléchargement
* permet une redirection vers un site web (wahou) ;
* propose des urls propres, quels que soient les noms des fichiers ;
* permet d'avoir des noms différents sur le serveur et en téléchargement (ainsi même si vous uploadez sur le serveur votre livrev2_final_fulldef_valide.pdf, le fichier téléchargé s'appellera quand même "Le Guide ultime des guides.pdf") ; 
* enregistre des stats (minimales) sans base de données, dans un fichier .csv : un compteur par format + un compteur total, le tout par jour ;
* propose une interface (minimale) de visualisation de ces stats. Exemple de page (aux données fantaisistes) : 
![Exemple d'affichage des statistiques](https://sebastien.delahaye.net/callofcoulisses/media/stats-livres.png)

## Comment ça marche ?
Il vous faudra un serveur avec PHP et mod_rewrite. Tout ça se découpe en 4 fichiers, qu'il faudra tous ouvrir au moins une fois pour les personnaliser un minimum.

### index.php
C'est le script principal, celui qui gère les téléchargements et qui incrémente les compteurs. Attention, il ne remplace pas votre page d'accueil ! Je conseille de le placer dans un dossier (dans mon cas il se trouve dans /livre/).
Il faut lui modifier plusieurs variables en début de fichier : 
- `$pdf`, `$mobi`, `$epub` : le nom des fichiers .pdf, .mobi ou .epub que vous envoyez sur le serveur (si vous ne les utilisez pas tout, ce n'est pas grave, il suffit de ne pas créer de lien vers le format non utilisé) ; 
- `$filename` : c'est le nom que vous voulez donner au fichier téléchargé **sans l'extension** (par exemple "Le Guide ultime des guides") ; 
- `$web` : si jamais vous publiez aussi une version web (différente de la page d'accueil de votre livre), c'est là qu'il faut mettre son adresse complète ; 
- `$url` : l'URL complète où se trouvent vos fichiers sur le serveur mais **sans les fichiers** (par exemple si vous les mettez dans "/livre/downloads/secret/", il faudra mettre "http://mon.site/livre/downloads/secret/") ; 
- `$home` : la page de votre site (pour reprendre mon exemple brillant, "http://mon.site/"), ça sert aussi de redirection pour l'erreur 404 ; 
- `$stat_fichier` : le nom du fichier, avec son emplacement, où vous voulez mettre le fichier de statistiques (par exemple statistiques/top_secret/stats.csv").

### .htaccess
C'est le fichier de redirection, qui gère les erreurs 404 et permet d'avoir des URLs propres (par exemple mon.site/livre/pdf/ va télécharger le PDF). Attention, comme le fichier commence par un point (.), certains OS le masquent par défaut. Dans le doute, je vous le remets en intégralité : 

```
Options +FollowSymlinks 

RewriteEngine On
RewriteRule ^/?epub/?$ /livre/index.php?redirect=epub [L]
RewriteRule ^/?web/?$ /livre/index.php?redirect=web [L]
RewriteRule ^/?mobi/?$ /livre/index.php?redirect=mobi [L]
RewriteRule ^/?pdf/?$ /livre/index.php?redirect=pdf [L]

ErrorDocument 404 /livre/index.php?redirect=erreur
```

Vous l'avez compris, il faut cinq fois de suite indiquer l'emplacement sur le serveur du fichier index.php que l'on vient de modifier. 

### stats/index.php
C'est le fichier qui permet d'afficher les statistiques. Vous pouvez bien sûr le mettre dans un autre répertoire, le protéger par un .htpasswd, peu importe. Trois variables à modifier : 
- `$titre` : une coquetterie, le nom de la page de statistiques ("Les stats de mon livre", par exemple).
Vous pouvez aussi modifier le graph Google Chart, mais là ce sera au milieu du fichier ; 
- `$in_flames` : une autre coquetterie. Si c'est à `1`, rajoute des flammes sur le compteur du jour. Si vous préférez le bon goût et la tristesse, mettez-le à zéro ; 
- `$stat_fichier` : un truc super important puisque c'est l'emplacement du fichier CSV de statistiques par rapport au fichier actuel (s'il est dans le même dossier et qu'il s'appelle stats.csv, vous mettez donc "stats.csv" ; s'il est dans un sous-dossier, vous mettez "top_secret/stats.csv" ou ce que vous voulez). Si vous vous plantez, vous aurez la chance de voir une très belle page d'erreur que j'ai fait juste pour vous (car sur mon site ça marche). Bon allez, je vous la mets quand même, on s'amuse comme on peut.
![Exemple de page d'erreur](https://sebastien.delahaye.net/callofcoulisses/media/stats-error.png)

### stats/statistiques.csv
Un fichier .csv avec une ligne par défaut, pour commencer. Vous le placez où vous voulez tant que ça correspond avec les valeurs de `$stat_fichier` dans les deux fichiers PHP. Je vous recommande, juste avant de lancer votre bouquin, d'éditer la ligne avec la date de la veille (au format Ymd tout collé, donc 20191225 pour le 25 décembre 2019 par exemple), afin d'avoir une première valeur à zéro proche des autres sur votre graph.

### Et après ?
Après, il suffit de faire la page de votre livre des liens correspondant à ce que vous avez défini. Par exemple, si le script est dans le dossier "livre" et que vous avez défini un pdf, faites un lien vers http://mon.site/livre/pdf/ (ou http://mon.site/livre/mobi/ pour la version Mobi, http://mon.site/livre/epub/ pour la version epub, http://mon.site/livre/web/ pour la redirection, etc. Vous pouvez même en rajouter, le code n'est pas trop complexe).

## À l'aide ?!
Si vous avez encore un doute sur comment utiliser tout ça, ou si vous n'y arrivez pas, n'hésitez pas à m'écrire (<sebastien@delahaye.net>). Je ne garantis pas une réponse rapide ou utile mais je verrai ce que je peux faire.

## Bonus : faire reconnaître son bouquin par les logiciels bibliographiques
Comment faire pour que Zotero (ou Endnote, Mendeley, etc.) reconnaisse que votre page abrite un livre et le récupère ? Il y a des balises meta pour ça. Une partie de celles [que j'utilise](https://sebastien.delahaye.net/callofcoulisses/) (en plus des balises meta classiques, open graph et twitter) et qui semblent faire le job : 
```
<meta name="citation_author" content="Nom, Prénom" />
<meta name="citation_title" content="Titre" />
<meta name="citation_online_date" content="2019-03-04" />
<meta name="citation_publisher" content="éditeur" />
<meta name="citation_keywords" content="des;tags;pertinents" />
<meta name="citation_language" content="fr" />
<meta name="citation_pdf_url" content="URL du PDF !" />

<meta name="dc.title" content="Titre" />
<meta name="dc.creator" content="Nom, Prénom" />
<meta name="dc.publisher" content="éditeur" />
<meta name="dc.format" content="text/html" />
<meta name="dc.date" content="2019-03-04" />
<meta name="dc.language" content="fr" />
<meta name="dc.type" content="book" />
<meta name="dc.created" content="2019-03-04" />
<meta name="dc.description" content="Une description." />
<meta name="dc.subject" content="des;tags;pertinents" />
<meta name="dc.identifier" scheme="URI" content="une url" />
<meta name="dc.rights" content="© Prénom Nom, Année et votre licence s'il y en a une" />

<meta name="eprints.title" content="Titre" />
<meta name="eprints.creators_name" content="Nom, Prénom" />
<meta name="eprints.type" content="book" />
<meta name="eprints.datestamp" content="2019-03-04" />
```
## Licence
MIT License
Copyright (c) 2019 Sébastien Delahaye <sebastien@delahaye.net>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is	furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
