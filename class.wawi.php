<?php 

	class touren_wawi
	{
		function __construct()
		{
			$this->options['templates']				= TRUE;
			$this->options['templatepfad']			= 'tourentemplate';
			$this->options['mandaten']				= TRUE;
			$this->options['createIdStructureByID'] = false;
			/*
			$this->options['postenSQL'] 			= "SELECT verleihungsid AS vkid,ID AS postenid FROM verleihungen_positionen WHERE verleihungsid='{value}'";

			$this->modulReihenfolge 				= array('%module%2druckserver','verleihungen2bestellungen','verleihungen2lieferungen','verleihungen2rechnungen','bestellungen2bankkonten','bestellungen2angebote','bestellungen2lieferungen','bestellungen2verleihungen','bestellungen2lieferantenbestellung','bestellungen2rechnungen','bestellungen2kassensystem','bestellungen2xtcconnector','bestellungen2yatego','bestellungen2tradoria','bestellungen2auktionator','rechnungen2gutschriften','rechnungen2mahnungen','rechnungen2reklamationen');

			$this->options['bestellungen2verleihungen']['sql']			
				= "	SELECT verleihungen_positionen.ID AS postenid,
							verleihungen_positionen.verleihungsid AS vkid,
							verleihungen.status AS status,
							verleihungen.verleihdatum AS datum
					FROM 	verleihungen_positionen,
							verleihungen
					WHERE 	({where_start} bestellungspostenid='{value}' OR {where_end})
					AND		verleihungen_positionen.verleihungsid = verleihungen.ID
					GROUP BY vkid";	
					
			$this->options['lieferungen2verleihungen']['sql']			
				= "	SELECT verleihungen_positionen.ID AS postenid,
							verleihungen_positionen.verleihungsid AS vkid,
							verleihungen.status AS status,
							verleihungen.verleihdatum AS datum
					FROM 	verleihungen_positionen,
							verleihungen,
							lieferungen_positionen
					WHERE	({where_start} lieferungen_positionen.ID='{value}' OR {where_end})
					AND		lieferungen_positionen.verleihungspostenid=verleihungen_positionen.ID
					AND		verleihungen_positionen.verleihungsid = verleihungen.ID
					GROUP BY vkid";			
					
			$this->options['rechnungen2verleihungen']['sql']			
				= "	SELECT verleihungen_positionen.ID AS postenid,
							verleihungen_positionen.verleihungsid AS vkid,
							verleihungen.status AS status,
							verleihungen.verleihdatum AS datum
					FROM 	verleihungen_positionen,
							verleihungen,
							rechnungen_positionen
					WHERE	({where_start} rechnungen_positionen.ID='{value}' OR {where_end})
					AND		verleihungspostenid=verleihungen_positionen.ID
					AND		verleihungen_positionen.verleihungsid = verleihungen.ID
					GROUP BY vkid";				
			*/
		}
	}

?>