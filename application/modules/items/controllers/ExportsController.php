<?php

class Items_ExportsController extends Auction_Controller_Action
{
    protected $moduleName = 'items';
    
    public function indexAction()
    {
        try {
        	$this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }

    public function ebayAction()
    {
        try {
            $this->authenticateAction('view');

            $this->view->blockArray = $this->getBlockArray();

        } catch (Metis_Auth_Exception $e) {
            $e->failed();
            return;
        }
    }

    public function ebayprocessAction()
    {
        try {
            $this->authenticateAction('view');

            require_once('models/Block.php');
            $tBlock = new models_Block();
            $block = $tBlock->find($this->_getParam('blockId'))->current();

            $filename = 'ebayExport_' . $this->_getParam('blockId') . '.csv';

            $savePath = $this->getExportPath() . DIRECTORY_SEPARATOR . $filename;
            $fp = fopen($savePath, 'w');

            fputcsv($fp, array(
                '*Action(SiteID=Canada|Country=CA|Currency=CAD|Version=941)',
                '*ProductName',
                '*Category',
                '*Title',
                '*Description',
                '*ConditionID',
                '*Quantity',
                '*Format',
                '*StartPrice',
                '*Duration',
                'ImmediatePayRequired',
                '*Location',
                'PayPalAccepted',
                'PayPalEmailAddress',
                '*ReturnsAcceptedOption',
                'ScheduleTime',
                'PackageLength',
                'PackageWidth',
                'PackageDepth',
                'WeightMajor',
                'WeightMinor',
                'WeightUnit',
                'Product:UPC'
            ), "\t");

            $table = new models_vItemList();

            $select = $table->select();

            $select->where('blockId = ?', $this->_getParam('blockId'))
                ->order('controlNumber');

            $items  = $table->fetchAll($select);

            foreach ($items as $item) {

                $weight_lb = floor($item->weight);
                $weight_oz = ($item->weight * 16) % 16;

                $row = array(
                    'AddProduct',
                    str_replace("'", "", substr($item->name, 0, 55)),
                    88433,
                    str_replace("'", "", substr($item->name, 0, 80)),
                    nl2br(str_replace("'", "", $item->description)),
                    1000,
                    1,
                    'Auction',
                    $item->fairRetailPrice / 2,
                    7,
                    1,
                    'Ontario Canada',
                    1,
                    'paypal@rotaryonline.auction',
                    'ReturnsNotAccepted',
                    $block->blockDate . ' 20:00:00',
                    $item->length,
                    $item->width,
                    $item->height,
                    $weight_lb,
                    $weight_oz,
                    'lb',
                    $item->upc
                );

                fputcsv($fp, $row, "\t");
            }

            fclose($fp);

            $this->_redirector->gotoUrl('/exports/' . $filename);

        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }
    }
    
    public function inventoryAction()
    {
        try {  $table = new models_vItemDetail();
	        $templatePath = $this->getTemplatePath() . DIRECTORY_SEPARATOR . 'tpl_inventory.pdf';
	        $pdf = Zend_Pdf::load($templatePath);
	        
	        $template = $pdf->pages[0];
	        
			$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
			$font_b = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
			$font_c = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
			$font_d = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
	        
	        $table = new models_vItemDetail();
	        
	        $where = array();
	        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $where[] = $table->getAdapter()->quoteInto('blockNumber > ?', 0);
	        $order = array('binNumber', 'controlSource', 'controlNumber');
	        $items  = $table->fetchAll($where, $order);
	        
	        $labelPoints = new Auction_ItemLabel();
	        $labelPoints->setupPoints(new Auction_Point(140, 460), // itemNumber
	        					  		new Auction_Point(20, 1000), // blockNumber
	                                    new Auction_Point(85, 495), // itemName
	                                    new Auction_Point(50, 470), // itemDesc
	                                    new Auction_Point(22, 860), // itemValue
	                                    new Auction_Point(25, 480), // controlNumber
	                                    new Auction_Point(440, 915), // donor
	                                    new Auction_Point(65, 100), // time
	                                    new Auction_Point(85, 460)); // bin
	                                    
	        $startPoints = array(
	                        new Auction_Point(0, 0), new Auction_Point(0, -35),
	                        new Auction_Point(0, -70), new Auction_Point(0, -105),
	                        new Auction_Point(0, -140), new Auction_Point(0, -175),
	                        new Auction_Point(0, -208), new Auction_Point(0, -243),
	                        new Auction_Point(0, -278), new Auction_Point(0, -312),
	                        new Auction_Point(0, -347), new Auction_Point(0, -382),
	                        new Auction_Point(0, -416), new Auction_Point(0, -451)
	                       );
	        
	        while ($items->valid()){
	            $page = new Zend_Pdf_Page($template);
	            
	            $sides = 2;
	            $xModifier = 396;
	            
	            for ($side = 1; $side <= $sides; $side++) {
		            $item = $items->current();
		            if (!$items->valid()) {
		            	break;
		            }
		            $currentBin = $item->binNumber;
		            
		            $xmod = 0;
		            switch ($side) {
		            	case 1:
		            		$xmod = 0;
		            		break;
		            	case 2:
		            		$xmod = $xModifier;
		            		break;
		            }
		            
		            // Draw the header stuff
		            $page->setFont($font_b, 16);
	                $page->drawText('BIN #' . $item->binNumber, 20 + $xmod, 535);
	                
		            $page->setFont($font_b, 12);
	                $page->drawText($this->view->getActiveAuctionDate(1), 185 + $xmod, 555);
		            
	                
		            $rowCount = 1;
		            do {
		            	// This is to cover items that don't have blocks... sloppy but it works.
		            	if ($rowCount > 14) {
		            		break;
		            	}
		            	
		                $labelPoints->setStartPoint($startPoints[$rowCount-1]);
		                
		                // draw control number
		                $page->setFont($font_b, 18);
		                $page->drawText($item->controlSource . $item->controlNumber, $labelPoints->getControlNumber()->getX() + $xmod, $labelPoints->getControlNumber()->getY());
		                
		                // Draw Item Name over multiple lines
		                $page->setFont($font_d, 10);
				        $name = wordwrap(str_replace(array("\r\n", "\n", "\r"), ' ', $item->itemName), 36, "\n", false);
				        $line = strtok($name, "\n");
				        $name_y = $labelPoints->getItemName()->getY();
				        $lineCount = 1;
				        while($line !== false){
				            if ($lineCount > 3) break;
				            $page->drawText(iconv('ISO-8859-1', 'ASCII//TRANSLIT', $line), $labelPoints->getItemName()->getX() + $xmod, $name_y);
				            $name_y -= 10;
				            $line = strtok("\n");
				            $lineCount++;
				        }
		                
		                $items->next();
		                $item = $items->current();
		                $rowCount++;
		             } while ($items->valid() && $currentBin == $item->binNumber);
	            }
	            $pdf->pages[] = $page;
	        }
	        
	        unset($pdf->pages[0]);
	        
	        $savePath = $this->getPdfPath() . DIRECTORY_SEPARATOR . 'inventorySheets_' . $this->view->getActiveAuctionDate(1) . '.pdf';
	        $pdf->save($savePath);
	
	        $this->_redirector->gotoUrl('/pdf/inventorySheets_' . $this->view->getActiveAuctionDate(1) . '.pdf');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function blocksheetAction()
    {
        try {  $table = new models_vItemDetail();
	        $templatePath = $this->getTemplatePath() . DIRECTORY_SEPARATOR . 'tpl_blocksheet.pdf';
	        $pdf = Zend_Pdf::load($templatePath);
	        
	        $template = $pdf->pages[0];
	        
			$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
			$font_b = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
			$font_c = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
	        
	        $table = new models_vItemDetail();
	        
	        $where = array();
	        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $where[] = $table->getAdapter()->quoteInto('blockNumber > ?', 0);
	        $order = array('blockNumber', 'controlNumber');
	        $items  = $table->fetchAll($where, $order);
	        
	        $labelPoints = new Auction_ItemLabel();
	        $labelPoints->setupPoints(new Auction_Point(140, 460), // itemNumber
	        					  		new Auction_Point(20, 1000), // blockNumber
	                                    new Auction_Point(140, 468), // itemName
	                                    new Auction_Point(100, 460), // itemDesc
	                                    new Auction_Point(22, 860), // itemValue
	                                    new Auction_Point(22, 460), // controlNumber
	                                    new Auction_Point(440, 915), // donor
	                                    new Auction_Point(65, 100), // time
	                                    new Auction_Point(85, 460)); // bin
	                                    
	        $startPoints = array(
	                        new Auction_Point(0, 0), new Auction_Point(0, -45),
	                        new Auction_Point(0, -90), new Auction_Point(0, -135),
	                        new Auction_Point(0, -180), new Auction_Point(0, -225),
	                        new Auction_Point(0, -270), new Auction_Point(0, -315),
	                        new Auction_Point(0, -360)
	                       );
	        
	        while ($items->valid()){
	            $page = new Zend_Pdf_Page($template);
	            
	            $sides = 2;
	            $xModifier = 396;
	            
	            for ($side = 1; $side <= $sides; $side++) {
		            $item = $items->current();
		            if (!$items->valid()) {
		            	break;
		            }
		            $currentBlock = $item->blockNumber;
		            
		            $xmod = 0;
		            switch ($side) {
		            	case 1:
		            		$xmod = 0;
		            		break;
		            	case 2:
		            		$xmod = $xModifier;
		            		break;
		            }
		            
		            // Draw the header stuff
		            $page->setFont($font_b, 16);
	                $page->drawText('BLOCK #' . $item->blockNumber, 20 + $xmod, 535);
		            $page->setFont($font, 14);
	                $page->drawText('TIME: ' . $item->blockTime, 20 + $xmod, 520);
	                
		            $page->setFont($font_b, 12);
	                $page->drawText($this->view->getActiveAuctionDate(1), 185 + $xmod, 555);
		            
	                
		            $rowCount = 1;
		            do {
		            	// This is to cover items that don't have blocks... sloppy but it works.
		            	if ($rowCount > 9) {
		            		break;
		            	}
		            	
		                $labelPoints->setStartPoint($startPoints[$rowCount-1]);
		                
		                // draw bin number
		                $page->setFont($font_b, 14);
		                $page->drawText($item->binNumber, $labelPoints->getBin()->getX() + $xmod, $labelPoints->getBin()->getY());
		                
		                // draw item number
		                //$page->setFont($font_b, 14);
		                //$page->drawText($item->itemNumber, $labelPoints->getItemNumber()->getX() + $xmod, $labelPoints->getItemNumber()->getY());
		                
		                // draw control number
		                $page->setFont($font_b, 14);
		                $page->drawText($item->controlSource . $item->controlNumber, $labelPoints->getControlNumber()->getX() + $xmod, $labelPoints->getControlNumber()->getY());
		                
		                // Draw Item Name over multiple lines
		                $page->setFont($font_c, 10);
				        $name = wordwrap(str_replace(array("\r\n", "\n", "\r"), ' ', $item->itemName), 38, "\n", false);
				        $line = strtok($name, "\n");
				        $name_y = $labelPoints->getItemName()->getY();
				        $lineCount = 1;
				        while($line !== false){
				            if ($lineCount > 3) break;
				            $page->drawText(iconv('ISO-8859-1', 'ASCII//TRANSLIT', $line), $labelPoints->getItemName()->getX() + $xmod, $name_y);
				            $name_y -= 10;
				            $line = strtok("\n");
				            $lineCount++;
				        }
		                
		                $items->next();
		                $item = $items->current();
		                $rowCount++;
		             } while ($items->valid() && $currentBlock == $item->blockNumber);
	            }
	            $pdf->pages[] = $page;
	        }
	        
	        unset($pdf->pages[0]);
	        
	        $savePath = $this->getPdfPath() . DIRECTORY_SEPARATOR . 'blocksheets_' . $this->view->getActiveAuctionDate(1) . '.pdf';
	        $pdf->save($savePath);
	
	        $this->_redirector->gotoUrl('/pdf/blocksheets_' . $this->view->getActiveAuctionDate(1) . '.pdf');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function logicalsortAction()
    {
        try {
        	$this->authenticateAction('view');
        	
	        require_once('Zend/Pdf.php');
	        $templatePath = $this->getTemplatePath() . DIRECTORY_SEPARATOR . 'tpl_logicalSort.pdf';
	        $pdf = Zend_Pdf::load($templatePath);
	        
	        $template = $pdf->pages[0];
	        
			$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	        
	        require_once('models/vItemList.php');
	        $table = new models_vItemList();
	        
	        $where = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $order = array('controlNumber');
	        $items  = $table->fetchAll($where, $order);
	        
	        $i = 2;
			foreach ($items as $item) {
				
				// Fix for missing items printed 2013
				/*
				$wehave = array("2","3","4","5","6","7","8","9","11","12","13","14","16","20","21","23","25","28","29","32","33","34","35","36","37","38","39","40","41","42","44","45","46","47","49","50","51","52","53","54","55","56","57","58","59","60","61","64","65","66","67","68","69","70","71","72","73","74","75","78","79","80","81","82","85","87","88","89","90","92","93","94","101","102","103","104","105","107","108","109","114","116","118","119","120","122","123","124","125","126","127","128","129","130","131","132","133","134","135","136","137","138","139","140","141","142","143","144","145","146","147","148","149","151","152","154","155","157","158","159","160","161","163","164","165","166","168","170","171","172","173","175","176","177","178","179","180","181","182","183","184","185","186","187","188","189","190","191","192","194","196","200","201","202","203","204","205","206","207","208","209","210","211","212","213","214","215","216","217","218","219","220","221","222","223","226","227","228","229","231","232","233","234","235","236","237","238","240","242","243","244","245","246","247","248","251","252","253","254","256","257","258","259","262","263","264","266","267","268","269","271","272","273","274","275","276","279","280","281","282","283","284","285","286","293","294","295","296","297","298","299","300","301","302","303","304","305","308","309","310","311","312","313","317","318","320","321","322","323","324","325","326","327","328","329","330","331","332","333","334","336","337","338","339","340","341","343","344","345","348","349","350","351","352","355","356","357","358","359","360","363","365","366","367","368","369","370","371","372","373","374","375","376","382","383","384","385","386","387","388","389","391","392","394","395","396","397","399","400","401","402","403","404","405","406","407","408","409","410","421","422","423","425","427","430","431","432","435","436","437","438","439","440","443","444","445","446","447","448","449","450","451","452","453","454","455","456","457","458","459","460","461","462","463","464","465","466","467","468","469","470","471","472","473","474","475","476","479","480","481","486","487","488","489","500","501","502","503","504","505","506","507","510","511","512","513","514","516","517","518","521","522","523","524","525","526","527","531","532","533","534","535","536","540","542","543","544","545","546","547","548","549","550","551","553","555","556","557","558","559","560","561","562","563","564","565","566","567","568","569","570","571","573","577","578","579","580","581","582","583","585","587","588","589","590","591","592","593","594","595","596","597","598","599","601","606","607","608","609","610","613","614","615","616","618","619","620","623","624","625","626","627","628","629","633","634","635","636","637","638","639","641","642","646","647","648","649","650","651","653","654","655","656","657","658","659","660","661","662","664","665","667","668","672","673","674","675","676","677","678","679","680","681","683","686","687","688","689","690","691","692","693","694","695","696","697","698","699","700","701","702","703","704","705","707","709","710","711","712","713","714","715","717","718","719","720","723","725","726","727","728","730","731","732","734","735","736","738","739","740","741","742","743","746","748","749","750","752","754","755","756","757","758","770","771","772","774","775","776","777","779","781","782","783","784","785","786","788","789","790","791","792","793","796","798","799","800","801","802","803","804","805","806","807","808","809","812","814","815","816","817","818","819","821","822","823","825","826","827","829","830","831","832","837","839","840","841","842","843","844","845","846","847","848","849","850","851","852","853","854","855","856","857","858","859","860","861","862","863","864","865","866","867","868","869","871","872","875","877","879","882","883","885","886");
				if (in_array($item->controlNumber, $wehave)) {
					continue;
				}
				*/
				
			    $pagePart = $i % 2;
		        
		        $control_x = 464;
		        $control_y = 701;
		        
		        $bin_x = 364;
		        $bin_y = 701;
		        
		        $label_x     = 130;
		        $itemName_y  = 652;
		        $itemValue_y = 623;
		        $donorName_y = 409;
		        
		        $desc_x = 40;
		        $desc_y = 555;
		        
		        switch ($pagePart){
		            case 0:
		                break;
		            case 1:
		                $control_y   = 332;
				        $itemName_y  = 283;
				        $itemValue_y = 255;
				        $donorName_y = 40;
		                $desc_y      = 190;
		                $bin_y       = 332;
		                break;
		        }
			    
			    if ($pagePart == 0){
		            $page = new Zend_Pdf_Page($template);
			    }
		        
		        $page->setFont($font, 36);
		        $page->drawText($item->controlSource . $item->controlNumber, $control_x, $control_y);
		        
		        $page->setFont($font, 36);
		        $page->drawText($item->binNumber, $bin_x, $bin_y);
		        
		        $page->setFont($font, 18);
		        $page->drawText($item->name, $label_x, $itemName_y);
		        $page->drawText('$' . $item->fairRetailPrice, $label_x, $itemValue_y);
		        $page->setFont($font, 18);
		        $page->drawText($item->donorCompany . ', ' .$item->donorFirstName . ' ' . $item->donorLastName, $label_x, $donorName_y);
		        
		        $page->setFont($font, 14);
		        
		        $description = wordwrap($item->description, 80, "\n", false);
		        $line = strtok($description, "\n");
		        $lineCount = 1;
		        while($line !== false){
		            if ($lineCount > 9) break;
		            $page->drawText(iconv('ISO-8859-1', 'ASCII//TRANSLIT', $line), $desc_x, $desc_y);
		            $desc_y -= 15;
		            $line = strtok("\n");
		            $lineCount++;
		        }
		        
		        if ($pagePart == 1 || $i > $items->count()) {
		            $pdf->pages[] = $page;
		        }
		        $i++;
			}
			
			unset($pdf->pages[0]);
			
	        $savePath = $this->getPdfPath() . DIRECTORY_SEPARATOR . 'logicalSort_' . $this->view->getActiveAuctionDate(1) . '.pdf';
	        
	        $pdf->save($savePath);
	        
	        $this->_redirector->gotoUrl('/pdf/logicalSort_' . $this->view->getActiveAuctionDate(1) . '.pdf');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function itemlistAction()
    {
        try {
        	$this->authenticateAction('view');
        
	        require_once('Zend/Pdf.php');
	        $templatePath = $this->getTemplatePath() . DIRECTORY_SEPARATOR . 'tpl_itemlist.pdf';
	        $pdf = Zend_Pdf::load($templatePath);
	        
	        $template = $pdf->pages[0];
	        
			$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
			$font_b = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
	        
	        require_once('models/vItemList.php');
	        $table = new models_vItemList();
	        
	        $where = array();
	        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $where[] = $table->getAdapter()->quoteInto('controlNumber > ?', 0);
	        $where[] = $table->getAdapter()->quoteInto('blockNumber > ?', 0);
	        $order = array('blockNumber', 'controlNumber');
	        $items  = $table->fetchAll($where, $order);
	        
	        // set the x vals for each field
	        $block_x    = 30;
	        //$itemNum_x  = 90;
	        $date_x     = 78; // old 130
	        $time_x     = 130; // old 190
	        $itemName_x = 188; // old 240
	        $value_x    = 525; // old 525
	        
	        // loop to create all the pages.
	        $pageNum = 1;
	        
	        while ($items->valid()){
	            $page = new Zend_Pdf_Page($template);
	            
	            $page->setFont($font_b, 20);
                $page->drawText($this->view->getActiveAuctionDate(1) . ' Metro Toronto Rotary Auction', 140, 740);
                
	            $page->setFont($font_b, 14);
                if ($this->_getParam('type') == 'ops') {
                	$page->drawText('Operations Item Listing', 220, 725);
                } else {
                	$page->drawText('Public Item Listing', 235, 725);
                }
	            
                $page->setFont($font_b, 12);
                $page->drawText('BLCK', 29, 690);
                //$page->drawText('ITEM #', 82, 690);
                if ($this->_getParam('type') == 'ops') {
                	$page->drawText('CNTRL', 80, 690); // 131 old val
                } else {
                	$page->drawText('NMBR', 86, 690); // 136 old val
                }
                $page->drawText('TIME', 137, 690); // 192 old val
                $page->drawText('ITEM NAME', 192, 690); // 343 old val
                $page->drawText('VALUE', 537, 690);
	            
	            // set the start value for y for a new page
		        $line_y = 671;
	            
	            // Loop through each of 4 blocks for a page (page loop)
	            for ($i=0; $i < 4; $i++){
	                $item = $items->current();
                	
	                $currentBlock = $item->blockNumber;
	                
	                // Loop through all the items for the current block
	                $itemCount = 1;
	                do {
	                	$time = strtotime($item->blockDate . ' ' . $item->blockTime);
	                    // add $row to the PDF
	                    $page->setFont($font, 14);
	                    $page->drawText(($itemCount==1?$item->blockNumber:''), $block_x, $line_y);
	                    $page->setFont($font, 12);
	                    //$page->drawText($item->itemNumber, $itemNum_x, $line_y);
		                if ($this->_getParam('type') == 'ops') {
	                    	$page->setFont($font, 12);
		                	$page->drawText($item->controlSource . $item->controlNumber, $date_x+5, $line_y);
		                } else {
	                    	$page->setFont($font, 10);
	                    	$page->drawText($item->controlNumber, $date_x+5, $line_y);
		                	//$page->drawText(date("M jS", $time), $date_x, $line_y);
		                }
	                    $page->drawText(date("g:ia", $time), $time_x, $line_y);
	                    $page->drawText(substr($item->name, 0, 47), $itemName_x, $line_y);
	                    $page->setFont($font, 11);
	                    $page->drawText('$' . $item->fairRetailPrice, $value_x, $line_y);
	                    
	                    $items->next();
	                    $item = $items->current();
	                    $itemCount++;
	                    $line_y -= 18;
	                } while ($items->valid() && $item->blockNumber == $currentBlock);
	                
	                /*
	                 * If, while looping through the 4 blocks on a page, the
	                 * resultset is ended, break out of the page loop.
	                 */
	                
	                if (!$items->valid()) break;
	            }
	            
	            $page->setFont($font, 10);
	            $page->drawText('PAGE ' . $pageNum, 285, 20);
	            
	            $pageNum++;
	            
	            $pdf->pages[] = $page;
	        }
	        
	        unset($pdf->pages[0]);
	        
	        $prefix = '';
	        
            if ($this->_getParam('type') == 'ops') {
                $prefix = 'itemlist_';
            } else {
                $prefix = 'publiclist_';
            }
            
	        $savePath = $this->getPdfPath() . DIRECTORY_SEPARATOR . $prefix . $this->view->getActiveAuctionDate(1) . '.pdf';
	        $pdf->save($savePath);
	        $this->_redirector->gotoUrl('/pdf/' . $prefix . $this->view->getActiveAuctionDate(1) . '.pdf');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function itemlabelsAction()
    {
        try {
        	$this->authenticateAction('view');
        
	        require_once('Zend/Pdf.php');
	        require_once('Auction/ItemLabel.php');
	        require_once('Auction/Point.php');
	        
	        $pdf = new Zend_Pdf();
	        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	        $font_b = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
	        
	        require_once('models/vItemList.php');
	        $table = new models_vItemList();
	        
	        $where = array();
	        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        // Fix 2011 missing stickers
	        //$where[] = $table->getAdapter()->quoteInto("CONCAT(controlSource, controlNumber) IN ('S269','S201','S185','S024','S025','U078','S023','X070','X049','X050','S300','S266','S222','S223','S267','X024','S108','S099','S068','W148','S291','S292','W157','W158','W159','T035')", "");

	        // Fix 2013 missing stickers
	        //$where[] = $table->getAdapter()->quoteInto("controlNumber IN ('601', '898', '899', '336', '327', '326')", "");
	        $where[] = $table->getAdapter()->quoteInto('controlNumber > ?', 0);
	        $order = array('binNumber', 'controlSource', 'controlNumber');
	        $items  = $table->fetchAll($where, $order);
	        
	        $labelPoints = new Auction_ItemLabel();
	        $labelPoints->setupPoints(new Auction_Point(0, 0), new Auction_Point(0, 0),
	                                    new Auction_Point(90, 732), new Auction_Point(0, 0), // 1st is the item name, using this for the year. 
	                                    new Auction_Point(0, 0), new Auction_Point(90, 765), // 2nd one is the Control number
	                                    new Auction_Point(0, 725), new Auction_Point(0, 0), // 1st number is the donor, using this for the QR code
	                                    new Auction_Point(90, 745)); // this is the BIN
	        
	                                    
	        // Set start points for each label, one row at a time.
	        $startPoints = array();
	        
	        // rows
	        $startRows = -35; // was -41
	        for ($i = 0; $i < 10; $i++) {
	        	// columns
	        	$startCols = 20;
	        	for ($j = 0; $j < 3; $j++) {
	        		$startPoints[$i][$j] = new Auction_Point($startCols, $startRows);
	        		$startCols += 198;
	        	}
	        	$startRows -= 72;
	        }
	        
	        $qrsize = 90;
	        $qrpdfsize = 65;
	        
	        $year = $this->view->getActiveAuctionDate(1);
	        
	        while ($items->valid()){
	            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
	            
	            // Loop thru rows
		        for ($i=0; $i < 10; $i++){
		        	// Loop thru cols
		            for ($j=0; $j < 3; $j++){
		                
		                if (!$items->valid()){
		                    break 2;
		                }

		                $item = $items->current();
		                $labelPoints->setStartPoint($startPoints[$i][$j]);
		                
		                // do the QR code thing
		                $qrString = $item->controlSource . $item->controlNumber . '_' . str_pad($this->getCurrentAuctionId(), 2, '0', STR_PAD_LEFT) . '|' . str_pad($item->binNumber, 3, '0', STR_PAD_LEFT);
		                $qrString = 'https://system.metrotorontorotaryauction.com/mobile/scan/view/code/' . $qrString;
		                $fileUrl = 'http://chart.apis.google.com/chart?chs=' . $qrsize . 'x' . $qrsize . '&cht=qr&chld=L|0&chl=' . urlencode($qrString);
		                $filePath = $this->getPdfPath() . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $item->controlSource . $item->controlNumber .'.png';
		                file_put_contents($filePath, file_get_contents($fileUrl));
		                $qrImage = Zend_Pdf_Image::imageWithPath($filePath);
		                $page->drawImage($qrImage,  $labelPoints->getDonor()->getX(), $labelPoints->getDonor()->getY(), $labelPoints->getDonor()->getX() + $qrpdfsize, $labelPoints->getDonor()->getY() + $qrpdfsize);
		                unlink($filePath);
		                
		                $page->setFont($font_b, 32);
		                $page->drawText($item->controlSource . $item->controlNumber, $labelPoints->getControlNumber()->getX(), $labelPoints->getControlNumber()->getY());
		                
		                $page->setFont($font_b, 16);
		                $page->drawText('bin #: ' . $item->binNumber, $labelPoints->getBin()->getX(), $labelPoints->getBin()->getY());
		                
		                $page->setFont($font_b, 10);
		                $page->drawText($year . ' Auction Year', $labelPoints->getItemName()->getX(), $labelPoints->getItemName()->getY());
		                $items->next();
		            }
		        }
		        $pdf->pages[] = $page;
	        }
	        
	        $savePath = $this->getPdfPath() . DIRECTORY_SEPARATOR . 'itemLabels_' . $this->view->getActiveAuctionDate(1) . '.pdf';
	        
	        $pdf->save($savePath);
	
	        $this->_redirector->gotoUrl('/pdf/itemLabels_' . $this->view->getActiveAuctionDate(1) . '.pdf');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function bibleAction()
    {
        try {
        	$this->authenticateAction('view');
        	
	        require_once('Zend/Pdf.php');
	        require_once('Auction/ItemLabel.php');
	        require_once('Auction/Point.php');
	        
	        $templatePath = $this->getTemplatePath() . DIRECTORY_SEPARATOR . 'tpl_bible.pdf';
	        $pdf = Zend_Pdf::load($templatePath);
	        
	        $template = $pdf->pages[0];
	        
			$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
			$font_b = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
			$font_c = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
			$font_d = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
	        
	        $table = new models_vItemDetail();
	        
	        $where = array();
	        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $where[] = $table->getAdapter()->quoteInto('controlNumber > ?', 0);
	        $where[] = $table->getAdapter()->quoteInto('blockNumber > ?', 0);
	        $order = array('blockNumber', 'controlNumber');
	        $items  = $table->fetchAll($where, $order);
	        
	        $labelPoints = new Auction_ItemLabel();
	        $labelPoints->setupPoints(new Auction_Point(22, 905), // itemNumber
	        					  		new Auction_Point(20, 1000), // blockNumber
	                                    new Auction_Point(105, 915), // itemName
	                                    new Auction_Point(105, 900), // itemDesc
	                                    new Auction_Point(22, 860), // itemValue
	                                    new Auction_Point(22, 905), // controlNumber old 875
	                                    new Auction_Point(440, 915), // donor
	                                    new Auction_Point(65, 100), // time
	                                    new Auction_Point(22, 890)); // bin
	                                    
	        $startPoints = array(
	                        new Auction_Point(0, 0), new Auction_Point(0, -95),
	                        new Auction_Point(0, -190), new Auction_Point(0, -285),
	                        new Auction_Point(0, -380), new Auction_Point(0, -475),
	                        new Auction_Point(0, -570), new Auction_Point(0, -665),
	                        new Auction_Point(0, -760)
	                       );
	        
	        while ($items->valid()){
	            $page = new Zend_Pdf_Page($template);
	            
	            $item = $items->current();
	            $currentBlock = $item->blockNumber;
	            
	            // Draw the header stuff
	            $page->setFont($font_b, 16);
                $page->drawText('BLOCK #' . $item->blockNumber, 20, 975);
                $page->drawText('BLOCK #' . $item->blockNumber, 495, 975);
	            $page->setFont($font, 14);
                $page->drawText('TIME: ' . $item->blockTime, 20, 960);
                $page->drawText('TIME: ' . $item->blockTime, 495, 960);
                
	            $page->setFont($font_b, 20);
                $page->drawText($this->view->getActiveAuctionDate(1) . ' Metro Toronto Rotary Auction', 140, 975);
                
	            
	            $rowCount = 1;
	            do {
	            	// This is to cover items that don't have blocks... sloppy but it works.
	            	if ($rowCount > 9) {
	            		break;
	            	}
	            	
	                $labelPoints->setStartPoint($startPoints[$rowCount-1]);
	                
	                // draw bin number
	                $page->setFont($font_b, 12);
	                $page->drawText('Bin #' . $item->binNumber, $labelPoints->getBin()->getX(), $labelPoints->getBin()->getY());
	                
	                // draw item number
	                //$page->setFont($font_b, 12);
	                //$page->drawText('Item #' . $item->itemNumber, $labelPoints->getItemNumber()->getX(), $labelPoints->getItemNumber()->getY());
	                
	                // draw control number
	                $page->setFont($font_b, 18);
	                $page->drawText($item->controlSource . $item->controlNumber, $labelPoints->getControlNumber()->getX(), $labelPoints->getControlNumber()->getY());
	                
	                // draw value
	                $page->setFont($font_b, 12);
	                $page->drawText('$' . $item->fairRetailPrice, $labelPoints->getItemValue()->getX(), $labelPoints->getItemValue()->getY());
	                
	                // Draw Item Name 
	                $page->setFont($font_d, 9);
	                $page->drawText($item->itemName, $labelPoints->getItemName()->getX(), $labelPoints->getItemName()->getY());
	                
	                // Draw Item Desc over multiple lines
	                $page->setFont($font_c, 8);
			        $description = wordwrap(str_replace(array("\r\n", "\n", "\r"), ' ', $item->itemDescription), 67, "\n", false);
			        $line = strtok($description, "\n");
			        $desc_y = $labelPoints->getItemDesc()->getY();
			        $lineCount = 1;
			        while($line !== false){
			            if ($lineCount > 7) break;
			            $page->drawText(iconv('ISO-8859-1', 'ASCII//TRANSLIT', $line), $labelPoints->getItemDesc()->getX(), $desc_y);
			            $desc_y -= 10;
			            $line = strtok("\n");
			            $lineCount++;
			        }
	                
	                // Draw Donor Name over multiple lines
	                $page->setFont($font_d, 9);
			        $donor = wordwrap(($item->donor_companyName!=''?$item->donor_companyName:$item->donor_firstName . ' ' . $item->donor_lastName), 28, "\n", false);
			        $line = strtok($donor, "\n");
			        $desc_y = $labelPoints->getDonor()->getY();
			        $lineCount = 1;
			        while($line !== false){
			            if ($lineCount > 2) break;
			            $page->drawText($line, $labelPoints->getDonor()->getX(), $desc_y);
			            $desc_y -= 13;
			            $line = strtok("\n");
			            $lineCount++;
			        }
	                $page->setFont($font_c, 8);
			        $page->drawText($item->donor_address1, $labelPoints->getDonor()->getX(), $labelPoints->getDonor()->getY() - 25);
			        $page->drawText(substr($item->donor_email, 0, 31), $labelPoints->getDonor()->getX(), $labelPoints->getDonor()->getY() - 35);
			        $page->drawText(substr($item->donor_website, 0, 31), $labelPoints->getDonor()->getX(), $labelPoints->getDonor()->getY() - 45);
	                
	                $items->next();
	                $item = $items->current();
	                $rowCount++;
	             } while ($items->valid() && $currentBlock == $item->blockNumber);
	             
	             $pdf->pages[] = $page;
	        }
			
	        unset($pdf->pages[0]);
	        
	        $savePath = $this->getPdfPath() . DIRECTORY_SEPARATOR . 'bible_' . $this->view->getActiveAuctionDate(1) . '.pdf';
	        
	        $pdf->save($savePath);
	        
	        $this->_redirector->gotoUrl('/pdf/bible_' . $this->view->getActiveAuctionDate(1) . '.pdf');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function noRouteAction()
    {
        $this->_redirect('/');
    }

    private function getBlockArray()
    {
        require_once('models/Block.php');
        $table = new models_Block();
        $where = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
        $blocks  = $table->fetchAll($where, 'number');

        $blockArray = array();
        foreach ($blocks as $block){
            $blockArray[$block->blockId] = $block->number;
        }

        return $blockArray;
    }
    
    private function getPdfPath()
    {
    	return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'pdf';
    }
    
    private function getTemplatePath()
    {
    	return $this->getPdfPath() . DIRECTORY_SEPARATOR . 'templates';
    }
    
    private function getExportPath()
    {
    	return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'exports';
    }
}
