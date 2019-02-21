<?php

/* 
	Afficher des stats (minimales) pour les accès et téléchargements d'un livre
	---------------------------------------------------------------------------

	La visualisation des données se fait avec Google Charts
	https://developers.google.com/chart/

	Pour le reste : 

	MIT License
	Copyright (c) 2019 Sébastien Delahaye <sebastien@delahaye.net>

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.	 

*/

/*
	Début des variables à modifier

*/

// le titre de la page
$titre = "Statistiques : les Coulisses d'une usine à succès";

// Le fichier CSV de statistiques qu'on va interroger.
$stat_fichier = 'statistiques.csv';

/*
	Fin des variables à modifier

*/

// 	On vérifie que le fichier existe (sinon on affiche un message d'erreur tip top)
if (!file_exists($stat_fichier)) {
	echo "<html><head><title>$titre</title><link href=\"https://fonts.googleapis.com/css?family=Lato:300,400\" rel=\"stylesheet\" /><style>html,body{color:#f29278;margin:0;padding:0;width:100%;height:100%;display:table;}h1{font-family:'Lato',serif;height:100%;text-align:center;vertical-align:middle;display:table-cell;margin:auto;width:100%;}</style></head><body><h1>⚠️ Fichier CSV non trouvé ⚠️</h1></html>";
	exit;
}

/* 
	On va ouvrir le fichier de statistiques en lecture et importer tout son
	contenu (moins la première ligne, qui contient uniquement les intitulés de
	champs, d'où la variable $fichier_ligne) dans un tableau PHP.

*/
$fichier_ligne = 1;
ini_set('auto_detect_line_endings',TRUE); // ça c'est pour la compatibilité mac
if (($fichier = fopen($stat_fichier, 'r+')) !== FALSE) {
	while (($stat_anciennes = fgetcsv($fichier, 1000, ',')) !== FALSE) {
		if ($fichier_ligne == 1) {
			$fichier_ligne++;
		} else {
			$stat[] = array($stat_anciennes[0],
							$stat_anciennes[1],
							$stat_anciennes[2],
							$stat_anciennes[3],
							$stat_anciennes[4],
							$stat_anciennes[5]);
		}
	}
	fclose($fichier);
} 

/* 
	Par confort, on stocke le dernier jour et l'avant-dernier jour dans des
	tableaux séparés.

*/
$nb_jours = count($stat);
$dernier_jour = $stat[$nb_jours-1];
$penultieme_jour = $stat[$nb_jours-2];

//Début du HTML avec du PHP dedans, et puis du Javascript avec du PHP dedans
?><html lang="fr">
	<head>
		<meta charset="utf-8" />
		<title><?= $titre; ?></title>
		<meta name="robots" content="noindex, nofollow" />
		<link href="https://fonts.googleapis.com/css?family=Lato:300,400" rel="stylesheet" />
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load('current', {'packages':['corechart'], 'language':'fr'});
			google.charts.setOnLoadCallback(drawChart);
			function drawChart() {
				var data = new google.visualization.DataTable();
				data.addColumn('date', 'Date');
				data.addColumn('number', 'web');
				data.addColumn('number', 'pdf');
				data.addColumn('number', 'epub');
				data.addColumn('number', 'azw');
				data.addColumn('number', 'total');
				data.addRows([ 
<?php 
				/* 
					On reprend notre tableau PHP et on le transforme en tableau Javascript
					(car on aime souffrir mais surtout car il y en a besoin pour visualiser
					les données).

				*/
				foreach($stat as $ligne) {
					$y = substr($ligne[0],0,4);
					// Ahah, on enlève 1 car en javascript les mois commencent à zéro...
					$m = substr($ligne[0],4,2)-1;
					// En revanche les jours commencent à 1, j'adore javascript
					$d = substr($ligne[0],6,2);
					$web  = $ligne[1];
					$pdf = $ligne[2];
					$epub = $ligne[3];
					$azw = $ligne[4];
					$total = $ligne[5];
					echo "\t\t\t\t\t[new Date($y,$m,$d),$web,$pdf,$epub,$azw,$total],\n";
				}
				?>
				]);
	
				// Les options de la visualisation
				var options = {
					'width':700,
					'height':300,
					'colors':['#f29278','#182a46','#838893'],
				};
				/* 
					Le type de graphique que vous voulez. Beaucoup d'options disponibles
					dans Google Charts, je me contente de LineChart car je suis un homme
					simple.

				*/
				var chart = new google.visualization.LineChart(document.getElementById('viz'));
				chart.draw(data, options);
			}
		</script>
		<style>
			* {
				padding:0;
				margin:0;
				font-family:'Lato',serif;
				font-weight:300;
				text-transform:uppercase;
			}
			header, main {
				width:700px;
				margin:15px auto 15px auto;	
			}
			h1 {
				text-align:center;
				font-weight:bold;
				margin-top:30px;
			}
			#intro {
				display:flex;
				margin-top:50px;
			}
			#chart {
				margin-top:30px;
			}
			#total {
				width:350px;
			}
			#hier {
				width:350px;
				text-align:center;
				max-height:100%;
			}
			#hier span:first-child {
				display:block;
				font-size:300%;
				margin:50px auto 30px auto;
				font-weight:bold;
			}
			table {
				width:100%;
				margin-top:15px;
			}
			tr:first-child td {
				font-weight:bold;
			}
		</style>
	</head>
	<body>
		<header>
			<h1>Statistiques <?= date('d/m/Y');?></h1>
		</header>
		<main>
			<div id="intro">
			<div id="total">
				<h2>Nombre d'accès</h2>
				<table>
					<tr>
						<td>Total :</td>
						<td><?= $dernier_jour[5]; ?></td>
					</tr>
					<tr>
						<td>Medium :</td>
						<td><?= $dernier_jour[1]; ?></td>
					</tr>
					<tr>
						<td>pdf :</td>
						<td><?= $dernier_jour[2]; ?></td>
					</tr>
					<tr>
						<td>epub :</td>
						<td><?= $dernier_jour[3]; ?></td>
					</tr>
					<tr>
						<td>azw :</td>
						<td><?= $dernier_jour[4]; ?></td>
					</tr>
				</table>
			</div>
			<div id="hier">
				<span>+<?= ($dernier_jour[5]-$penultieme_jour[5]); ?></span>
				<span>par rapport au précédent jour</span>
			</div>
			</div>
			<div id="chart">
				<h2>Historique</h2>
				<div id="viz"></div>
			</div>
		</main>
	</body>
</html>