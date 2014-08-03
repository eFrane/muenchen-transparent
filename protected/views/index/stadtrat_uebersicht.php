<?php
/**
 * @var IndexController $this
 * @var array $geodata
 * @var array $geodata_overflow
 * @var Antrag[] $antraege_stadtrat
 * @var Antrag[] $antraege_sonstige
 * @var string $datum
 * @var bool $explizites_datum
 * @var string $neuere_url_ajax
 * @var string $neuere_url_std
 * @var string $aeltere_url_ajax
 * @var string $aeltere_url_std
 * @var Termin[] $termine_zukunft
 * @var Termin[] $termine_vergangenheit
 * @var Termin[] $termin_dokumente
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 * @var array[] $fraktionen
 * @var array $statistiken
 * @var AntragDokument[] $highlights
 */

$this->pageTitle = Yii::app()->name;

$assets_base = $this->getAssetsBase();

/** @var CWebApplication $app */
$app = Yii::app();
/** @var CClientScript $cs */
$cs = $app->getClientScript();

//$cs->registerScriptFile($assets_base . '/js/index.js');
$cs->registerScriptFile('/js/index.js');


/**
 * @var Termin[] $termine
 * @return array[]
 */
function gruppiere_termine($termine)
{
	$data = array();
	foreach ($termine as $termin) {
		$key = $termin->termin . $termin->sitzungsort;
		if (!isset($data[$key])) {
			$ts         = RISTools::date_iso2timestamp($termin->termin);
			$data[$key] = array(
				"id"        => $termin->id,
				"datum"     => strftime("%e. %b., %H:%M", $ts),
				"gremien"   => array(),
				"ort"       => $termin->sitzungsort,
				"tos"       => array(),
				"dokumente" => $termin->antraegeDokumente,
			);
		}
		$url = Yii::app()->createUrl("termine/anzeigen", array("termin_id" => $termin->id));
		if (!isset($data[$key]["gremien"][$termin->gremium->name])) $data[$key]["gremien"][$termin->gremium->name] = array();
		$data[$key]["gremien"][$termin->gremium->name][] = $url;
	}
	foreach ($data as $key => $val) ksort($data[$key]["gremien"]);
	return $data;
}

?>

<div id="mapholder">
	<div id="map"></div>
</div>
<div id="overflow_hinweis" <? if (count($geodata_overflow) == 0) echo "style='visibility: hidden;'"; ?>>
	<label><input type="checkbox" name="zeige_overflow">
		Zeige <span class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?></span> mit über 20 Ortsbezügen
	</label>
</div>
<div id="benachrichtigung_hinweis">
	<div id="ben_map_infos">
		<div class="nichts" style="font-style: italic;">
			<strong>Hinweis:</strong><br>
			Du kannst dich bei <strong>neuen Dokumenten mit Bezug zu einem bestimmten Ort</strong> per E-Mail benachrichtigen lassen.<br>
			Klicke dazu auf den Ort, bestimme dann den relevanten Radius.<br>
			<br>
		</div>
		<div class="infos" style="display: none;">
			<strong>Ausgewählt:</strong> <span class="radius_m"></span> Meter um "<span class="zentrum_ort"></span>" (ungefähr)<br>
			<br>Willst du per E-Mail benachrichtigt werden, wenn neue Dokumente mit diesem Ortsbezug erscheinen?
		</div>
		<form method="POST" action="<?= CHtml::encode($this->createUrl("benachrichtigungen/index")) ?>">
			<input type="hidden" name="geo_lng" value="">
			<input type="hidden" name="geo_lat" value="">
			<input type="hidden" name="geo_radius" id="geo_radius" value="">
			<input type="hidden" name="krit_str" value="">

			<div>
				<button class="btn btn-primary ben_add_geo" disabled name="<?= AntiXSS::createToken("ben_add_geo") ?>" type="submit">Benachrichtigen!</button>
			</div>
		</form>
	</div>
</div>

<script>
	yepnope({
		load: ["/js/Leaflet/leaflet.js",
			"/js/leaflet.fullscreen/Control.FullScreen.js",
			<?=json_encode($assets_base)?> +"/ba_features.js",
			"/js/Leaflet.draw-0.2.3/dist/leaflet.draw.js",
			"/js/leaflet.spiderfy.js",
			"/js/leaflet.textmarkers.js"
		],
		complete: function () {
			var $map = $("#map").AntraegeKarte({
				benachrichtigungen_widget: "benachrichtigung_hinweis",
				show_BAs: true,
				benachrichtigungen_widget_zoom: 14,
				ba_link: "<?=CHtml::encode($this->createUrl("index/ba", array("ba_nr" => "12345")))?>",
				onSelect: function (latlng, rad, zoom) {
					if (zoom >= 14) {
						index_geo_dokumente_load("<?=CHtml::encode($this->createUrl("index/antraegeAjaxGeo"))?>?lng=" + latlng.lng + "&lat=" + latlng.lat + "&radius=" + rad + "&", latlng.lng, latlng.lat, rad);
					}
				}
			});
			$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, <?=json_encode($geodata_overflow)?>);
		}
	})
</script>


<div class="row <? if ($explizites_datum) echo "nur_dokumente"; ?>" id="listen_holder">
	<div class="col col-lg-5" id="stadtratsdokumente_holder">
		<?
		$this->renderPartial("index_antraege_liste", array(
			"antraege"          => $antraege_stadtrat,
			"datum"             => $datum,
			"neuere_url_ajax"   => $neuere_url_ajax,
			"neuere_url_std"    => $neuere_url_std,
			"aeltere_url_ajax"  => null,
			"aeltere_url_std"   => null,
			"weiter_links_oben" => $explizites_datum,
		));
		$this->renderPartial("index_antraege_liste", array(
			"title"             => "Sonstige neue Dokumente",
			"antraege"          => $antraege_sonstige,
			"datum"             => $datum,
			"neuere_url_ajax"   => $neuere_url_ajax,
			"neuere_url_std"    => $neuere_url_std,
			"aeltere_url_ajax"  => $aeltere_url_ajax,
			"aeltere_url_std"   => $aeltere_url_std,
			"weiter_links_oben" => false,
		));
		?>
	</div>
	<div class="col col-lg-4 keine_dokumente">

		<?
		if (count($termin_dokumente) > 0) {
			?>
			<h3>Neue Sitzungsdokumente</h3>
			<ul class="antragsliste"><?
				foreach ($termin_dokumente as $termin) {
					$ts = RISTools::date_iso2timestamp($termin->termin);
					echo "<li class='listitem'><div class='antraglink'>" . CHtml::encode(strftime("%e. %b., %H:%M", $ts) . ", " . $termin->gremium->name) . "</div>";
					foreach ($termin->antraegeDokumente as $dokument) {
						echo "<ul class='dokumente'><li>";
						echo "<div style='float: right;'>" . CHtml::encode(strftime("%e. %b.", RISTools::date_iso2timestamp($dokument->datum))) . "</div>";
						echo CHtml::link($dokument->name, $dokument->getOriginalLink());
						echo "</li></ul>";
					}
					echo "</li>";
				}
				?></ul>
		<? } ?>

		<h3>Kommende Termine</h3>
		<?
		$data = gruppiere_termine($termine_zukunft);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den nächsten $tage_zukunft Tagen</p>";
		else $this->renderPartial("termin_liste", array(
			"termine" => $data
		));
		?>

		<h3>Vergangene Termine</h3>
		<?
		$data = gruppiere_termine($termine_vergangenheit);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den letzten $tage_vergangenheit Tagen</p>";
		else $this->renderPartial("termin_liste", array(
			"termine" => $data
		)); ?>
	</div>
	<div class="col col-lg-3 keine_dokumente">
		<section style="display: inline-block">
			<h3>Benachrichtigungen</h3>

			<p>
				<a href="<?= CHtml::encode($this->createUrl("benachrichtigungen/index")) ?>" class="startseite_benachrichtigung_link email" title="E-Mail">@</a>
				<a href="<?= CHtml::encode($this->createUrl("index/feed")) ?>" class="startseite_benachrichtigung_link" title="RSS-Feed">R</a>
				<!--
				<a href="#" class="startseite_benachrichtigung_link" title="Twitter">T</a>
				<a href="#" class="startseite_benachrichtigung_link" title="Facebook">f</a>
				-->
			</p>
		</section>

		<section class="start_berichte">
			<a href="<?=CHtml::encode(Yii::app()->createUrl("index/highlights"))?>" class="weitere">Weitere</a>
			<h3>Berichte</h3>
			<ul><?
				foreach ($highlights as $dok) {
					echo "<li>";
					echo CHtml::link($dok->antrag->getName(true), $dok->getOriginalLink());
					echo "</li>";
				}
				?>
			</ul>
		</section>

		<section>
			<h3>StadträtInnen</h3>

			<ul class="fraktionen_liste"><?
				usort($fraktionen, function ($val1, $val2) {
					if (count($val1) < count($val2)) return 1;
					if (count($val1) > count($val2)) return -1;
					return 0;
				});
				foreach ($fraktionen as $fraktion) {
					/** @var StadtraetIn[] $fraktion */
					$fr = $fraktion[0]->stadtraetInnenFraktionen[0]->fraktion;
					echo "<li><a href='" . CHtml::encode($fr->getLink()) . "' class='name'>";
					echo "<span class='count'>" . count($fraktion) . "</span>";
					echo CHtml::encode($fr->name) . "</a><ul class='mitglieder'>";
					foreach ($fraktion as $str) {
						echo "<li>";
						if ($str->abgeordnetenwatch != "") echo "<a href='" . CHtml::encode($str->abgeordnetenwatch) . "' class='abgeordnetenwatch_link' title='Abgeordnetenwatch'></a>";
						if ($str->web != "") echo "<a href='" . CHtml::encode($str->web) . "' title='Homepage' class='web_link'></a>";
						if ($str->twitter != "") echo "<a href='https://twitter.com/" . CHtml::encode($str->twitter) . "' title='Twitter' class='twitter_link'>T</a>";
						if ($str->facebook != "") echo "<a href='https://www.facebook.com/" . CHtml::encode($str->facebook) . "' title='Facebook' class='fb_link'>f</a>";
						echo "<a href='" . CHtml::encode($str->getLink()) . "' class='ris_link'>" . CHtml::encode($str->name) . "</a>";
						echo "</li>\n";
					}
					echo "</ul></li>\n";

				}
				?></ul>

			<script>
				$(function () {
					var $frakts = $(".fraktionen_liste > li");
					$frakts.addClass("closed").find("> a").click(function (ev) {
						if (ev.which == 2 || ev.which == 3) return;
						ev.preventDefault();
						var $li = $(this).parents("li").first(),
							is_open = !$li.hasClass("closed");
						$frakts.addClass("closed");
						if (!is_open) $li.removeClass("closed");
					});
				})
			</script>
		</section>

		<section>
			<h3>Statistiken</h3>

			<div class="statistiken">
				<strong><?= number_format($statistiken["anzahl_dokumente"], 0, ",", ".") ?> durchsuchbare Dokumente</strong>,<br>
				insgesamt <strong><?= number_format($statistiken["anzahl_seiten"], 0, ",", ".") ?> Seiten</strong><br>
				<hr>
				<?= number_format($statistiken["anzahl_dokumente_1w"], 0, ",", ".") ?> neue Dokumente in den letzten 7 Tagen
				(<?= number_format($statistiken["anzahl_seiten_1w"], 0, ",", ".") ?> Seiten)
				<hr>
				Zuletzt aktualisiert: <?=
				RISTools::datumstring($statistiken["letzte_aktualisierung"]) . ", " . substr($statistiken["letzte_aktualisierung"], 11, 5);
				?>
			</div>
		</section>
	</div>
</div>
