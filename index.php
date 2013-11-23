<?php

//////////////////////////////////////////////////////////////////////////
/*

					MMS - CMS - Q3 Content Management System
					
TODO:
-Tablica
-galerie

*/
////////////////////////////////////////////////////////////////////////

//ZMIENNE GLOBALNE -->
	$SL = "/";
	//global settings - określa zmienne niezbędne do działania silnika strony
	//$GSK = array('loc_path','srv_name','dir_path','tpl_dir','res_dir','data_dir','upl_dir','file_ext','file_rss','mnu_pfx','admpswd');

	//page settings keys - określa ustawienia niezbędne do działania strony (meta, słowa kluczowe, itp)
	$PSK = array('admpswd'=>'Hasło administratora|Aktualne hasło administratora (pozostaw puste aby nie zmieniać)','keywords'=>'Słowa kluczowe|Słowa kluczowe strony przeznaczone dla wyszukiwarek internetowych.','description'=>'Opis strony|Opis strony widoczny w wyszukiwarkach internetowych.','google_key'=>'Klucz autentykacji google|&lt;meta name="google-site-verification" content="<u>fyntfyHMLNQpLjiM9VwdephOhUqyVA41ezT10ywAQjA</u>" /&gt;','tpl'=>'Szablon strony','title'=>'Tytuł strony|Domyślny tytuł widoczny w pasku tytułowym przeglądarki','absurl'=>'Adres internetowy strony|Adres pod którym strona jest widoczna w internecie','email'=>'Adres kontaktowy formularza|Adres e-mail na który wysyłane są wiadomości z formularza kontaktowego.');
	//page values keys - dane dotyczące treści zależne od wybranej strony (treść strony)
	$PVK = array('lnk'=>'tekst w adresie strony|Określa przyjazną nazwę wirtualnego pliku strony, np www.strona.pl/<b>o_nas</b>.html', 'typ'=>'Typ strony|Typ zawartości strony. Może to być tekst lub galeria','mnu'=>'Tekst w menu| Tekst wyświetlany w głównym menu strony','status'=>'Status strony|Pozwala ukryć stronę w menu, lub zablokować do niej dostęp','tpl'=>'Szablon strony|Unikalny szablon strony dostępny w ramach jednego szablonuwitryny','passwd'=>'Hasło do strony|Unikalne hasło dla wybranej podstrony','title'=>'Tytuł strony|Tytuł strony wyświetlany w oknie głównym nad treścią','content'=>'Treść strony|Główna treść wybranej podstrony');

	//Page user settings keys
	$PUSK = ""; //Pobrać dynamicznie ze strony
	//page user values keys
	$PUVK = ""; //Pobrać dynamicznie ze strony
// <-- ZMIENNE GLOBALNE


//ZMIENNE SYSTEMOWE -->
	$GS['mms_ver'] = "1.4";
	$GS['loc_path'] = dirname(__FILE__).$SL;;//okresla lokalna sciezka np.: D:\xammp\htdocs\mms/
	$GS['srv_name'] = $_SERVER['SERVER_NAME'];//okresla nazwe serwera np.: localhost
	$GS['dir_path'] = substr($_SERVER['SCRIPT_NAME'],0,-9);//okresla sciezke na serwerze np.: /mms/
	$GS['tpl_dir']='tpl'.$SL;
	$GS['res_dir']='res'.$SL;
	$GS['data_dir']='data'.$SL;
	$GS['upl_dir']=$GS['data_dir'].'images'.$SL;//katalog do uploadu plikow
	$GS['file_ext']='.html';//rozszerzenie podstron
	$GS['file_rss']='rss.xml';//plik rss
	$GS['mnu_pfx']='mnu_';//prefix dla elementów menu

// <-- ZMIENNE SYSTEMOWE
		
	//Zainstaluj system
	InstallVer();
	
// POCZĄTEK SKRYPTU -->

session_start();
ob_start();


//Dispatcher realizujący zapytanie do serwera
switch(basename($_SERVER['REQUEST_URI'])){
	case $GS['file_rss']:
		if($GS['file_rss']!=''){showrss();die;}//czy tworzony rss
		break;
	case 'robots.txt':
		srbts();die;
	case 'sitemap.xml':
		ssmap();die;
	case 'captcha.php':
		captcha();die;
}

// DEV -->
//Zabezpieczenie przed blokadą sewrwera dostępu do pliku index.php- do sprawdzenia czy to tylko webd
//if($_GET["p"]=="403.")$_GET["p"] = "index";
// <-- DEV




//POBRANIE DANYCH --> 

	//page settings - ustawienia strony
	include ($GS['data_dir'] . "ps.php");//page settings

	//page values (z uwzględnieniem wersji strony)
	include ($GS['data_dir'] . "pages".$_GET["v"].".php");
	
	//page user settings - stałe elementy strony
	include ($GS['data_dir'] . "pus.php");
	
	$ps_date = date ("H:i:s d.m.Y", filemtime($GS['data_dir'] . "ps.php"));
	$pages_date = date ("H:i:s d.m.Y", filemtime($GS['data_dir'] . "pages.php"));
	$pus_date = date ("H:i:s d.m.Y", filemtime($GS['data_dir'] . "pus.php"));
	
	
	//Pobranie treści szablonu
	if(!file_exists($GS['tpl_dir'] . $PS['tpl'] . '/template.tpl')) $PS['tpl']="default";
	$tplfile=file_get_contents($GS['tpl_dir'] . $PS['tpl'] . '/template.tpl');//pobiera plik szablonu

// <-- POBRANIE DANYCH

//Jeśli nie określono podstrony pobieramy index
$_GET["p"] = ($_GET["p"])?$_GET["p"]:$pages[0]['lnk'];
//($_SESSION['admin']!='ok')?"index":"dashboard";


// PRZETWORZENIE WCZYTANYCH DANYCH -->

	//Page user settings
	preg_match_all("/\{us_(\w+)\}/e",  strtolower($tplfile), $TPL_TMP, PREG_PATTERN_ORDER);
	$PUSK = array_unique($TPL_TMP[1]);

	//Page user values
	preg_match_all("/\{uv_(\w+)\}/e",  strtolower($tplfile), $TPL_TMP, PREG_PATTERN_ORDER);
	$PUVK = array_unique($TPL_TMP[1]);
	

	//Ustawienie zmiennych w sesji do wykorzystania w innych miejscach
	
	if(!$PS["absurl"]) $PS["absurl"] = 'http://'.$_SERVER['SERVER_NAME'];
	$_SESSION["absurl"]=$PS["absurl"];
	$_SESSION["upl_dir"]=$GS['upl_dir'];

// <-- PRZETWORZENIE WCZYTANYCH DANYCH

//SEKCJA ZAPISANIA ZMIENNYCH ADMINISTRACYJNYCH   --->  

	switch($_POST["action"]){

		case "login":{//Zalogowanie użytkownika
			
			if($_SESSION['admin']!='ok' && md5($_POST['pswd']) == $PS['admpswd'] && $_POST['pswd']){
				$_SESSION['admin']='ok';
				$_GET["p"]="dashboard";
			}else{
				$msg = "<span class='label label-error'>Dane nieprawidłowe</span>";
			}
		}break;
		
		case "sendmessage":{//Message from page
			
			if($_POST["captcha"]==$_SESSION["captchaCheck"]){
				//Wysłanie wiadomości z formularza
				$title = "Wiadomość z formularza kontaktowego strony ".$PS["title"];
				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/HTML; charset=utf-8";
				$headers[] = "From: ".$_POST["name"]." <".$_POST["email"].">";
				$headers[] = "Subject: ".$title;
				$headers[] = "X-Mailer: PHP/".phpversion();

				if(mail($PS["email"], "=?utf-8?B?".base64_encode($title)."?=", $_POST["message"], implode("\r\n", $headers))){
					$msg = "<span class='label label-success'>Wiadomość została wysłana</span>";
				}else{
					$msg = "<span class='label label-error'>Wiadomość nie została wysłana</span>";
				};
				//Zapisanie wiadomości do pliku tekstowego na wszelki wypadek		
				$message = "FROM ".$_POST["name"]." <".$_POST["email"]."> MESSAGE: ".$_POST["message"];
				$fp=fopen($GS['data_dir'] . 'mail.txt','a');
				fwrite($fp,$message."\r\n");
				fclose($fp);
				unset ($_POST);
				
			}else{
				$msg = "<span class='label label-error'>Podano nieprawidłowy kod z obrazka</span>";
			}
		}break;
		
		case "ps":{//Page Settings Keys
		
			foreach($PSK as $_PSK=>$_PSKV){	
		
				if($_PSK=="admpswd" ){
					if($_POST["admpswd"]!=""){
						$_POST["admpswd"] = md5($_POST["admpswd"]);
					}else{
						$_POST["admpswd"]= $PS["admpswd"];
					}
				}
				
				$PS_TMP[$_PSK] = str_replace(array("\r\n", "\r", "\n"), "", $_POST[$_PSK]);
			}
			
			save_phpfile("ps","PS",$PS_TMP);
			
			$PS = $PS_TMP;
			
			$msg = "<span class='label label-success'>Ustawienia zostały zapisane</span>";
			$_GET["a"]="ps";
			
		}break;
		
		case "pv":{//Page Values Keys

			foreach($PVK as $_PVK=>$_PVKV){	
				$PV_TMP[$_PVK] = stripslashes($_POST[$_PVK]);
			}
			
			save_phpfile("pv","PV",$PV_TMP);
			
			$PV = $PV_TMP;
			
			$msg = "<span class='label label-success'>Dane zostały zapisane</span>";
			$_GET["a"]="pv";
			
		}break;

		case "pus":{//Page User Settings Keys

			foreach($PUSK as $_PUSK){	
				$PUS_TMP[$_PUSK] = stripslashes($_POST[$_PUSK]);
			}
			
			save_phpfile("pus","PUS",$PUS_TMP);
			
			$PUS = $PUS_TMP;
			
			$msg = "<span class='label label-success'>Ustawienia użytkownika zostały zapisane</span>";
			$_GET["a"]="pus";
			
		}break;
		
		case "puv":{//Page User Values Keys
			
			foreach($PUVK as $_PUVK){	
				$PUV_TMP[$_PUVK] = stripslashes($_POST[$_PUVK]);
			}
			
			save_phpfile("puv","PUV",$PUV_TMP);
			
			$PUV = $PUV_TMP;
			
			$msg = "<span class='label label-success'>Dane użytkownika zostały zapisane</span>";
			$_GET["a"]="puv";
		}break;
		
		case "update":{//Aktualizacja silnika
			
			//uploaded file has higher priority 
			if ($_FILES["upd_file"] && $_FILES["upd_file"]["error"]==UPLOAD_ERR_OK){

				$tmp_name = $_FILES["upd_file"]["tmp_name"];
				//Kopia bezpieczeństwa
				if($_FILES["upd_file"]["name"]=='index.php' && @copy('index.php','index_'.time().'.upd')){
					$succ = @move_uploaded_file($tmp_name, "./index.php");
				}
			}else{
				//double check version, update if newer version
				$upd = GetLastVer();
				if(trim($upd["v"])>$GS['mms_ver']){
					$succ = UpdateVer();
				}
			}
			
			if($succ){
				$msg = "<span class='label label-success'>System został zaktualizowany.</span>";
			}else{
				$msg = "<span class='label label-error'>System nie został zaktualizowany.</span>";
			}
			
			//$_GET["a"]="ps";
			
		}break;
		
		case "pages":{ //Wszystkie akcje dotyczące stron

			//Zapisanie strony
			if($_POST["id"]>=0){
				
				switch($_POST["subaction"]){
				
					case "delete":{
						unset($pages[$_POST["id"]]);
						$_GET["p"]="index";
					}break;
					
					default:{
						
						//Page values
						foreach($PVK as $_PVK=>$_PVKV){	
						
							//zmiana wybranych wartości
							if ($_PVK=='lnk') $_POST[$_PVK] = createurl($_POST[$_PVK]);
							if ($_PVK=='content') $_POST[$_PVK] = $_POST['pgcontent'] ;//|| $_POST['glcontent'];
							
							$PV_TMP[$_PVK] = stripslashes($_POST[$_PVK]);
						}
						
						//Page user values
						foreach($PUVK as $_PUVK){	
							$PV_TMP[$_PUVK] = stripslashes($_POST[$_PUVK]);
						}		
						
						$pg_lnk_exists = 0;
						foreach ($pages as $pg){
							if ($_POST["lnk"]==$pg["lnk"] && !$_POST["id"] && $_POST["id"]!=0)$pg_lnk_exists = 1;
						} 
						//jeżeli strona o takim linku nie istnieje zapisz ją.
						if(!$pg_lnk_exists) $pages[$_POST["id"]] = $PV_TMP; 

						$_GET["p"] = $_POST["lnk"];
						

					}break;
				}

				//Przenumerowanie pozycji
				if($_POST["pos"]){
					unset($pdtmp);
					$tmppos = explode(",",$_POST["pos"]);

					foreach($tmppos as $tppos){
						$pdtmp[] = $pages[str_replace($GS['mnu_pfx'],"",$tppos)];
					}
					$pages = $pdtmp;
				
				}
				//Zapis stron
				if($pg_lnk_exists == 0){
					if (save_pages($pages)){
						$msg .= "<span class='label label-success'>Dane zostały zapisane</span>";
					}else{
						$msg .= "<span class='label label-error'>Dane nie zostały zapisane</span>";
					};
				}else{
					$msg = "<span class='label label-error'>Strona o podanym linku już istnieje</span>";
				}
			}
		}break;
		
	}
	
// <-- SEKCJA ZAPISANIA ZMIENNYCH ADMINISTRACYJNYCH_______________________________________________________________________________________________________



//Pobranie strony do wyświetlenia (zmienne w pliku muszą nazywać się $pages )

$disp_pg = gpl($_GET["p"],$pages);

//Pobranie pliku z właściwym szablonem dla strony
$disp_pg["tpl"] = (file_exists($GS['tpl_dir'] . $PS['tpl'] . '/'.$disp_pg["tpl"].'.tpl'))?$disp_pg["tpl"]:"template";
if(!file_exists($GS['tpl_dir'] . $PS['tpl'] . '/'.$disp_pg["tpl"].'.tpl'))$PS['tpl']="default";
$tplfile=file_get_contents($GS['tpl_dir'] . $PS['tpl'] . '/'.$disp_pg["tpl"].'.tpl');//pobiera plik szablonu



//Przygotowanie menu strony

foreach($pages as $pkey=>$page){
	if(($_SESSION['admin']!='ok' || isset($_GET['prev'])) && $page["status"]==0 )continue;
	
	$prev=(isset($_GET['prev']))?"?prev":"";

	$icon =($page["status"]==0)?"<img style='position:relative;top:3px;height:14px;' src='".$GS['res_dir']."invisible_light_icon.png' alt='niewidoczna' border='0'/>":(($page["status"]==2)?"<img style='position:relative;top:3px;height:14px;border:0px;' src='".$GS['res_dir']."lock.png' alt='niewidoczna' />":"");
	
	$class = ($page["lnk"]==$_GET["p"])?"active":"";
	$liclass = ($page["lnk"]==$_GET["p"])?"current":"";
	
	$TPL_V["ps_menu"] .= "<li id='".$GS['mnu_pfx'].$pkey."' class='".$liclass."'><a class='".$class."' title='".$page["title"]."' href='".$page["lnk"].".html".$prev."'><span> ".$icon." ".$page["mnu"]."</span></a></li>";
}
		
//ZMIENNE SZABLONU --> -------------------------------------------------------------

	//Page settings - ustawienia globalne strony - szablon, tytuł
	foreach ($PSK as $_PSK=>$_PSV) $TPL_V["ps_" . $_PSK] = $PS[$_PSK];

	//Page values - wartości strony - tytyłpodstrony, treść strony
	foreach ($PVK as $_PVK=>$_PVV) $TPL_V["pv_" . $_PVK] = $disp_pg[$_PVK];

	//Page user settings - dodatkowe ustawienia użytkownika - nagłówek strony, stopka strony
	foreach ($PUSK as $_PUSK) $TPL_V["us_" . $_PUSK] = $PUS[$_PUSK];

	//Page user values - dodatkowe wartości strony - prawy pasek, górny baner
	foreach ($PUVK as $_PUVK) $TPL_V["uv_" . $_PUVK] = $disp_pg[$_PUVK];

// <-- ZMIENNE SZABLONU -------------------------------------------------------------

//SEKCJA ADMINISTRACYJNA --> 
			
	if((isset($_GET['adm']) || $_SESSION['admin']=='ok') && !isset($_GET['prev'])){//proba wejscia do sekcji admin
		
		//Pobranie szablonu dla admina
		$tplfile = inc("adm_tpl");
		
		//Wylogowanie użytkownika
		if(isset($_GET['logout']) && $_SESSION['admin']=='ok'){//jesli wylogowanie
			unset($_SESSION['admin']);
			header('Location: http://'.$GS['srv_name'].$GS['dir_path']);
		}
		
		//z całej strony robimy formularz
		$tplfile=str_replace('<body>','<body><form method="POST" enctype="multipart/form-data" action="?adm" id="admform">',$tplfile);
		$tplfile=str_replace('</body>','</form></body>',$tplfile);
		
		//PASEK ADMINISTRATORA vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
			$admhtml = "";

			if($_SESSION['admin']!='ok'){
				
				$tplfile = inc("adm_login");

			}else{
				
				$admhtml .= "
				<a href='?adm&logout' class='fr'>Wyloguj</a>
				<a href='http://".$GS['srv_name'].$GS['dir_path']."?a=ps' class='fr'>Ustawienia</a>
				<a href='http://".$GS['srv_name'].$GS['dir_path']."?a=nowa' class='fr'>Dodaj stronę</a>
				
				<a href='#' class='submit'>Zapisz zmiany</a>
				<a href='http://".$GS['srv_name'].$GS['dir_path']."?a=pus' >Stałe elementy strony</a>
				<a href='http://".$GS['srv_name'].$GS['dir_path'].$disp_pg["lnk"].$GS['file_ext']."?prev' target='_blank' >Podgląd strony</a>
				
				".$msg."
				
				";
				
			}
			
			$TPL_V["admin_bar"] = $admhtml;

		//PASEK ADMINISTRATORA ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

		//podmiana strony na stronę z ustawieniami
		if($_SESSION['admin']=='ok'){
			
			if ($_GET["p"]!="dashboard")
				$_GET["a"]=($_GET["a"])?$_GET["a"]:"default1";
			
			switch($_GET["a"]){
			
				case "ps":{
				
					//Get remote version
					$upd = GetLastVer();
					if(trim($upd["v"])<=$GS['mms_ver']){
						$new_ver_html = "brak dostępnych aktualizacji";
					}else{
						$new_ver_html = "Dostępna wersja ".$upd["v"]." <input type='submit' value='Aktualizuj'><br/><span>".$upd["d"]."</span>" ;
					}
				
					$fields="<input type='hidden' name='action' value='ps'/>";
					
					foreach($PSK as $_PSK=>$_PSKV){	
					
						$_PSKV = explode("|",$_PSKV);
						
						$fields .="<div class='field'><label style=''>";
						
						switch($_PSK){
						
							case "admpswd":{
								$fields .= $_PSKV[0].":<br/><input type='text' name='".$_PSK."' value='' style=''/>";
							}break;
							case "tpl":{
								//Utworzenie select szablonów
								$templates = array_diff(scandir($GS['tpl_dir']), array('..', '.', 'default'));
								$tpl_sel =  "<select name='tpl' id='tpl' >";
								$tpl_sel .=  "<option value='default'>Domyślny</option>";
								foreach($templates as $tpl_dir){
									$sel =($tpl_dir == $PS[strtolower($_PSK)])?"selected='selected'":"";
									$tpl_sel .=  "<option value='".$tpl_dir."' ".$sel.">".$tpl_dir."</option>";
								}
								
								$tpl_sel .=  "</select>";
								
								$fields .= $_PSKV[0].":<br/>".$tpl_sel;

							}break;
							case "keywords":
							case "description":
							{
								$fields .= $_PSKV[0].":<br/><textarea name='".$_PSK."' style=''/>".$PS[strtolower($_PSK)]."</textarea>";
							
							}break;
							default:{
								$fields .= $_PSKV[0].":<br/><input type='text' name='".$_PSK."' value='".$PS[strtolower($_PSK)]."' style=''/>";
							}break;
						}

						$fields .="</label><span>".$_PSKV[1]."</span></div>";
						
						$tabs =  "
							<div id='tabContainer'>
								<ul class='tabs'>
									<li><a class='active' id='itab1' href='#tab1'>Ustawienia witryny</a></li>
									<li><a id='itab2' href='#tab2'>Zasoby</a></li>
									<!--<li><a id='itab3' href='#tab3'>Aktualizacja</a></li>-->
								</ul>
								<div class='tabDetails'>
									<div id='tab1' class='tabContents'>
										".$fields."
										<div >
											<input type='submit' value='Zapisz zmiany'> Ostatnia zmiana ustawień: ".$ps_date."
										</div>
									</div>
								
									<div id='tab2' class='tabContents'>
										<div class='field'><label style=''>Manager zasobów:</label><span>Zarządzaj obrazami znajdującymi się w galerii</span></div>	
										<iframe height='550' style='width:100%' frameborder='0' src='res/tinymce/plugins/filemanager/dialog.php?type=1&lang=pl&fldr='>
										</iframe>
										<div style='clear:both;'></div>
									</div>
									<div id='tab3' class='tabContents'>
										

											<h3>Zainstalowana wersja: " . $GS['mms_ver'] . "</h3>
											<input type='hidden' name='action4' value='update'>
											<div class='field'>" .$new_ver_html ."</div>

											<h3>Lokalnie</h3>
											<strong>Plik aktualizacji:</strong></br>
											<input type='file' name='upd_file' value=''> 
											
											<input type='submit' value='Aktualizuj'>

										
										<div style='clear:both;'></div>
									</div>
								</div>
							</div>";
						
					}

					$TPL_V["PV_TITLE"] = "Ustawienia witryny";
					$TPL_V["PV_CONTENT"] = $tabs ;
	
				}break;
				
				case "pus":{//page user settings

					$fields="<input type='hidden' name='action' value='pus'/>";
					foreach($PUSK as $_PUSK){	
						$fields .= "<div style='padding:5px;'>
						<h3>".strtoupper($_PUSK)."</h3>
						<textarea class='smalleditable' name='".$_PUSK."' id='".$_PUSK."' >".$PUS[$_PUSK]."</textarea>
						</div>";
					}
					$fields .="<input type='submit' value='Zapisz zmiany'>";
					
					$TPL_V["PV_TITLE"] = "Stałe elementy witryny";
					$TPL_V["PV_CONTENT"] = $fields ;
				
				}break;
				
				
				default:{//Tablica
					
					$disp_pg = array('lnk'=>generateRandomString(10), 'typ'=>'html', 'mnu'=>'nowa strona', 'tpl'=>'template', 'title'=>'Tytuł nowej strony. Kliknij tutaj i zmień', 'content'=>'Kliknij tutaj i wprowadź swoją zawartość', 'status'=>'0', );
					$disp_pg["id"]=count($pages);
										
					$stats = GetStats();
					
					//ostatni miesiąc
					$lm_stats_html ="<table width='100%' cellspacing='0'style='margin-top:10px;'><tr>";
					for($xd=1 ; $xd<=date("t",strtotime("last month"));$xd++){
						$lm_stats_html .= "<td class='small bar'><div style='height:" . max($stats["lm_hit_a"][$xd]/2,1) . "px;' title='" . max($stats["lm_hit_a"][$xd],1) . "'></div>" . $xd . "</td>";
					}
					$lm_stats_html .="</tr></table>";

					//Obecny miesiąc
					$cm_stats_html ="<table width='100%' cellspacing='0'style='margin-top:10px;'><tr>";
					for($xd=1 ; $xd<=date("t",time());$xd++){
						$cm_stats_html .= "<td class='small bar'>
						<div style='height:" . max($stats["cm_hit_a"][$xd]/2,1) . "px;' title='" . max($stats["cm_hit_a"][$xd],1) . "'></div>" .$xd . "</td>";
					}
					$cm_stats_html .="</tr></table>";
					
					$page_stats_html ="";
					foreach($stats["pg_stats"] as $pg_key => $pg_st){
						if(!$pg_key)$pg_key="Strona główna";
						$page_stats_html .= "<span>&bull; ". $pg_key . ": <strong>". $pg_st ."</strong></span><br/>";
					}
					$page_stats_html .="";	
					
						
					$TPL_V["pv_title"] = "Tablica";
					
					$TPL_V["pv_content"] = "
					<div class='container'>
						Dzisiejsza data: ".date("d.m.Y", time()).". Ostatnie logowanie: 00:00:0000
						<hr>
						<div class='one_half'>
						<h2>Popularne zadania</h2>
						<ul>
							<li><a href='#'><H3 style='margin-bottom:0px;'>Dodaj nową stronę</h3></a><span>Dodaj nową stronę do menu</span></li>
							<li><a href='#'><H3 style='margin-bottom:0px;'>Zmień ustawienia strony</h3></a><span>Zmień ustawienia dla całej witryny jak tytuł czy słowa kluczowe</span></li>
							<li><a href='#'><H3 style='margin-bottom:0px;'>Wyświetl stronę</h3></a><span>Podejrzyj w nowym oknie jak wygląda witryna</span></li>
							<li><a href='#'><H3 style='margin-bottom:0px;'>Wyloguj się</h3></a><span>Zamknij panel administracyjny i przejdź do witryny</span></li>
						</ul></div>
						<div class='one_half '><h2>Statystyki</h2>
						
							<ul>
								<li><H3 style='margin-bottom:0px;'>Liczba odwiedzin <span>wyświetlenia (unikalne)</span></h3><span>
									Od początku: <strong>".$stats["hit"]." (".$stats["uhit"].")</strong><br/>
									W tym miesiącu: <strong>".$stats["m_hit"]." (".$stats["um_hit"].")</strong><br/>
									W tym tygodniu: <strong>".$stats["w_hit"]." (".$stats["uw_hit"].")</strong><br/>
									dziś: <strong>".$stats["d_hit"]." (".$stats["ud_hit"].")</strong></span>
								</li>
								<li>
									<H3 style='margin-bottom:0px;'>Poprzedni miesiąc <span>wyświetlenia</span></h3>
									<span>".$lm_stats_html."</span>
									<H3 style='margin-bottom:0px;'>Bieżący miesiąc <span>wyświetlenia</span></h3>
									<span>".$cm_stats_html."</span>
								</li>
								
								<li><H3 style='margin-bottom:0px;'>Popularne strony <span>wyświetlenia</span></h3>
								<span>".$page_stats_html."</span></li>
								
							</ul>
						</div>
					</div>
					";

				}break;
				
				case "nowa":{ //Tworzenie nowej strony

					$disp_pg = array('lnk'=>generateRandomString(10), 'typ'=>'html', 'mnu'=>'nowa strona', 'tpl'=>'template', 'title'=>'Tytuł nowej strony. Kliknij tutaj i zmień', 'content'=>'Kliknij tutaj i wprowadź swoją zawartość', 'status'=>'0', );
					$disp_pg["id"]=count($pages)+1;
					
					$TPL_V["pv_title"] = "Tytuł nowej strony. Kliknij tutaj aby go zmienić";
					
					$TPL_V["pv_content"] = "Kliknij tutaj i wprowadź swoją zawartość.";
					
				}//break; nie zatrzymujemy się
				
				case "default1":{ // opcje dla wybranej strony
				
					//Lista szablonów dla strony
					$ptpls = glob($GS['tpl_dir'] .$PS["tpl"]."/*.tpl", GLOB_BRACE);
					$ptpl_sel =  "<select name='tpl' id='tpl' >";
					foreach($ptpls as $ptpl){
						$ptpl = substr(basename($ptpl),0,-4);
						$sel =($ptpl == $disp_pg["tpl"])?"selected='selected'":"";
						$ptpl_sel .=  "<option value='".$ptpl."' ".$sel.">".$ptpl."</option>";
					}
					$ptpl_sel .=  "</select>";
					
					//Lista dostępnych typów stron
					$ptypeslst = array("html"=>"Tekst","gal"=>"Galeria");
					$ptype_sel =  "<select name='typ' id='typ' >";
					foreach($ptypeslst as $pkey=>$ptype){
						$ptsel =($pkey == $disp_pg["typ"])?"selected='selected'":"";
						$ptype_sel .=  "<option value='".$pkey."' ".$ptsel.">".$ptype."</option>";
					}
					$ptype_sel .=  "</select>";
					
					if($disp_pg["typ"]=="gal"){
						
						$_SESSION["upl_dir"] = "data/galery/";
						
						if ($a==5 && file_exists("res/tinymce/plugins/filemanager/".$_SESSION["upl_dir"])){
							$galeries = glob("res/tinymce/plugins/filemanager/".$_SESSION["upl_dir"]."*",GLOB_ONLYDIR);
							
							$gal_sel =  "<select name='pgcontent' id='pgcontent' ><option value='' >Folder główny</option>";
							foreach($galeries as $gkey=>$gal_dir){
							
								$glsel =(basename ($gal_dir) == $disp_pg["content"])?"selected='selected'":"";
								$gal_sel .=  "<option value='".basename ($gal_dir)."' ".$glsel.">".basename ($gal_dir)."</option>";
							}
							$gal_sel .=  "</select>";
						}
						
						
					}
					
					
					$tabs =  "
							<div id='tabContainer'>
								<ul class='tabs'>
									<li><a class='active' id='itab1' href='#'>Podstawowe dane</a></li>
									<li><a id='itab2' href='#'>Treść strony</a></li>
									<li><a id='itab3' href='#'>Bloki stałe</a></li>
									<li><a id='itab4' href='#'>Poprzednie wersje</a></li>
								</ul>
								<div class='tabDetails'>
									<div id='tab1' class='tabContents'>
										
										<input type='hidden' name='action' value='pages'/><input type='hidden' name='id' value='".$disp_pg["id"]."'><input type='hidden' name='pos' id='pos' value='' />
										
										<div class='field'><label style=''>Tytuł: <br/><input type='text' name='title' id='title' value='".$disp_pg["title"]."' /></label><span>Tytuł strony wyświetlany w oknie głównym nad treścią</span></div>
										<div class='field'><label style=''>Menu: <br/><input type='text' name='mnu' id='mnu' value='".$disp_pg["mnu"]."' /></label><span>Tekst wyświetlany w głównym menu strony</span></div>
										<div class='field'><label style=''>Link: <br/><input type='text' name='lnk' id='lnk' value='".$disp_pg["lnk"]."' /></label><span>Określa przyjazną nazwę wirtualnego pliku strony, np www.strona.pl/<b>".$disp_pg["lnk"]."</b>.html</span></div>
										<div class='field'><label style=''>Typ: <br/>".$ptype_sel."</label><span>Typ strony określa zawartość w niej umieszczoną.</span></div>
										<div class='field'><label style=''>Szablon: <br/>".$ptpl_sel."</label><span>Unikalny szablon strony dostępny w ramach jednego szablonu witryny</span></div>	
										
										<div class='field'><label style=''>Status:<br/>
											<select name='status' id='status'>
											<option value='0' ".(($disp_pg["status"]==0)?"selected='selected'":"").">Robocza</option>
											<option value='1' ".(($disp_pg["status"]==1)?"selected='selected'":"").">Opublikowana</option>
											<option value='2' ".(($disp_pg["status"]==2)?"selected='selected'":"").">Zablokowana</option>
											</select></label><span>Pozwala ukryć stronę w menu, lub zablokować do niej dostęp</span> 
										</div>

										<div class='field'><label style=''>Hasło:<br/>
										<input type='text' name='passwd' id='passwd' value='".$disp_pg["passwd"]."' /></label><span>Unikalne hasło dla wybranej podstrony</span></div>

										<div class='field'><label style=''>Inne akcje:<br/>
											<select name='subaction' id='subaction'>
											<option value='0' >Nic nie rób</option>
											<option value='delete' >Usuń stronę</option>
											</select></label><span>Wybierz akcję, którą chcesz wykonać</span> 
										</div>

										<div >
											<input type='submit' value='Zapisz zmiany'>
										</div>
									</div>
								
									<div id='tab2' class='tabContents'>
										
										<div id='tab_html' class='subtab' style='display:".(($disp_pg["typ"]=="html")?"":"none")."'>
											<textarea class='editable' name='pgcontent' id='pgcontent'>".$disp_pg["content"]."</textarea>
										</div>
										
										<div id='tab_gal' class='subtab' style='display:".(($disp_pg["typ"]=="gal")?"":"none")."'>
											
											<!--<div class='field'><label style=''>Folder:<br/>".$gal_sel."</label><span>Folder z którego będą wyświetlane obrazy</span></div>-->
										
											<div class='field'><label style=''>Manager obrazów:</label><span>Zarządzaj obrazami znajdującymi się w galerii</span></div>	
										
											<iframe height='550' style='width:100%' frameborder='0' src='res/tinymce/plugins/filemanager/dialog.php?type=1&lang=pl&fldr='>
											</iframe>
										</div>
										
										<div style='padding:5px;'><input type='submit' value='Zapisz zmiany'></div>
										
										<div style='clear:both;'></div>
									</div>
									
									<div id='tab3' class='tabContents'>";
													
										
										$fields="";
										if($PUVK){
											foreach($PUVK as $_PUVK){

												$fields .= "<div style='padding:5px;'>
													<h3>".$_PUVK."</h3>
													<textarea class='smalleditable' name='".$_PUVK."' id='".$_PUVK."'>".$disp_pg[$_PUVK]."</textarea>
												</div>";
											}
										}
										
								
										$tabs .= "".$fields."<input type='submit' value='Zapisz zmiany'> <a href='http://".$GS['srv_name'].$GS['dir_path']."?prev&showblocks' target='_blank' >Wyświetl bloki na stronie</a>
									</div>
									<div id='tab4' class='tabContents'><ul>";
										
										$tabs .= "<li>Bieżąca wersja <a href='".$disp_pg["lnk"].$GS['file_ext']."'>Wczytaj</a> <a href='".$disp_pg["lnk"].$GS['file_ext']."?prev' target='_blank'>Podgląd</a> </li>";
												
										$versions = glob($GS['data_dir']."pages*.php", GLOB_BRACE);
										rsort($versions);
										foreach($versions as $version){
											
											$vdate = str_replace($GS['data_dir']."pages","",str_replace(".php","",$version));
											//jeśli data jest pusta robimy kolejne kółko
											if(!$vdate)continue;
											//wczytujemy plik z wersją
											unset($pages);
											include $version;
											foreach($pages as $pg){
												if($disp_pg['lnk'] == $pg["lnk"]){
													$tabs .= "<li>" . date("H:i:s d.m.Y",$vdate) . " <a href='".$pg["lnk"].$GS['file_ext']."?v=".$vdate."'>Wczytaj</a> <a href='".$pg["lnk"].$GS['file_ext']."?prev&v=".$vdate."' target='_blank'>Podgląd</a> </li>";
												}	
											}
											
											
											
										}
										
									
									$tabs .= "</ul><div><a class='btn' href=''>Pobierz kopię</a> <a class='btn' href=''>Usuń kopie zapasowe</a></div>";
										
								$tabs .= "</div>
								</div>
							</div>";
					
				
					$TPL_V["pv_content"] = $tabs;
					
				}break;
			
			}//switch($_GET["a"])
		}//if($_SESSION['admin']=='ok')
	}//if((isset($_GET['adm']) || $_SESSION['admin']=='ok') && !isset($_GET['prev']))
		
	//Strony dostępne tylko dla administratora
	if($disp_pg["status"]==2 ){//sprawdzamy uprawnienie
	
		if($_SESSION['admin']!='ok' || isset($_GET['prev'])){//jeśli to nie admin
			
			if((isset($_POST["hash"]) && $_POST["hash"]==$disp_pg["passwd"])  || (isset($_SESSION[$disp_pg["id"]."_hash"]) && $_SESSION[$disp_pg["id"]."_hash"]==$disp_pg["passwd"] ) ){
			
				$_SESSION[$disp_pg["id"]."_hash"] = $disp_pg["passwd"];
			}else{
				
				$TPL_V["PV_TITLE"] = "Brak uprawnień do wyświetlenia strony";
				
				$TPL_V["PV_CONTENT"] = "Podaj hasło aby wyświetlić stronę.
				<div style='padding:10px;border:1px solid silver;margin:10px 0px;'><form method='post' action='?prev'>
				
				<div style='padding:5px;'><label style='display:block; text-align:right;width:100px;float: left;margin:2px'>Hasło:</label> 
				<input type='password' name='hash' id='hash' value='' /></div>
				
				<div style='padding:5px;'>
				<input type='submit' value='Zaloguj'>
				</div>
				
				</form></div>";
			}
		}
	}
	
// <-- SEKCJA ADMINISTRACYJNA
	
	

	
	
// OSTATECZNE PRZETWARZANIE WSZYSTKICH DANYCH_________________________________________________________________________________________________
	
	//Utworzenie zmiennej z folderem szablonu
	$TPL_V["PS_TPL"] = $GS['tpl_dir'] . $PS['tpl'] ."/";
	
	//Pokazuje ukryte bloki w szablonie - pomocne dla administratora
	if(isset($_GET['showblocks'])){
		foreach($TPL_V as $_TPL_K=>$_TPL_V){
			if(substr($_TPL_K,0,2)=="pv" || substr($_TPL_K,0,2)=="uv" || substr($_TPL_K,0,2)=="us"  ){
				$TPL_V[$_TPL_K] = "<div style='border:1px solid red;margin:5px;padding:10px'><span style='color:white;display:block;background:red;'>".$_TPL_K."</span>".$_TPL_V."</div>";
			}
		}
	}
	
	//Przetwarzanie wyszukiwania na stronie
	if($_GET["s"]){
		$TPL_V["pv_title"] ="Wyniki wyszukiwania - [".$_GET["s"] ."]";

		$TPL_V["pv_content"] ="<ul>";
		foreach($pages as $sp){
			$pos = stripos ($sp["content"], $_GET["s"]);
			if ($pos !== false) { 
				$TPL_V["pv_content"] .= "<li><h4><a href='".$sp["lnk"].$GS['file_ext']."?h=".$_GET["s"].((isset($_GET["prev"]))?"&prev":"")."'>" . $sp["mnu"] . "</a></h4>" . substr(strip_tags($sp["content"]),0,50) . "...</li>";
			}
		}
		$TPL_V["pv_content"] .="</ul>";
	}
	
	//podwietlenie szukanego wyrazu
	if($_GET["h"]){
		$TPL_V["pv_content"] = preg_replace("/\p{L}*?".preg_quote($_GET["h"])."\p{L}*/ui", "<span class='highlight'>$0</span>", $TPL_V["pv_content"]);
	}
	
	
	
	
	//Wstawienie pliku formularza kontaktu do zmiennych strony
	$TPL_V["pv_contact"] = inc("contact");

	
	//DEV -->
	//Jak wersja strony jest inna niż aktualna to poinformuj o tym
	//if($_GET["v"]){
	//	$tplfile=str_replace('{PV_TITLE}', "{PV_TITLE} - ver (".$_GET["v"]."). <a href='".$disp_pg["lnk"].$GS['file_ext']."?prev'>Wyświetl aktualną wersję</a>",$tplfile);
	//}
	// <-- DEV
	
	//Podmiana zmiennych szablonowych w treści strony
	if($_SESSION['admin']!='ok' || isset($_GET["prev"])){
		$TPL_V["pv_content"] = parsetplvars($TPL_V["pv_content"], array_change_key_case($TPL_V, CASE_UPPER));
	}
	$TPL_V["pv_content"] = stripslashes($TPL_V["pv_content"]);
	
	
	if(($_SESSION['admin']!='ok' || isset($_GET["prev"])) && $disp_pg["typ"]=="gal"){
		
		$fancybox = inc("fancybox");
		
		$tplfile=str_replace('</head>', $fancybox . "</head>",$tplfile);
		
		$images = glob("res/tinymce/plugins/filemanager/data/galery/*.{gif,jpg}",GLOB_BRACE);
		$TPL_V["pv_content"] ="<div class='galery'><ul>";
		foreach( $images as $image ){ 
			$TPL_V["pv_content"] .="<li>
			<a class='fancybox' href='res/tinymce/plugins/filemanager/data/galery/".basename($image)."' title='' rel='group1'>
			<img src='res/tinymce/plugins/filemanager/data/thumbs/".basename($image)."' alt='' /></a></li>"; 
		} 
		$TPL_V["pv_content"] .="</ul></div>";
	
	}

	
	
	
	
	
/*
					
					if($disp_pg["typ"]=="gal"){
					
						$images = explode ("|",$disp_pg["content"]);
						
						foreach($images as $image){
							$disp_pg["content"] .= "<img class='thumb' src='".$image."' alt='".$image."'/>";
						
							$disp_pg["content"] .= "<a href='../articles/".$image['obraz'].".".$image['ext']."' title='". $image["title"] . "' rel='group1'>
								<img src='../articles/thumbs/".$image['obraz']."_thumb.".$image['ext']."' alt='' class='img'>
							</a>

							<span class='action' style=' '>
								<a href='?a=edit&amp;id=".$item['id']."&sa=delete&sid=".$image["id"]."' class='fr' title='Usuń'><img src='./images/cancel_16.png' alt='delete' class='help'></a>
							</span>";
						
						
						
						echo "";
						
						
						
						}
						
						$disp_pg["content"] .= "<span  class='thumb fl-space' style='width:100px;height:122px;$style'>
												<a href='' id='add_file' title='Dodaj nowy obraz'><img src='images/add.jpg' alt=''  style='width:100px;height:100px;'></a>
											</span>";
						
						
					
					}
					
					
					*/
					
	
	
	
	
	//Ostateczna podmiana zmiennych szablonu
	$tplfile=parsetplvars($tplfile, array_change_key_case($TPL_V, CASE_UPPER)); 
	
	//Wyświetlenie strony
	
	echo eval('?>'.$tplfile); 
	
	//drobne statystyki
	if($_SESSION['admin']!='ok')
		SaveStats();
	
	//Dalej nie ma sensu przetwarzać strony
	ob_end_flush();
	die;	
		
		

//SEKCJA FUNKCJI
//===========================================================================================================================================================
//===========================================================================================================================================================
//===========================================================================================================================================================
//===========================================================================================================================================================
function showrss(){//wyswietla kanal RSS

	//disabled - do not use
	global $GS;

	//page values
	include ($GS['data_dir'] . "pages.php");
	//page settings
	include ($GS['data_dir'] . "ps.php");
	
	$datemodif=date(DATE_RFC822,filemtime($GS['data_dir'] . "pages.php"));//ustala date ostatniej modyfikacji pliku data.php
	header('Content-Type: text/xml');
	echo"<?xml version=\"1.0\"  encoding=\"UTF-8\"?>\r\n<rss version=\"2.0\">\r\n  <channel>\r\n";
	echo"    <title>".$PS['title']."</title>\r\n";
	echo"    <link>http://".$GS['srv_name'].$GS['dir_path']."</link>\r\n";
	echo"    <description>".$PS['description']." - kanał RSS</description>\r\n";
	echo"    <lastBuildDate>$datemodif</lastBuildDate>\r\n";
	foreach($pages as $k => $p){
		echo"    <item>\r\n      <title>".$p['title']."</title>\r\n";
		echo"      <link>http://".$GS['srv_name'].$GS['dir_path'].$p['lnk'].$GS['file_ext']."</link>\r\n";
		echo"      <description>". preg_replace("/&#?[a-z0-9]+;/i","",$p['content'])  ."</description>\r\n";
		echo"    </item>\r\n";
	}
	echo"  </channel>\r\n</rss>";
}

function srbts(){//wyswietla robots.txt
	global $GS;
	header('Content-Type: text/plain');
	echo"Sitemap: http://".$GS['srv_name'].$GS['dir_path'] . "sitemap.xml
	User-agent: *
	Disallow: /data/
	Disallow: /res/
	Disallow: /tpl/";
}

function captcha(){//wyswietla captcha
	
	session_start();
	global $GS;
	
	$captcha = '';
	$supportedCharacter = array('1','2','3','4','5','6','7','8','9','0','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','!','$','%');

	for ($i = 1; $i <= 5; $i++) {
		$position = mt_rand(0,sizeof($supportedCharacter) - 1); 
		$captcha .= $supportedCharacter[$position];         
	}
	
	$image = imagecreate(50, 15);
	$bg = imagecolorallocate($image, 255, 255, 255);
	
	$textcolor = imagecolorallocate($image, 0, 0,0);
	imagestring($image, 9, 0, 0, $captcha, $textcolor);
	imagefilter($image,IMG_FILTER_SMOOTH,10);

	header('Content-Type: image/jpeg');
	imagejpeg($image);
	imagedestroy($image);   
	$_SESSION['captchaCheck'] = $captcha; 

}

function ssmap(){//wyswietla plik sitemap
	global $GS,$pages;
	
	include_once($locpath.$GS['data_dir'].'pages.php');
	$datemodif=date('Y-m-d',filemtime($locpath.$GS['data_dir'].'pages.php'));//ustala date ostatniej modyfikacji pliku pages.php
	header('Content-Type: text/xml');
	echo"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
	foreach($pages as $k1 => $p2){//petla po kolejnych artykulach
		echo"	<url>\r\n";
		echo"		<loc>http://".$GS['srv_name'].$GS['dir_path'].(($p2['lnk']!='index') ? $p2['lnk'].$GS['file_ext']: '')."</loc>\r\n";
		echo"		<lastmod>$datemodif</lastmod>\r\n";
		echo"		<changefreq>weekly</changefreq>\r\n";
		echo"		<priority>0.5</priority>\r\n";
		echo"	</url>\r\n";
	}

	echo'</urlset>';
}
function crht(){//tworzy .htaccess
	global $GS;

	$htdata="RewriteEngine On
	RewriteRule ^(.*).html$ index.php?p=$1&%{QUERY_STRING}
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule !\.(pdf|js|ico|gif|jpg|png|css|rar|zip|tar\.gz)$ index.php [L]";
	$fp=fopen($locpath.'.htaccess','w');
	fwrite($fp,$htdata);
	fclose($fp);
}

function fixslash($str){//jak stripslashes, ale bez zmiany '
	$str=str_replace('\\"','"',$str);
	$str=str_replace('\\\\','\\',$str);
	return $str;
}
function hex2asc($str){//zrodlo: http://www.php.net/hexdec#54002
    $p='';
    for($i=0;$i<strlen($str);$i=$i+2)
        $p.=chr(hexdec(substr($str, $i, 2)));
    return $p;
}
function createurl($title){//tworzy URLe bez smieci
	$url=str_replace(' ','-',$title);
	$utfchars=array(hex2asc("C484"),hex2asc("C485"),hex2asc("C486"),hex2asc("C487"),hex2asc("C498"),hex2asc("C499"),hex2asc("C581"),hex2asc("C582"),hex2asc("C583"),hex2asc("C584"),hex2asc("C393"),hex2asc("C3B3"),hex2asc("C59A"),hex2asc("C59B"),hex2asc("C5BB"),hex2asc("C5BC"),hex2asc("C5B9"),hex2asc("C5BA"));
	$normchars=array('a','a','c','c','e','e','l','l','n','n','o','o','s','s','z','z','z','z');
	$url=str_replace($utfchars,$normchars,$url);//usuwa ogonki
	$url=strtolower(preg_replace('|[^a-z0-9-_.;,\s]|i','',$url));//usuwa niestandardowe znaki i zmienia na male litery
	return str_replace(' ', "_", $url);  
} 

function parsetplvars($input,$values){//Zamienia wartości w szablonie
	return preg_replace("/\{(\w+)\}/e","\$values['\\1']",$input);
}

function save_pages($array){//Zapisuje strony do bazy
	global $GS;
	//print_r($array);
	if(!is_array($array))return false;
		
	$data = "<?\r\n";
	foreach($array as $key=>$item){
		
		$fields = "";
		foreach($item as $tkey=>$field){
			$fields .= "'". trim($tkey) . "'=>'".str_replace(array("\r", "\n"), "", $field)."', ";
		}
		
		$data .= "\$pages['".$key."'] = array(".$fields.");\r\n";
	}
	$data .= "?>";
	
	//kopia bezpieczeństwa
	@copy ($locpath.$GS['data_dir'].'pages.php',$locpath.$GS['data_dir'].'pages'.time().'.php');
	
	$fp=fopen($locpath.$GS['data_dir'].'pages.php','w');
	fwrite($fp,$data);
	fclose($fp);
	return true;
}


function save_settings($array){//Zapisuje ustawienia
	global $GS;

	$data = "<?\r\n";
	foreach($array as $key=>$item){
		$data .= "\$values['".strtoupper($key)."'] = '".$item."';\r\n";
	}
	$data .= "?>";

	$fp=fopen($locpath.$GS['data_dir'].'settings.php','w');
	fwrite($fp,$data);
	fclose($fp);
}

function save_phpfile($fn,$key,$a){
	global $GS;

	$data = "<?\r\n";
	foreach($a as $k=>$i){
		$data .= "\$".$key."['".$k."'] = '".$i."';\r\n";
	}
	$data .= "?>";

	$fp=fopen($locpath.$GS['data_dir'].$fn.'.php','w');
	fwrite($fp,$data);
	fclose($fp);

}

function gpl($link,$array){//Pobiera stronę po linku

	foreach($array as $k=>$item){
		if($item["lnk"]==$link){
			$item["id"] = $k;
			return $item;
		}
	}
	return array('lnk'=>'404', 'typ'=>'html', 
	'mnu'=>'404', 'title'=>'<p style="font-size:40pt">404</p>Brak strony do wyświetlenia', 
	'content'=>'Wybrana strona nie istnieje. Przepraszamy', 'status'=>'0', );
}


function GetStats(){
	global $GS;
	
	$stats = file($GS['data_dir'].'stats.log');
	
	$ret_stat["hit"] = count($stats);
	
	foreach ($stats as $stat){
	
		$tmp_arr = explode("|",$stat);
		$time = strtotime(substr($tmp_arr[0],1));

		//Rok
		$ytm = date("Y",$time);
		//Miesiąc
		$mtm = date("m",$time);
		//Tydzień
		$wtm = date("w",$time);
		//Dzień
		$dtm = date("d",$time);
		
		//Zliczanie tylko unikalnych
		if($tmp_arr[9]!=1){
			
			//Całość
			$uhit++;
			
			//Wg stron
			if(strpos($tmp_arr[8],".")>0){
				$upg_stats[substr($tmp_arr[8],0,strpos($tmp_arr[8],"?"))]++;
			}
			
			//Roczne odwiedziny
			$uy_hit_arr[$ytm]++;
			
			//w tym roku
			if($ytm == date("Y",time()))
				$uy_hit++;
			
			//miesięczne odwiedziny
			$um_hit_arr[$mtm]++;
			
			//w tym miesiącu
			if($mtm == date("m",time()))
				$um_hit++;
			
			//poprzedni miesiac po dniach
			if($mtm == (date("m",time())-1))
				$ulm_hit_arr[(int)date("d",$time)]++;
			
			//ten miesiac po dniach
			if($mtm == date("m",time()))
				$ucm_hit_arr[(int)date("d",$time)]++;

			//w tym tygodniu
			if($wtm == date("w",time()))
				$uw_hit++;	
			
			//dzisiaj
			if($dtm == date("d",time()))
				$ud_hit++;

		}
		
		//Całość
		$hit++;
		
		//Wg stron
		if(strpos($tmp_arr[8],".")>0){
			$pg_stats[substr($tmp_arr[8],0,strpos($tmp_arr[8],"?"))]++;
		}
		
		//Roczne odwiedziny
		$y_hit_arr[$ytm]++;
		
		//w tym roku
		if($ytm == date("Y",time()))
			$y_hit++;
		
		//Miesiąc
		$tm = date("m",$time);
		
		//miesięczne odwiedziny
		$m_hit_arr[$mtm]++;
		
		//w tym miesiącu
		if($mtm == date("m",time()))
			$m_hit++;
		
		//poprzedni miesiac po dniach
		if($mtm == (date("m",time())-1))
			$lm_hit_arr[(int)date("d",$time)]++;
		
		//ten miesiac po dniach
		if($mtm == date("m",time()))
			$cm_hit_arr[(int)date("d",$time)]++;

		//w tym tygodniu
		if($wtm == date("w",time()))
			$w_hit++;	
		
		//dzisiaj
		if($dtm == date("d",time()))
			$d_hit++;			
		
	}

	$ret_stat["pg_stats"] = $pg_stats;
	$ret_stat["hit"] = $hit;
	$ret_stat["y_hit"] = $y_hit;
	$ret_stat["y_hit_a"] = $y_hit_arr;
	$ret_stat["lm_hit_a"] = $lm_hit_arr;
	$ret_stat["cm_hit_a"] = $cm_hit_arr;
	$ret_stat["m_hit_a"] = $m_hit_arr;
	$ret_stat["m_hit"] = $m_hit;
	$ret_stat["w_hit"] = $w_hit;
	$ret_stat["d_hit"] = $d_hit;
	
	$ret_stat["upg_stats"] = $upg_stats;
	$ret_stat["uhit"] = $uhit;
	$ret_stat["uy_hit"] = $uy_hit;
	$ret_stat["uy_hit_a"] = $uy_hit_arr;
	$ret_stat["ulm_hit_a"] = $ulm_hit_arr;
	$ret_stat["ucm_hit_a"] = $ucm_hit_arr;
	$ret_stat["um_hit_a"] = $um_hit_arr;
	$ret_stat["um_hit"] = $um_hit;
	$ret_stat["uw_hit"] = $uw_hit;
	$ret_stat["ud_hit"] = $ud_hit;
	
	return $ret_stat;
	
	
	/*
		[0] => {13-10-09 08:36:32
		[1] => localhost
		[2] => Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.69 Safari/537.36
		[9] = unique
	*/
	
}


function SaveStats(){
	global $GS;

	$stats = array();

	$stats["DATE"] = date("y-m-d H:i:s", time());
	$stats["HTTP_HOST"] = $_SERVER['HTTP_HOST'];
	$stats["HTTP_USER_AGENT"] = $_SERVER['HTTP_USER_AGENT'];
	$stats["HTTP_REFERER"] = $_SERVER['HTTP_REFERER'];
	//$stats["SERVER_NAME"] = $_SERVER['SERVER_NAME'];
	//$stats["SERVER_ADDR"] = $_SERVER['SERVER_ADDR'];
	//$stats["SERVER_PORT"] = $_SERVER['SERVER_PORT'];
	$stats["REMOTE_ADDR"] = $_SERVER['REMOTE_ADDR'];
	$stats["REMOTE_HOST"] = $_SERVER['REMOTE_ADDR'];
	$stats["REDIRECT_URL"] = $_SERVER['REDIRECT_URL'];
	//$stats["QUERY_STRING"] = $_SERVER['QUERY_STRING'];
	$stats["REQUEST_URI"] = $_SERVER['REQUEST_URI'];
	//$stats["PHP_SELF"] = $_SERVER['PHP_SELF'];
	$stats["CURRENT_PAGE"] = (string) substr( $_SERVER["REQUEST_URI"], strrpos( $_SERVER["REQUEST_URI"], '/' )+1 );
	$stats["UNIQUE"] = $_COOKIE["stats"];

	$browser = $_SERVER['HTTP_USER_AGENT'];
	if (strstr(strtoupper($browser),"MSIE") || strstr(strtoupper($browser),"FIREFOX") || strstr(strtoupper($browser),"SAFARI") || strstr(strtoupper($browser),"OPERA") || strstr(strtoupper($browser),"CHROME") || strstr(strtoupper($browser),"NETSCAPE") || strstr(strtoupper($browser),"CAMINO") || strstr(strtoupper($browser),"SEAMONKEY") || strstr(strtoupper($browser),"ICAB") || strstr(strtoupper($browser),"K-MELEON") || strstr(strtoupper($browser),"AMAYA") || strstr(strtoupper($browser),"FLOCK") || strstr(strtoupper($browser),"GALEON") || strstr(strtoupper($browser),"MAXTHON") || strstr(strtoupper($browser),"DILLO") || strstr(strtoupper($browser),"SLIM") || strstr(strtoupper($browser),"KIDROCKET") || strstr(strtoupper($browser),"PHASEOUT") || strstr(strtoupper($browser),"OMNIWEB") || strstr(strtoupper($browser),"ICEWEASEL")) {
		$ROBOT = false;
	} else {
		$ROBOT = true;
	}
	
	//Wziąć słowo kluczowe po którym wyszukano w google	
	if (@$_SERVER['HTTP_REFERER']) {
		$referringPage = parse_url($_SERVER['HTTP_REFERER']);
		
		if (stristr($referringPage['host'], 'google.') || stristr($referringPage['host'], 'bing.') || stristr($referringPage['host'], 'yahoo.')) {
			
			parse_str( $referringPage['query'], $queryVars );
			
			if (stristr($referringPage['host'], 'google.')	|| stristr($referringPage['host'], 'bing.')) { $search = $queryVars['q']; }
			else if (stristr($referringPage['host'], 'yahoo.')) { $search = $queryVars['p']; }
			else { $search = false; }
			
			if ($search) { $search = str_replace("+"," ",$search); }
			
		}else { 
			$search = false; 
		}
	}else { 
		$search = false; 
	}
	
	if ($search)$stats["GOOGLE_KEYWORD"] = $search;
	
	setcookie("stats",1);
	
	$data = "{";
	foreach($stats as $stat){
		$data .= $stat."|";
	}
	$data .= "}\r\n";
	
	
	//$stats["browser_info"] = php_browser_info();
	
	/*
	
	'".$stats["ip"]."', 
	'".$stats["ip_host"]."',  
	'".php_uname('n')."',  
	'".$stats["user_agent"]."', 
	'".$stats["browser_info"]["platform"]."', 
	'".$stats["browser_info"]["browser"]."',   	
	'".$stats["date"]."',  
	'".$stats["page"]."',  
	'".$stats["browser_info"]["device_name"]."',  
	'".$stats["browser_info"]["renderingengine_name"]."'
	*/
	
	
	
	
	
	
	
	
	$fp=fopen($locpath.$GS['data_dir'].'stats.log','a');
	fwrite($fp,$data);
	fclose($fp);

}


function generateRandomString($length = 10) {
    //return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
}

function GetLastVer(){

	//Pobierz zdalny plik
	$ver_file = @fopen ("http://qczy.pl/update/mms.ver", "r");
	if (!$ver_file) {
		$new_ver_html = "<p>Nie można otworzyć zdalnego pliku.\n";	
	}else{
		while (!feof ($ver_file)) {
			$NW[] = fgets ($ver_file, 1024);	
		}
	}
	@fclose($ver_file);

	//Pobierz dostępną wersję
	$retval["v"] = $NW[0];

	//Pobierz opis nowej wersji (zmiany itp)
	for($i=1;$i<=100;$i++) $retval["d"] .=$NW[i];
	
	return $retval;
	
}

function UpdateVer(){

	global $GS;
	
	//Pobierz zdalny plik
	$new_ver_file = fopen ("http://qczy.pl/update/res.zip", "r");
	if ($new_ver_file) {
		fclose($new_ver_file);

		if(!class_exists('ZipArchive')) die("Class ZipArchive is missing");
		$zip = new ZipArchive;
		$zip->open("http://qczy.pl/update/res.zip");
		$zip->extractTo('./');
		$zip->close();
		
		return 1;
	}else{
		return 0;
	}
}

function InstallVer(){
	global $GS;
	
	//foldery
	if (!file_exists($GS['loc_path'].$GS['tpl_dir'])) mkdir($GS['loc_path'].$GS['tpl_dir'], 0777);
	if (!file_exists($GS['loc_path'].$GS['res_dir'])) mkdir($GS['loc_path'].$GS['res_dir'], 0777);
	if (!file_exists($GS['loc_path'].$GS['data_dir'])) mkdir($GS['loc_path'].$GS['data_dir'], 0777);
	if (!file_exists($GS['loc_path'].$GS['upl_dir'])) mkdir($GS['loc_path'].$GS['upl_dir'], 0777);

	//htaccess
	if(!file_exists($GS['loc_path'].'.htaccess')||filesize($GS['loc_path'].'.htaccess')==0)
		crht();

	//Rozpakowanie plików
	if (file_exists('res.zip')){
		if(!class_exists('ZipArchive')) die("Class ZipArchive is missing");
		$zip = new ZipArchive;
		$zip->open('res.zip');
		$zip->extractTo('./');
		$zip->close();
		rename ("res.zip", "res.bak");
	}else{
		//Wyświetlić komunikat, że może brakować plików
	}
	
	//Tworzenie bazy stron
	if (!file_exists($GS['loc_path'].$GS['data_dir']."pages.php")){
		$disp_pg[0] = array('lnk'=>'index', 'typ'=>'html', 'mnu'=>'Strona główna', 'title'=>'Pierwsza strona', 'content'=>'Treść pierwszej strony. Dziękujemy za zainstalowanie itp...', 'status'=>'1', );
		save_pages($disp_pg);
	}
	
	//Tworzenie pliku ustawień
	if (!file_exists($GS['loc_path'].$GS['data_dir']."ps.php")){
		save_phpfile("ps","PS",array( 'admpswd' => '25d55ad283aa400af464c76d713c07ad', 'keywords' => 'page keywords', 'description' => 'page description', 'google_key' => '', 'tpl' => 'default', 'title' => 'PAGE TITLE', 'absurl' => '',	'email' => 'admin@page.com'));
	}
	
	//Tworzenie pliku ustawień użytkownika
	if (!file_exists($GS['loc_path'].$GS['data_dir']."pus.php")){
		save_phpfile("pus","PUS",array("title"=>"Nowa strona", "slogan"=>"moje motto", "footer"=>"Kolejna strona utworzona dzięki MMS"));
	}
	
	//Tworzenie domyślnego szablonu
	if(!file_exists($GS['tpl_dir'] . 'default/template.tpl')){
		$def_tpl = inc("def_tpl");
		@mkdir($GS['tpl_dir'] . 'default');
		$fp=fopen($GS['tpl_dir'] . 'default/template.tpl','w');
		fwrite($fp,$def_tpl);
		fclose($fp);
	}
	
	return;
}

//Sztuczne includowanie wirtualnych plików
function inc($file){
	global $GS;
	global $msg;
	switch($file){
		
		case "fancybox":{
			
			return  "
			<!-- Add jQuery library -->
			<script type='text/javascript' src='".$GS['res_dir']."fancybox2/lib/jquery-1.10.2.min.js'></script>

			<!-- Add mousewheel plugin (this is optional) -->
			<script type='text/javascript' src='".$GS['res_dir']."fancybox2/lib/jquery.mousewheel-3.0.6.pack.js'></script>

			<!-- Add fancyBox main JS and CSS files -->
			<script type='text/javascript' src='".$GS['res_dir']."fancybox2/source/jquery.fancybox.js?v=2.1.5'></script>
			<link rel='stylesheet' type='text/css' href='".$GS['res_dir']."fancybox2/source/jquery.fancybox.css?v=2.1.5' media='screen' />

			<!-- Add Button helper (this is optional) -->
			<link rel='stylesheet' type='text/css' href='".$GS['res_dir']."fancybox2/source/helpers/jquery.fancybox-buttons.css?v=1.0.5' />
			<script type='text/javascript' src".$GS['res_dir']."fancybox2source/helpers/jquery.fancybox-buttons.js?v=1.0.5'></script>
			<script type='text/javascript'>
				$(document).ready(function() {
					$('.fancybox').fancybox();
				});
			</script>";
		};
			
		case "adm_login":{
			
			return "<!DOCTYPE html>
					<head>
						<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
						<meta name='keywords' content='{_PSKWORDS}'>
						<meta name='description' content='{PS_DESCRIPTION}'>
						<style>
							body{font: 10pt helvetica, arial, sans-serif; }
							input[type=text],input[type=password],select,textarea {padding:3px;width:50%;margin:0px}
							input[type=submit] {padding:5px 15px;cursor:pointer;}
							a {text-decoration:none;color:black}
							#container { width: 265px; margin: 10px auto; background-color: #fff; color: #333; border: 1px solid gray; line-height: 130%; box-shadow: 0px 0px 10px rgba(0,0,0,0.3);}  
							#content { padding: 1em;background-color:#f6f6f6 }  
							#top { padding: .5em; background-color: #ddd; border-bottom: 1px solid gray; }  
							#top h1 { padding: 0; margin: 0; }  
							.label-error {color:red;}
							.fr{float:right}
							#footer { clear: both; margin: 0; padding: .5em; color: #333; background-color:#ddd; border-top: 1px solid gray; }  
						</style>
					</head>
					<body>
						<div id='container'>
							<div id='top'>
								MMS Login
							</div>
							<div id='content'>
								".$msg."
								<form method='POST' enctype='multipart/form-data' action='?adm' id='admform'>
									<input type='hidden' name='action' value='login'/>
									<input type='password' name='pswd' style='width:155px;'>
									<input type='submit' value='Login'>
								</form>
							</div>
							<div id='footer'>
								<a href='http://mms.qczy.pl' target='_blank'>Q3 MMS</a>  <span class='fr' >".$GS['mms_ver'] ."</span>
							</div>
						</div>
					</body>
				</html>";
		
		}break;
		
		case "adm_tpl":{
		
			return "<!DOCTYPE html>
					<head>
						<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
						<meta name='keywords' content='{_PSKWORDS}'>
						<meta name='description' content='{PS_DESCRIPTION}'>
						<script src='".$GS['res_dir']."jquery-1.10.2.min.js'></script>
						<script src='".$GS['res_dir']."jquery-ui.js'></script>
						<script type='text/javascript' src='".$GS['res_dir'] ."tinymce/tinymce.min.js'></script>
						<script type='text/javascript'>
							//forced_root_block : '',	
							//content_css:'{PS_TPL}style.css',			
							
									//convert_urls: false,
									//remove_script_host : false,
									//document_base_url: '{PS_ABSURL}',
									//convert_urls: false,
									//remove_script_host : false,
									//content_css:'{PS_TPL}style.css',
									//23.11 - usunięte fullpage
									
							if (typeof(tinymce) != 'undefined') {
								tinymce.init({
									selector: 'textarea.editable',
									inline: false,
									menubar: false,
									height:'400px',
									language : 'pl',
									toolbar_items_size: 'small',
									save_enablewhendirty: false,
									plugins: [
										'save advlist autolink lists link image charmap print preview anchor',
										'searchreplace visualblocks code fullscreen',
										'emoticons insertdatetime media table contextmenu paste textcolor filemanager media'
									],
									toolbar1: 'save | cut copy paste pastetext pasteword | undo redo | removeformat bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
									toolbar2: 'table | styleselect fontsizeselect | code anchor link unlink image | forecolor backcolor emoticons',
									image_advtab: true,
									external_filemanager_path:'res/tinymce/plugins/filemanager/',
									filemanager_title:'MMS - Filemanager'
								});

								tinymce.init({
									selector: 'textarea.smalleditable',
									save_enablewhendirty: false,
									language : 'pl',
									forced_root_block : '',
									plugins: [
										'save advlist autolink lists link image charmap print preview anchor',
										'searchreplace visualblocks code fullscreen',
										'insertdatetime media table contextmenu paste textcolor filemanager'
									],
									toolbar1: 'save | undo redo | removeformat bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | styleselect fontsizeselect forecolor backcolor | code anchor link unlink image ',
									menubar: false,
									external_filemanager_path:'res/tinymce/plugins/filemanager/',
									filemanager_title:'MMS - Filemanager',
									toolbar_items_size: 'small',
									height: '200px'
								});
												
							}
								
							if (typeof(jQuery) != 'undefined') {							
								$(function() {
									//Sortowanie elementów menu
									$( '.sortable' ).sortable({stop: function( event, ui ) {var order = $('.sortable').sortable('toArray');$('#pos').val(order.join(','));}});$( '.sortable' ).disableSelection();
								
								
									$('.tabContents').hide();
									$('.tabContents:first').show();
									
									//$('.tabContents').tabs('option', 'selected', 2);
									//hash = window.location.hash;
									//alert (hash);
									
									$('#tabContainer ul li a').click(function(){
										var activeTab = $(this).attr('id').substr(1,10);
										$('.tabContents').hide(); 
										$('#'+activeTab).fadeIn(); 
										$('#tabContainer ul li a').removeClass('active');
										$(this).addClass('active'); 
										return false;	
									});
									
   

									$('#typ').change(function() {
										
										$('.subtab').hide(); 
										
										tp = $(this).val();
										
										$('#tab_'+tp).show();
									});
									
									$('a.submit').click(function(){
										var form = $(this).parents('form:first');
										form.submit();
									});
									
									$('#plik_new').change(function() {
										$('#new_item').submit();
										return false;
									});
																	
									$('#add_file').click(function() {
										$('#plik_new').click();
										return false;
									});
								});
							}
							setTimeout(function(){ $('.label').fadeOut('slow');}, 2000);

						</script>
						<link rel='stylesheet' href='".$GS['res_dir'] ."jquery-ui.css'><style>
							body{font: 10pt helvetica, arial, sans-serif; }
							a {text-decoration:none;color:black}
							h3 span{font-weight:normal;font-size:8pt;}
							#tabContainer ul.tabs { text-align: left; margin:0; border-bottom: 1px solid silver; list-style-type: none;padding: 6px 10px 6px 10px; }
							#tabContainer ul.tabs li { display: inline;}
							#tabContainer ul.tabs li { border-bottom: 1px solid #fff;background-color: #fff; }
							#tabContainer ul.tabs li a { padding: 6px 30px; border: 1px solid silver; background-color: #ddd; margin-right: 0px; text-decoration: none;border-bottom: none;}
							#tabContainer ul.tabs li a.active { background-color: #f6f6f6;color: #000; position: relative;top: 1px; }
							#tabContainer ul.tabs a:hover { background: #fff; }
							.tabDetails{padding:10px;border-style:solid;border-color:silver;border-width:0px 1px 1px 1px;}
							.field label {display:block;font-weight:bold;}
							.field{padding:5px;}
							.field span {font-size:8pt;}
							.label-success {color:green;}
							.label-error {color:red;}
							.small {font-size:6pt;}
							.bar {width:15px;border-right:1px solid #f6f6f6;padding:0px;vertical-align:bottom;text-align:center;color:gray;}
							.bar div{background:silver; border-right:white;}
							
							input[type=text],input[type=password], input[type=file],select,textarea {padding:3px;width:50%;margin:0px;border:1px solid silver;}
							input[type=submit] {padding:5px 15px;cursor:pointer;}
							input[type=file] {background:white;}
							#top { padding: .5em; background-color: #ddd; border-bottom: 1px solid gray; }  
							#top h1 { padding: 0; margin: 0; }  
							#top a, a.btn {border:1px solid #ACACAC;padding:5px 15px;margin:3px;background:#EEEEEE;display:inline-block;}
							#top a:hover, a.btn:hover {border:1px solid #7EB4EA;padding:5px 15px;background:#EAF3FC}
							#top a:active, a.btn:active {border:1px solid #569DE5;padding:5px 15px;background:#DAECFC}
							.fr{float:right}
							.fl{float:left}
							#container { width: 90%; margin: 10px auto; background-color: #fff; color: #333; border: 1px solid gray; line-height: 130%; box-shadow: 0px 0px 10px rgba(0,0,0,0.3);}  
							
							#leftnav { float: left; margin: 0; padding: 2px;width:197px; } 
							#content { margin-left: 200px; border-left: 1px solid gray; padding: 1em;background-color:#f6f6f6 }  
							#footer { clear: both; margin: 0; padding: .5em; color: #333; background-color: #ddd; border-top: 1px solid gray; }  
							.rmenu{float: right;padding: 8px;}

							#content h2 { margin: 0 0 .5em 0; }
							#leftnav ul { margin: 0; padding: 0; } 
							#leftnav ul li { list-style-type: none; display: block; } 
							#leftnav li a { display: block; padding: 5px 10px; text-decoration: none; border-right: 1px solid #fff; } 
							#leftnav li a:hover { background: #ddd; } 
							#leftnav li a.active { background: #EEEEEE; }
							
							.container{display:block;overflow:hidden;}
							.one_half {width:50%;float:left;}
							.last {clear:left;}
							
							@media only screen and (max-width:1000px){
								#tabContainer ul.tabs li a{padding: 6px 10px;}
								.one_half {width:100%;float:none;}
								.bar {width:35px;}
							}
							@media only screen and (max-width:800px){
								#tabContainer ul.tabs li a{display:block;}
								input[type=text],input[type=password],select,textarea {width:99%;}
								#tabContainer ul.tabs{padding: 0px;}
							}
							@media only screen and (max-width:600px){
								#leftnav{width:99%;float:none;}
								#content{margin-left: 0px;border-left: 0px;border-top: 1px solid gray;}
								.rmenu{float: none;}
								#top a {display:block;}
								.fr{float:none}
								
							}
						</style>
						<title>{PS_TITLE}</title>
					</head>
					<body>
						
						<div id='container'>
						<div id='top'>
							{ADMIN_BAR}
						</div>
						<div id='leftnav'>
						<ul>
							<li><a class='home' href='dashboard.html'>Tablica</a>
						</ul>
						<ul class='sortable'>
							{PS_MENU}
						</ul>
						</div>
						<div id='content'>
						<h2>{PV_TITLE}</h2>
						{PV_CONTENT}
						</div>
						<div id='footer'>
								<a href='http://mms.qczy.pl' target='_blank'>Q3 MMS</a> - Micro Management System by <a href='mailto:qczy@o2.pl'>Q3</a>  <span class='fr' >ver: ".$GS['mms_ver'] ."</span>
							</div>
						</div>
						
					</body>
				</html>";
		
		
		}
		
		case "def_tpl":{
		
			return "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
				<html xmlns='http://www.w3.org/1999/xhtml'>
					<head>

						<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
						<meta name='robots' content='index, follow'>
						<meta name='keywords' content='{PS_KEYWORDS}'>
						<meta name='title' content='{PS_TITLE}'>
						<meta name='author' content='Administrator'>
						<meta name='description' content='{PS_DESCRIPTION}'>
						<meta name='google-site-verification' content='{PS_GOOGLE_KEY}' />
						<title>{PS_TITLE}</title>
						<style>
							body{background:#dddurl('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNAay06AAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDQvMDcvMTEZWfodAAAAOklEQVQYlX3KwQ0AIAxCUewM7L8Qq3QHPZlULXL6JG9ImiThFiSRmR4AwA/FDoeing4doEMPuFELKloZVRish7dDXwAAAABJRU5ErkJggg==') repeat scroll top left;margin:0;padding:0;font-family:Verdana,Geneva,sans-serif;font-size:13px;color:#666;}
							*{margin:0;padding:0;}
							/**element defaults**/ table{width:100%;text-align:left;}
							th,td{padding:10px 10px;}
							th{color:#fff;background:#FF6633 none repeat-x scroll left top;border-bottom:2px solid #FF4000;}
							td{border-bottom:1px solid #ccc;}
							code,blockquote{display:block;border-left:5px solid#222;padding:10px;margin-bottom:20px;}
							code{background-color:#222;color:#ccc;border:none;}
							blockquote{border-left:5px solid #222;}
							blockquote p{font-style:italic;font-family:Georgia,\"Times New Roman\",Times,serif;margin:0;color:#333;height:1%;}
							p{line-height:1.9em;margin-bottom:20px;font-size:12px;}
							a{color:#FF6633;}
							a:hover{color:#FF4000;}
							a:focus{outline:none;}
							fieldset{display:block;border:none;border-top:1px solid #ccc;}
							fieldset legend{font-weight:bold;font-size:13px;padding-right:10px;color:#333;}
							fieldset form{padding-top:15px;}
							fieldset p label{float:left;width:150px;}
							form input,form select,form textarea{padding:5px;color:#333333;border:1px solid #ddd;border-right:1px solid #ccc;border-bottom:1px solid #ccc;background-color:#fff;font-family:Arial,Helvetica,sans-serif;font-size:13px;}
							form input.formbutton{border:none;background:#FF6633 none repeat-x scroll left top;color:#ffffff;font-weight:bold;padding:6px 10px;font-size:12px;font-family:Tahoma,Geneva,sans-serif;letter-spacing:1px;width:auto;overflow:visible;}
							form.searchform p{margin:5px 0;}
							span.required{color:#ff0000;}
							h1{color:#000;font-size:35px;font-family:Arial,Helvetica,sans-serif;}
							h2{color:#000;font-family:Arial,Helvetica,sans-serif;font-size:27px;letter-spacing:0px;font-weight:normal;padding:0 0 5px;margin:0;}
							h3{color:#FF4000;font-size:24px;font-weight:normal;margin-bottom:10px;letter-spacing:-1px;font-family:Arial,Helvetica,sans-serif;}
							h4{padding-bottom:10px;font-size:15px;color:#FF794D;}
							h5{padding-bottom:10px;font-size:13px;color:#ccc;}
							ul,ol{margin:0 0 35px 35px;}
							li{padding-bottom:5px;}
							li ol,li ul{font-size:1.0em;margin-bottom:0;padding-top:5px;}
							#container{width:800px;margin:0 auto;padding:10px 10px 30px;background-color:#fff;}
							#header{padding:30px 15px;margin:0 auto;}
							#header h1 a{color:#444;font-size:50px;font-weight:normal;text-decoration:none;letter-spacing:-3px;float:left;border-bottom:2px solid #FF6633;}
							#header h2{color:#999;float:left;font-size:13px;margin-left:12px;margin-top:24px;padding-left:4px;letter-spacing:0;}
							#nav{height:33px;width:800px;background-color:#cfcfcf;}
							#nav ul{list-style:none;padding:0;margin:0;height:45px;}
							#nav ul li{float:left;display:block;padding:0;}
							#nav ul li a:hover,#navulli.selected{background-color:#FF6633;}
							#nav ul li a{color:#fff;display:block;border-right:1px solid #fff;font-size:11px;font-weight:bold;text-transform:uppercase;padding:10px 20px;text-decoration:none;}
							#nav ul li.start a{border-left:none;}
							#nav ul li.end a{border-right:none;}
							#nav ul li a:hover{color:#fff;text-decoration:underline;}
							#body{background:none;margin:0auto;padding:30px 12px 0;width:776px;}
							#content{float:left;width:535px;}
							.sidebar{width:200px;padding:10px 00;float:right;}
							.sidebar h3{color:#333;font-size:22px;}
							.sidebar ul{margin:0;padding:0;list-style:none;}
							.sidebar ul li{margin-bottom:20px;line-height:1.9em;}
							.sidebar li ul{}
							.sidebar li ul li{display:block;font-family:Arial,Helvetica,sans-serif;border-top:none;padding:6px 2px;margin:0;line-height:1.5em;font-size:13px;border:none;}
							.sidebar li ul li a{font-weight:normal;color:#222;}
							.sidebar li ul li a:hover{color:#FF6633;}
							.sidebar li ul.blocklist li{padding:0;display:inline;}
							.sidebar li ul.blocklist li a{background-color:#EAEAEA;display:block;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;margin-bottom:1px;padding:7px 10px;text-decoration:none;text-transform:uppercase;}
							.sidebar li ul.blocklist li a:hover{background-color:#FF6633;color:#fff;}
							.clear{clear:both;}
							#footer{margin:0auto0;border-top:10px solid#FF531A;width:800px;}
							#footer p{text-align:left;color:#ccc;font-size:12px;font-family:Arial,Helvetica,sans-serif;margin:0;padding:0;}
							#footer p a{color:#ccc;font-weight:bold;}
							.footer-content{padding:20px 12px 30px;background:#f0f0f0;border-top:10px solid #FF6633;}
							#footer.footer-content ul{width:238px;list-style:none;margin:0 30px 0 0;padding:0;float:left;}
							#footer.footer-content li{border-bottom:1px dashed#ddd;padding:7px 5px;}
							#footer.footer-content li a,#footer.footer-contenth4{font-family:Arial,Helvetica,sans-serif;}
							#footer.footer-content li a{color:#555;text-decoration:none;}
							#footer.footer-content li a:hover{color:#000;}
							#footer.footer-content h4{font-weight:normal;font-size:17px;color:#777;padding-bottom:0;}
							#footer.footer-content ul.endfooter{margin-right:0;}
							#footer.footer-content span.sitename{font-family:Arial,Helvetica,sans-serif;font-size:40px;font-weight:normal;letter-spacing:-2px;color:#aaa;float:left;}
							#footer.footer-bottom{padding:10px;text-align:center;background-color:#222;}
							#footer.footer-bottom p{text-align:center;}
							#footer.footer-bottom p,#footer.footer-bottom p a{color:#eee;}
							.highlight{color:white;background:red;}
							.label-success{display:block;padding:10px 30px;background:#CCFFCC}
							.label-error{display:block;padding:10px 30px;background:#FFCCCC}
							.galery ul{list-style:none;}
							.galery li{position:relative;float:left;}
							.galery li a{display:block;border:1px solid silver;padding:5px;margin:5px;}
							.galery li a:hover{background:#FF6633}
							.galery li img{display:block;}
						</style>
					</head>
					<body>
						<div id='container'>
							<div id='header'>
								<h1><a href='/'>{US_TITLE}</a></h1>
								<h2>{US_SLOGAN}</h2>
								<div class='clear'></div>
							</div>
							<div id='nav'>
								<ul>
								{PS_MENU}
								</ul>
							</div>
							<div id='body'>
								<div id='content'>
									<h2>{PV_TITLE}</h2>
									{PV_CONTENT}
								</div>

								<div class='sidebar'>
									<ul>	
										<li>
											{UV_BLOCK_1}
										</li>
										<li>
										{US_R_MENU}
										</li>

										<li>
										{UV_BLOCK_ABOUT}
										</li>

										<li>
										<h3>Search</h3>
										<ul>
										<li>
										<form method='get' class='searchform' action='' >
										<p>
										<input type='text' size='20' value='' name='s' class='s' /> 
										<input type='submit' class='searchsubmit formbutton' value='Search' />
										</p>
										</form>	
										</li>
										</ul>
										</li>

										<li>
										{UV_BLOCK_2}
										</li>

									</ul> 
								</div>
								<div class='clear'></div>
							</div>
							<div id='footer'>
								<div class='footer-content'>
									{US_LIST_1}
									{US_LIST_2}
									{US_LIST_3}

									<div class='clear'></div>
								</div>
								<div class='footer-bottom'>
									{US_FOOTER}
								</div>
							</div>
						</div>
					</body>
				</html>
			";
		
		
		}
		
		case "contact":{
		
			$a=  "<form id='form'>
                        <div class='success_wrapper'>
                          <div class='success'>Formularz został wysłany<br>
                          <strong>Wkrótce nasz pracownik skontaktuje się z Tobą w tej sprawie.</strong> </div>
                        </div>
                        <fieldset>
                            <label class='name'>
                                
                                <input type='text' value='Name:'>
                                <br class='clear'>
                                <span class='error error-empty'>*This is not a valid name.</span><span class='empty error-empty'>*Pole nie może być puste.</span> 
                            </label>
                            <label class='email'>
                                <input type='text' value='E-mail:'>
                                <br class='clear'>
                                <span class='error error-empty'>*Adres email jest nieprawidłowy.</span><span class='empty error-empty'>*Pole nie może być puste.</span> 
                            </label>
                            <!-- <label class='phone'>
                                <input type='tel' value='Phone:'>
                                <br class='clear'>
                                <span class='error error-empty'>*This is not a valid phone number.</span><span class='empty error-empty'>*This field is required.</span> 
                            </label> -->
                            <label class='message'>
                                <textarea>Wiadomość:</textarea>
                                <br class='clear'>
                                <span class='error'>*Wiadomość jest za krótka.</span> <span class='empty'>*Pole nie może być puste.</span> 
                            </label>
                            <div class='clear'></div>
                            <div class='btns'>
                                <a data-type='submit' class='more_btn'>Wyślij</a>
                                <a data-type='reset' class='more_btn'>wyczyść</a>
                                <div class='clear'></div>
                            </div>
                        </fieldset>
                    </form>";
			
			return  "
			<script src='".$GS['res_dir']."jquery-1.10.2.min.js'></script>
			<script src='".$GS['res_dir']."jquery.validate.min.js'></script>
			<script>
				$('#form').validate();
			</script>
			".$msg."
			<fieldset>
				<legend>Wypełnij poniższy formularz</legend>
				<form id='form' action='#' method='post'>
				<input type='hidden' name='action' value='sendmessage'>
				<p><label for='email'>Adres zwrotny:</label>
				<input name='email' id='email' value='".$_POST["email"]."' type='text' type='email' required/></p>

				<p><label for='message'>Wiadomość:</label>
				<textarea cols='37' rows='11' name='message' id='message' minlength='10' required/>".$_POST["message"]."</textarea></p>
				<p><label for='email'>Antyspam:</label>
				<img src='captcha.php' alt='captcha image'> <input name='captcha' id='captcha' value='' type='text' type='text' required/></p>
				<p><input name='send' style='margin-left: 150px;' class='formbutton' value='Wyślij' type='submit'></p>
				
				</form>
			</fieldset>";

		}

	}

}



?>