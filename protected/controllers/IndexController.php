<?php

class IndexController extends RISBaseController
{

	/**
	 * @param string $style
	 * @param int $width
	 * @param int $zoom
	 * @param int $x
	 * @param int $y
	 */
	public function actionTileCache($style, $width, $zoom, $x, $y)
	{

		if ($width == 256) {
			$boundaries = array(
				3  => array(2, 2, 6, 3),
				4  => array(6, 4, 11, 6),
				5  => array(14, 10, 19, 11),
				6  => array(31, 21, 36, 22),
				7  => array(66, 43, 70, 45),
				8  => array(134, 88, 138, 89),
				9  => array(270, 176, 274, 178),
				10 => array(542, 354, 547, 356),
				11 => array(1086, 708, 1091, 712),
			);
			if (isset($boundaries[$zoom])) {
				$bound       = $boundaries[$zoom];
				$outofbounds = false;
				if ($x < $bound[0] || $y < $bound[1] || $x > $bound[2] || $y > $bound[3]) $outofbounds = true;

				if ($outofbounds) {
					Header("Location: /images/HereBeDragons256.png");
					Yii::app()->end();
				}
			}
		}

		if ($width == 256) {
			$array = array("1", "2", "3");
			$key   = $array[array_rand($array)];
			$url   = "http://tiles" . $key . ".api.skobbler.net/tiles/${zoom}/${x}/${y}.png?api_key=" . Yii::app()->params['skobblerKey'];
		} else {
			$array = array("a", "b", "c");
			$key   = $array[array_rand($array)];
			$url   = "http://$key.tile.cloudmade.com/" . Yii::app()->params['cloudmateKey'] . "/$style/$width/$zoom/$x/$y.png";
		}

		$fp = fopen("/tmp/tiles.log", "a");
		fwrite($fp, $url . "\n");
		fclose($fp);


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$string = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($status == 200 && $string != "") {
			if (!file_exists(TILE_CACHE_DIR . $style)) mkdir(TILE_CACHE_DIR . $style, 0775);
			if (!file_exists(TILE_CACHE_DIR . "$style/$width")) mkdir(TILE_CACHE_DIR . "$style/$width", 0775);
			if (!file_exists(TILE_CACHE_DIR . "$style/$width/$zoom")) mkdir(TILE_CACHE_DIR . "$style/$width/$zoom", 0775);
			if (!file_exists(TILE_CACHE_DIR . "$style/$width/$zoom/$x")) mkdir(TILE_CACHE_DIR . "$style/$width/$zoom/$x", 0775);
			file_put_contents(TILE_CACHE_DIR . "$style/$width/$zoom/$x/$y.png", $string);
			Header("Content-Type: image/png");
			echo $string;
		} else {
			Header("Content-Type: text/plain");
			echo $status;
			var_dump($ch);
		}
		Yii::app()->end();
	}

	public function actionFeed()
	{
		if (isset($_REQUEST["krit_typ"])) {
			$krits = RISSucheKrits::createFromUrl();
			$titel = "Ratsinformant: " . $krits->getTitle();

			$solr   = RISSolrHelper::getSolrClient("ris");
			$select = $solr->createSelect();

			$krits->addKritsToSolr($select);

			$select->setRows(100);
			$select->addSort('sort_datum', $select::SORT_DESC);

			/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
			$hl = $select->getHighlighting();
			$hl->setFields('text, text_ocr, antrag_betreff');
			$hl->setSimplePrefix('<b>');
			$hl->setSimplePostfix('</b>');

			$ergebnisse = $solr->select($select);

			$data = RISSolrHelper::ergebnisse2FeedData($ergebnisse);
		} else {
			$data = array();
			/** @var array|RISAenderung[] $aenderungen */
			$aenderungen = RISAenderung::model()->findAll(array("order" => "id DESC", "limit" => 100));
			foreach ($aenderungen as $aenderung) $data[] = $aenderung->toFeedData();
			$titel = "Ratsinformant Änderungen";
		}

		$this->render("feed", array(
			"feed_title"       => $titel,
			"feed_description" => $titel,
			"data"             => $data,
		));
	}

	public function actionAjaxEmailIstRegistriert($email)
	{
		$person = BenutzerIn::model()->findAll(array(
			"condition" => "email='" . addslashes($email) . "' AND pwd_enc != ''"
		));
		if (count($person) > 0) {
			/** @var BenutzerIn $p */
			$p = $person[0];
			if ($p->email_bestaetigt) echo "1";
			else echo "0";
		} else {
			echo "-1";
		}
	}

	/**
	 * @param RISSucheKrits $curr_krits
	 * @param string $code
	 * @return array
	 */
	protected function sucheBenachrichtigungenAnmelden($curr_krits, $code)
	{
		$user = Yii::app()->getUser();

		$correct_person      = null;
		$wird_benachrichtigt = false;

		list($msg_ok, $msg_err) = $this->performLoginActions();

		if (!$user->isGuest) {
			/** @var BenutzerIn $ich */
			$ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));

			if (AntiXSS::isTokenSet("benachrichtigung_add")) {
				$ich->addBenachrichtigung($curr_krits);
			}
			if (AntiXSS::isTokenSet("benachrichtigung_del")) {
				$ich->delBenachrichtigung($curr_krits);
			}

			$wird_benachrichtigt = $ich->wirdBenachrichtigt($curr_krits);
		}


		if ($user->isGuest) {
			$ich              = null;
			$eingeloggt       = false;
			$email_angegeben  = false;
			$email_bestaetigt = false;
		} else {
			$eingeloggt = true;
			/** @var BenutzerIn $ich */
			if (!$ich) $ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));
			if ($ich->email == "") {
				$email_angegeben  = false;
				$email_bestaetigt = false;
			} elseif ($ich->email_bestaetigt) {
				$email_angegeben  = true;
				$email_bestaetigt = true;
			} else {
				$email_angegeben  = true;
				$email_bestaetigt = false;
			}
		}

		return array(
			"eingeloggt"          => $eingeloggt,
			"email_angegeben"     => $email_angegeben,
			"email_bestaetigt"    => $email_bestaetigt,
			"wird_benachrichtigt" => $wird_benachrichtigt,
			"ich"                 => $ich,
			"msg_err"             => $msg_err,
			"msg_ok"              => $msg_ok,
		);
	}


	/**
	 * @param AntragDokument[] $dokumente
	 * @param null|RISSucheKrits $filter_krits
	 * @return array
	 */
	protected function dokumente2geodata(&$dokumente, $filter_krits = null)
	{
		$geodata = array();
		foreach ($dokumente as $dokument) {
			if ($dokument->antrag) {
				$link = $dokument->antrag->getLink();
				$name = $dokument->antrag->getName();
			} elseif ($dokument->termin) {
				$link = $dokument->termin->getLink();
				$name = $dokument->termin->getName();
			} else {
				$link = $name = "";
			}
			if (strlen($name) > 150) $name = substr($name, 0, 148) . "...";
			if ($link != "") $link = "<div class='antraglink'>" . CHtml::link($name, $link) . "</div>";
			foreach ($dokument->orte as $ort) if ($ort->ort->to_hide == 0 && ($filter_krits === null || $filter_krits->filterGeo($ort->ort))) {
				$str = $link;
				$str .= "<div class='ort_dokument'>";
				$str .= "<div class='ort'>" . CHtml::encode($ort->ort->ort) . "</div>";
				$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</div>";
				$str .= "</div>";
				$geodata[] = array(
					FloatVal($ort->ort->lat),
					FloatVal($ort->ort->lon),
					$str
				);
			}
		}
		return $geodata;
	}

	/**
	 * @param RISSucheKrits $krits
	 * @param \Solarium\QueryType\Select\Result\Result $ergebnisse
	 * @return array
	 */
	protected function getJSGeodata($krits, $ergebnisse)
	{
		$geo            = $krits->getGeoKrit();
		$solr_dokumente = $ergebnisse->getDocuments();
		$dokument_ids   = array();
		foreach ($solr_dokumente as $dokument) {
			$x              = explode(":", $dokument->id);
			$dokument_ids[] = IntVal($x[1]);
		}
		$geodata = array();
		if (count($dokument_ids) > 0) {
			$lat        = FloatVal($geo["lat"]);
			$lng        = FloatVal($geo["lng"]);
			$dist_field = "(((acos(sin(($lat*pi()/180)) * sin((lat*pi()/180))+cos(($lat*pi()/180)) * cos((lat*pi()/180)) * cos((($lng- lon)*pi()/180))))*180/pi())*60*1.1515*1.609344) <= " . FloatVal($geo["radius"] / 1000);
			$SQL        = "select a.dokument_id, b.* FROM antraege_orte a JOIN orte_geo b ON a.ort_id = b.id WHERE a.dokument_id IN (" . implode(", ", $dokument_ids) . ") AND b.to_hide = 0 AND $dist_field";
			$result     = Yii::app()->db->createCommand($SQL)->queryAll();
			foreach ($result as $geo) {
				/** @var AntragDokument $dokument */
				$dokument = AntragDokument::model()->findByPk($geo["dokument_id"]);

				if ($dokument->antrag) {
					$link = $dokument->antrag->getLink();
					$name = $dokument->antrag->getName();
				} elseif ($dokument->termin) {
					$link = $dokument->termin->getLink();
					$name = $dokument->termin->getName();
				} else {
					$link = $name = "";
				}
				if (strlen($name) > 150) $name = substr($name, 0, 148) . "...";
				if ($link != "") $link = "<div class='antraglink'>" . CHtml::link($name, $link) . "</div>";
				$str = $link;
				$str .= "<div class='ort_dokument'>";
				$str .= "<div class='ort'>" . CHtml::encode($geo["ort"]) . "</div>";
				$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</div>";
				$str .= "</div>";
				$geodata[] = array(
					FloatVal($geo["lat"]),
					FloatVal($geo["lon"]),
					$str
				);
			}

		}
		return $geodata;
	}


	/**
	 * @param Antrag[] $antraege
	 * @param int $typ
	 * @return array
	 */
	protected function antraege2geodata(&$antraege, $typ = 0)
	{
		$geodata          = $geodata_overflow = array();
		$geodata_nach_dok = array();
		foreach ($antraege as $ant) {
			foreach ($ant->dokumente as $dokument) {
				foreach ($dokument->orte as $ort) if ($ort->ort->to_hide == 0) {
					$name = $ant->getName();
					if (strlen($name) > 150) $name = substr($name, 0, 148) . "...";
					$str = "<div class='antraglink'>" . CHtml::link($name, $ant->getLink()) . "</div>";
					$str .= "<div class='ort_dokument'>";
					$str .= "<div class='ort'>" . CHtml::encode($ort->ort->ort) . "</div>";
					$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</div>";
					$str .= "</div>";
					$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');

					if (!isset($geodata_nach_dok[$dokument->id])) $geodata_nach_dok[$dokument->id] = array();
					$geodata_nach_dok[$dokument->id][] = array(
						FloatVal($ort->ort->lat),
						FloatVal($ort->ort->lon),
						$str,
						$typ
					);
				}
			}
		}
		foreach ($geodata_nach_dok as $dok_geo) if (count($dok_geo) >= 20) {
			$geodata_overflow[] = $dok_geo;
		} else {
			foreach ($dok_geo as $d) $geodata[] = $d;
		}

		return array($geodata, $geodata_overflow);
	}

	/**
	 * @param string $datum_max
	 */
	public function actionAntraegeAjaxDatum($datum_max)
	{
		$x    = explode("-", $datum_max);
		$time = mktime(0, 0, 0, $x[1], $x[2], $x[0]);

		$i = 0;
		do {
			$datum = date("Y-m-d", $time - 3600 * 24 * $i);
			/** @var array|Antrag[] $antraege */
			$antraege = Antrag::model()->neueste_stadtratsantragsdokumente(null, $datum . " 00:00:00", $datum . " 23:59:59")->findAll();
			$i++;
		} while (count($antraege) == 0);

		list($geodata, $geodata_overflow) = $this->antraege2geodata($antraege);

		ob_start();
		$this->renderPartial('index_antraege_liste', array(
			"weitere_url" => $this->createUrl("index/antraegeAjaxDatum", array("datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum . " 00:00:00") - 1))),
			"antraege"    => $antraege,
			"datum"       => $datum,
		));

		Header("Content-Type: application/json; charset=UTF-8");
		echo json_encode(array(
			"datum"            => $datum,
			"html"             => ob_get_clean(),
			"geodata"          => $geodata,
			"geodata_overflow" => $geodata_overflow
		));
		Yii::app()->end();
	}

	/**
	 * @param float $lat
	 * @param float $lng
	 */
	public function actionGeo2Address($lat, $lng)
	{
		Header("Content-Type: application/json; charset=UTF-8");
		$naechster_ort = OrtGeo::findClosest($lng, $lat);
		echo json_encode(array(
			"ort_name" => $naechster_ort->ort,
		));
		Yii::app()->end();
	}


	/**
	 * @param float $lat
	 * @param float $lng
	 * @param float $radius
	 * @param int $seite
	 */
	public function actionAntraegeAjaxGeo($lat, $lng, $radius, $seite = 0)
	{
		$krits = new RISSucheKrits();
		$krits->addGeoKrit($lng, $lat, $radius);

		$solr   = RISSolrHelper::getSolrClient("ris");
		$select = $solr->createSelect();

		$krits->addKritsToSolr($select);

		$select->setStart(30 * $seite);
		$select->setRows(30);
		$select->addSort('sort_datum', $select::SORT_DESC);

		$ergebnisse = $solr->select($select);

		/** @var Antrag[] $antraege */
		$antraege       = array();
		$solr_dokumente = $ergebnisse->getDocuments();
		$dokument_ids   = array();
		foreach ($solr_dokumente as $dokument) {
			$x              = explode(":", $dokument->id);
			$dokument_ids[] = IntVal($x[1]);
		}
		foreach ($dokument_ids as $dok_id) {
			/** @var AntragDokument $ant */
			$ant = AntragDokument::model()->with(array(
				"antrag"           => array(),
				"antrag.dokumente" => array(
					"alias"     => "dokumente_2",
					"condition" => "dokumente_2.id IN (" . implode(", ", $dokument_ids) . ")"
				)
			))->findByPk($dok_id);
			if ($ant->antrag) {
				$antraege[$ant->antrag_id] = $ant->antrag;
			}
		}

		$geodata       = $this->getJSGeodata($krits, $ergebnisse);
		$naechster_ort = OrtGeo::findClosest($lng, $lat);
		ob_start();

		$this->renderPartial('index_antraege_liste', array(
			"weitere_url"   => $this->createUrl("index/antraegeAjaxGeo", array("lat" => $lat, "lng" => $lng, "radius" => $radius, "seite" => ($seite + 1))),
			"antraege"      => $antraege,
			"geo_lng"       => $lng,
			"geo_lat"       => $lat,
			"radius"        => $radius,
			"naechster_ort" => $naechster_ort
		));

		Header("Content-Type: application/json; charset=UTF-8");
		echo json_encode(array(
			"datum"         => date("Y-m-d"),
			"html"          => ob_get_clean(),
			"geodata"       => $geodata,
			"krit_str"      => $krits->getJson(),
			"naechster_ort" => $naechster_ort->ort
		));
		Yii::app()->end();
	}


	public function actionSuche($code = "")
	{
		if (isset($_POST["suchbegriff"])) {
			$suchbegriff = $_POST["suchbegriff"];
			$krits       = new RISSucheKrits();
			$krits->addVolltextsucheKrit($suchbegriff);
		} else {
			$krits       = RISSucheKrits::createFromUrl();
			$suchbegriff = $krits->getTitle();
		}

		$this->load_leaflet_css = true;

		$benachrichtigungen_optionen = $this->sucheBenachrichtigungenAnmelden($krits, $code);


		$solr   = RISSolrHelper::getSolrClient("ris");
		$select = $solr->createSelect();

		$krits->addKritsToSolr($select);


		$select->setRows(100);
		$select->addSort('sort_datum', $select::SORT_DESC);

		/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
		$hl = $select->getHighlighting();
		$hl->setFields('text, text_ocr, antrag_betreff');
		$hl->setSimplePrefix('<b>');
		$hl->setSimplePostfix('</b>');

		$facetSet = $select->getFacetSet();
		$facetSet->createFacetField('antrag_typ')->setField('antrag_typ');
		$facetSet->createFacetField('antrag_wahlperiode')->setField('antrag_wahlperiode');

		$ergebnisse = $solr->select($select);

		if ($krits->isGeoKrit()) $geodata = $this->getJSGeodata($krits, $ergebnisse);
		else $geodata = null;

		$this->render("suchergebnisse", array_merge(array(
			"krits"       => $krits,
			"suchbegriff" => $suchbegriff,
			"ergebnisse"  => $ergebnisse,
			"geodata"     => $geodata,
		), $benachrichtigungen_optionen));
	}


	public function actionDokument($id)
	{
		/** @var AntragDokument $dokument */
		$dokument = AntragDokument::model()->findByPk($id);
		try {
			$morelikethis = $dokument->solrMoreLikeThis();
		} catch (Exception $e) {
			$morelikethis = null;
		}
		$this->render("dokument_intern", array(
			"dokument"     => $dokument,
			"morelikethis" => $morelikethis,
		));
	}

	public function actionBa($ba_nr)
	{
		$this->top_menu = "ba";

		$this->load_leaflet_css      = true;
		$this->load_leaflet_draw_css = true;

		$tage_zukunft                 = 30;
		$tage_vergangenheit           = 30;
		$tage_vergangenheit_dokumente = 30;

		$datum_bis = date("Y-m-d");
		$datum_von = date("Y-m-d", time() - $tage_vergangenheit_dokumente * 24 * 3600);

		/** @var array|Antrag[] $antraege1 */
		$antraege1 = Antrag::model()->neueste_stadtratsantragsdokumente($ba_nr, $datum_von . " 00:00:00", $datum_bis . " 23:59:59")->findAll();
		/** @var array|Antrag[] $antraege2 */
		$antraege2 = Antrag::model()->neueste_stadtratsantragsdokumente_geo($ba_nr, $datum_von . " 00:00:00", $datum_bis . " 23:59:59")->findAll();

		$antraege = $antraege1;
		$a_ids    = array();
		foreach ($antraege1 as $a) $a_ids[] = $a->id;
		foreach ($antraege2 as $a) if (!in_array($a->id, $a_ids)) $antraege[] = $a;
		usort($antraege, function ($a1, $a2) {
			/** @var Antrag $a1 */
			/** @var Antrag $a2 */
			$ts1 = RISTools::date_iso2timestamp($a1->datum_letzte_aenderung);
			$ts2 = RISTools::date_iso2timestamp($a2->datum_letzte_aenderung);
			if ($ts1 > $ts2) return -1;
			if ($ts1 < $ts2) return 1;
			return 0;
		});

		list($geodata1, $geodata_overflow1) = $this->antraege2geodata($antraege1);
		list($geodata2, $geodata_overflow2) = $this->antraege2geodata($antraege2, 1);
		$geodata          = array_merge($geodata1, $geodata2);
		$geodata_overflow = array_merge($geodata_overflow1, $geodata_overflow2);

		$termine_zukunft       = Termin::model()->termine_stadtrat_zeitraum($ba_nr, date("Y-m-d 00:00:00", time()), date("Y-m-d 00:00:00", time() + $tage_zukunft * 24 * 3600), true)->findAll();
		$termine_vergangenheit = Termin::model()->termine_stadtrat_zeitraum($ba_nr, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
		$termin_dokumente      = Termin::model()->neueste_stadtratsantragsdokumente($ba_nr, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();


		$ba = Bezirksausschuss::model()->findByPk($ba_nr);
		$this->render("ba_uebersicht", array(
			"ba"                           => $ba,
			"antraege"                     => $antraege,
			"geodata"                      => $geodata,
			"geodata_overflow"             => $geodata_overflow,
			"termine_zukunft"              => $termine_zukunft,
			"termine_vergangenheit"        => $termine_vergangenheit,
			"termin_dokumente"             => $termin_dokumente,
			"tage_vergangenheit"           => $tage_vergangenheit,
			"tage_zukunft"                 => $tage_zukunft,
			"tage_vergangenheit_dokumente" => $tage_vergangenheit_dokumente,
		));
	}


	public function actionStadtrat()
	{
		$this->top_menu = "stadtrat";
		$this->performLoginActions();

		$this->load_leaflet_css      = true;
		$this->load_leaflet_draw_css = true;

		$i = 0;
		do {
			$datum = date("Y-m-d", time() - 3600 * 24 * $i);
			/** @var array|Antrag[] $antraege */
			$antraege = Antrag::model()->neueste_stadtratsantragsdokumente(null, $datum . " 00:00:00", $datum . " 23:59:59")->findAll();
			$i++;
		} while (count($antraege) == 0);

		list($geodata, $geodata_overflow) = $this->antraege2geodata($antraege);

		$tage_zukunft       = 7;
		$tage_vergangenheit = 7;

		$termine_zukunft       = Termin::model()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time()), date("Y-m-d 00:00:00", time() + $tage_zukunft * 24 * 3600), true)->findAll();
		$termine_vergangenheit = Termin::model()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
		$termin_dokumente      = Termin::model()->neueste_stadtratsantragsdokumente(0, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();

		$this->render('stadtrat_uebersicht', array(
			"weitere_url"           => $this->createUrl("index/antraegeAjaxDatum", array("datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum . " 00:00:00") - 1))),
			"antraege"              => $antraege,
			"geodata"               => $geodata,
			"geodata_overflow"      => $geodata_overflow,
			"datum"                 => $datum,
			"termine_zukunft"       => $termine_zukunft,
			"termine_vergangenheit" => $termine_vergangenheit,
			"termin_dokumente"      => $termin_dokumente,
			"tage_vergangenheit"    => $tage_vergangenheit,
			"tage_zukunft"          => $tage_zukunft,
			"fraktionen"            => StadtraetIn::getGroupedByFraktion(date("Y-m-d")),
		));
	}


	public function actionInfos()
	{
		$this->top_menu = "infos";
		$this->render('infos');
	}

	public function actionImpressum()
	{
		$this->top_menu = "impressum";
		$this->render('impressum');
	}

	/**
	 *
	 */
	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		} else {
			$this->render('error', array("code" => 400, "message" => "Ein Fehler ist aufgetreten"));
		}
	}

	/**
	 *
	 */
	public function actionIndex()
	{
		$this->actionStadtrat();
	}

}
