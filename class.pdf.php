<?php 

	class tri_pdfoutput_touren
	{
		var $ID;									//VK ID z. B. Bestellnummer, Rechnungsnummer
		var $template;								//Template des Moduls
		var $standardemplate;
		var $template_class;						//Klasse des Templates;
		var $templateordner	= 'tourentemplate';		//Ordnername zum Template
		var $prefix			= 'TO';
		var $modul			= 'touren';
		var $config			= array();						//PDF Einstellungen
		var $media			= 'A4';
		var $landscape		= false;
		var $margin			= array();
		var $lang;
		
		function init()
		{
			if(file_exists('cmssystem/'.$this->modul.'/'.$this->templateordner.'/'.$this->template.'/class_v2.php')){
				require_once('cmssystem/'.$this->modul.'/'.$this->templateordner.'/'.$this->template.'/class_v2.php');
			}else{
				require_once('../'.$this->modul.'/'.$this->templateordner.'/'.$this->template.'/class_v2.php');
			}
			$klassenname='tri_pdfoutput_'.$this->modul.'_'.$this->template;
			$this->template_class=new $klassenname;
			$this->template_class->init();
			$this->config=$this->template_class->config;
			$this->landscape=$this->template_class->landscape;
			$this->margin=$this->template_class->margin;
			$this->lang		= $this->template_class->lang;
		}
		
		function header($tri_pdfoutput){
			$content=$tri_pdfoutput->get_template('header',$this->modul,$this->templateordner,$this->template);
			if(method_exists($this->template_class,'header')){
				$content=$this->template_class->header($content);
			}
			$content=$this->output_parser($content,$tri_pdfoutput);
			return $content;
		}
		
		function content($tri_pdfoutput)
		{
			$ausgabe['content'] 			= $tri_pdfoutput->get_template('index',$this->modul,$this->templateordner,$this->template);
			$ausgabe['kunden']				= $tri_pdfoutput->get_template('index_kunden',$this->modul,$this->templateordner,$this->template);
						
			if(method_exists($this->template_class,'content'))
			{
				$content=$this->template_class->content($this->ID,$ausgabe,'',$this->lang);
			}else{	
				$content=touren_generieren_parser($this->ID,$ausgabe,'',$this->lang);
			}
			$content=$this->output_parser($content,$tri_pdfoutput);
			return $content;
		}
		
		function footer($tri_pdfoutput){
			$content=$tri_pdfoutput->get_template('footer',$this->modul,$this->templateordner,$this->template);
			if(method_exists($this->template_class,'footer')){
				$content=$this->template_class->footer($content);
			}
			$content=$this->output_parser($content,$tri_pdfoutput);
			return $content;
		}
		
		function output_parser($content,$tri_pdfoutput)
		{
			if(is_array($GLOBALS['tri_conf']['cache']['touren']['row'][$this->ID]))
			{
				$row_touren 	= $GLOBALS['tri_conf']['cache']['touren']['row'][$this->ID];
			}
			else
			{
				$res_touren 	= tri_db_query ($GLOBALS['datenbanknamecms'], "SELECT * FROM touren where ID='".$this->ID."'");
				$row_touren 	= mysql_fetch_array ($res_touren);	
				$GLOBALS['tri_conf']['cache']['touren']['row'][$this->ID] = $row_touren;
			}
			$content	= str_replace('"Labels/','"http://'.$_SERVER[HTTP_HOST].'/cmssystem/'.$this->modul.'/'.$this->templateordner.'/'.$this->template.'/Labels/',$content);
			$content	= $tri_pdfoutput->parse_customer_information($content,$row_touren['kundennummer'],$row_touren['ansprechpartner']);
			$content	= $tri_pdfoutput->parse_shop_information($content,$row_touren['mandanten_ID']);
			$content	= $tri_pdfoutput->parse_contact_person($content,$row_touren['edit'],$row_touren['mandanten_ID']);
			$content	= str_replace('{mod_preaefix}',$this->prefix,$content);
			
			return $content;
		}
	}
	
?>