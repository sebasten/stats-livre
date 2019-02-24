<?php

/* 
	Générer un téléchargement d'ebook, ou une redirection web, et logger le tout
	(en gardant la classe)
	----------------------------------------------------------------------------

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

// les noms (sur le serveur) des fichiers à télécharger
$pdf = 'exemple.pdf';
$azw = 'exemple.mobi';
$epub = 'exemple.epub';

// le nom (sur le futur disque dur de votre lecteur) du fichier téléchargé
$filename = "Mon titre";

// la redirection vers la version web
$web = 'http://monblog.com/avecmonlivre';

// l'url vers le fichier sur votre serveur
$url = 'https://site.perso.net/livre/downloads/';

// la page d'accueil du livre (qui est aussi ma 404 dans ce cas)
$home = 'https://site.perso.net/';

// le fichier CSV (avec son path) de statistiques 
$stat_fichier = 'stats/statistiques.csv';

/*
	Fin des variables à modifier

*/

// ça c'est pour la compatibilité mac
ini_set('auto_detect_line_endings',TRUE);

/*
	Une fonction pour créer un compteur pour chaque format et le total, par jour.
	Prend en argument le fichier CSV et l'incrément pour chaque format.

*/
function statCsv($stat_fichier,$i_web,$i_pdf,$i_epub,$i_mobi) {
	$fichier_ligne = 1;
	$today = date('Ymd');

	// on vérifie que le fichier existe. Sinon tant pis, on va pas bloquer le download pour ça
	if (file_exists($stat_fichier)) {
		// on commence par lire le fichier actuel et tout mettre dans un tableau PHP
		if (($fichier = fopen($stat_fichier, 'r+')) !== FALSE) {
			while (($stat_anciennes = fgetcsv($fichier, 1000, ',')) !== FALSE) {
				if ($fichier_ligne == 1) {
					// la première ligne c'est les intitulés, on les recrée en début de tableau
					$stat[] = array('date', 'web','pdf','epub','mobi','total');
					$fichier_ligne++;
				} else {
					if ($stat_anciennes[0] == $today) {
						/* 
							Si le dernier jour existe déjà, on reprend ses données.
							Sinon, on ne fait rien.
							Autrement dit, on reprend les données de la veille.
	
						*/
						if (!empty($stat_anciennes[1])) { $web = $stat_anciennes[1]; }
						if (!empty($stat_anciennes[2])) { $pdf = $stat_anciennes[2]; }
						if (!empty($stat_anciennes[3])) { $epub = $stat_anciennes[3]; }
						if (!empty($stat_anciennes[4])) { $mobi = $stat_anciennes[4]; }
						if (!empty($stat_anciennes[5])) { $total = $stat_anciennes[5]; }
					} else {
						// là du coup on stocke les données pour le lendemain, au cas où
						$web = $stat_anciennes[1];
						$pdf = $stat_anciennes[2];
						$epub = $stat_anciennes[3];
						$mobi = $stat_anciennes[4];
						$total = $stat_anciennes[5];
						// et on remplie le tableau
						$stat[] = array($stat_anciennes[0],
										$stat_anciennes[1],
										$stat_anciennes[2],
										$stat_anciennes[3],
										$stat_anciennes[4],
										$stat_anciennes[5]);
					}
				}
			}
			// On met le dernier jour hors de la boucle comme ça on peut bien le créer
			$stat[] = array($today,
							$web+$i_web,
							$pdf+$i_pdf,
							$epub+$i_epub,
							$mobi+$i_mobi,
							$total+1);
			fclose($fichier);
			// Maintenant qu'on a un beau tableau, on réécrit tout de zéro dans le CSV
			$fichier = fopen($stat_fichier,'w+');
			foreach($stat as $lignes) {
				fputcsv($fichier,$lignes);
			}
			fclose($fichier);
		}
	}
}

/*
	Le Redirector 3000 : 
	- si pas d'argument, ou un mauvais argument -> renvoie vers l'accueil
	- si web -> redirige vers le web et ajoute 1 aux stats
	- pdf, epub ou mobi, télécharge le fichier dans le bon format et +1 aux stats

*/
if (isset($_GET['redirect'])) {
	if ($_GET['redirect'] == 'web') {
		statCsv($stat_fichier,1,0,0,0);
		header('Location: '.$web);
		exit;
	} elseif ($_GET['redirect'] == 'pdf') {
		statCsv($stat_fichier,0,1,0,0);
		header("Content-Type: application/pdf");
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=$filename.pdf");
		readfile($url.$pdf);
	} elseif (isset($_GET['redirect']) && $_GET['redirect'] == 'mobi') {
		statCsv($stat_fichier,0,0,0,1);
		header("Content-Type: application/x-mobipocket-ebook");
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=$filename.mobi");
		readfile($url.$mobi);
	} elseif (isset($_GET['redirect']) && $_GET['redirect'] == 'epub') {
		statCsv($stat_fichier,0,0,1,0);
		header("Content-Type: application/epub+zip");
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=$filename.epub");
		readfile($url.$epub);
	} else {
		header('Location: '.$home);
		exit;
	}
} else {
	header('Location: '.$home);
	exit;
}
?>
