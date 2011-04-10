<?php

error_reporting(E_ALL &~E_STRICT);
require_once("../libs/phprtflite/rtf/Rtf.php"); 

function generateRtf($data) {
	
	//Fonts
	$fontDefault = new Font(10,'Arial');
	$fontBold = new Font(10, 'Arial'); $fontBold->setBold();
	$fontHead = new Font(12, 'Arial'); $fontHead->setBold();
	$fontBig = new Font(12, 'Arial');
	$fontLink = new Font(10, 'Helvetica', '#0000cc');
	
	$parBlack = new ParFormat('left'); 
	
	$parHead = new ParFormat(); 
	$parHead->setSpaceBefore(3); 
	$parHead->setSpaceAfter(8); 
	
	$parSimple = new ParFormat(); 
	$parSimple->setIndentLeft(5); 
	$parSimple->setIndentRight(0.5); 
	
	$parPhp = new ParFormat(); 
	$parPhp->setShading(5); 
	$parPhp->setBorders(new BorderFormat(1, '#000000', 'dash', 0.3)); 
	$parPhp->setIndentLeft(5); 
	$parPhp->setIndentRight(0.5); 
	
	////////////// 
	//Rtf document 
	$rtf = new Rtf();
	//$rtf->setLandscape();
	$rtf->setMargins(1,1,1,1);
	$sect = $rtf->addSection();
	
	// table in head
	$table = $sect->addTable(); 
	$table->addRows(4); 
	$table->addColumn(5); $table->addColumn(10);
	
	$table->getCell(1,1)->addImage('../plugins/utils/logo.jpg',new ParFormat());
	$table->getCell(1,1)->writeText('email: babysmile@ua.fm',$fontDefault,new ParFormat('left'));
	$table->getCell(1,1)->writeHyperLink('http://babysmile.dp.ua','Интернет-магазин Babysmile',$fontLink,new ParFormat('left'));
	$table->getCell(1,1)->writeHyperLink('http://babysmile.dp.ua','www.babysmile.dp.ua',$fontLink,new ParFormat('left'));
	$table->getCell(1,2)->writeText('ЧП Грабовский А.А.<br/>тел.788-23-83, моб.0675618252<br/>К /с 4405885013592339 в КБ "Приватбанк"<br/>Не является плательщиком налога на прибыль на общих основаниях<br/>Адрес: Украина, г.Днепропетровск, ул. Березинская, 28/44<br/>',$fontDefault,new ParFormat());
	$table->getCell(2,1)->writeText('Получатель',$fontBold,$parBlack);
	$table->getCell(2,2)->writeText($data['user'].'<br/>',$fontDefault,new ParFormat('left'));
	$table->getCell(3,1)->writeText('Плательщик',$fontBold,$parBlack);
	$table->getCell(3,2)->writeText('он же<br/>',$fontDefault,new ParFormat('left'));
	$table->getCell(4,1)->writeText('Заказ',$fontBold,$parBlack);
	$table->getCell(4,2)->writeText('согласно усному договору<br/>',$fontDefault,new ParFormat('left'));
	
	// add head
	$sect->writeText('Счет-фактура от '.$data['account'],$fontHead,new ParFormat('center'));
	
	// main table
	$positionsCount = count($data['positions']);
	$table = $sect->addTable(); 
	$table->addRows($positionsCount+5);
	$table->addColumn(1); $table->addColumn(9); $table->addColumn(0.6); $table->addColumn(2.2); $table->addColumn(2.8); $table->addColumn(2.8);
	// borders
	$border = new BorderFormat(1,'#000000');
	$table->setBordersOfCells($border,1,1,$positionsCount+1,6);
	$table->setBordersOfCells($border,$positionsCount+1,6,$positionsCount+5,6);
	// background
	for ($col=1;$col<=6;$col++) {
		$table->getCell(1,$col)->setBackGround('#cccccc');
	}
	// write text
	$table->getCell(1,1)->writeText('№',$fontBold,new ParFormat('center'));
	$table->getCell(1,2)->writeText('Наименование',$fontBold,new ParFormat('center'));
	$table->getCell(1,3)->writeText('Ед.',$fontBold,new ParFormat('center'));
	$table->getCell(1,4)->writeText('Количество',$fontBold,new ParFormat('center'));
	$table->getCell(1,5)->writeText('Цена без НДС',$fontBold,new ParFormat('center'));
	$table->getCell(1,6)->writeText('Сумма без НДС',$fontBold,new ParFormat('center'));
	foreach ($data['positions'] as $row=>$v) {
		$table->getCell($row+2,1)->writeText($row+1,$fontDefault,new ParFormat('center'));
		$table->getCell($row+2,2)->writeText($v['name'],$fontDefault,new ParFormat('left'));
		$table->getCell($row+2,3)->writeText('шт',$fontDefault,new ParFormat('center'));
		$table->getCell($row+2,4)->writeText($v['count'],$fontDefault,new ParFormat('right'));
		$table->getCell($row+2,5)->writeText($v['price']-0.2*$v['price'],$fontDefault,new ParFormat('right'));
		$table->getCell($row+2,6)->writeText($v['count']*$v['price']-0.2*$v['count']*$v['price'],$fontDefault,new ParFormat('right'));
	}
	
	$table->getCell($positionsCount+2,5)->writeText('Скидка',$fontBold,new ParFormat('right'));
	$table->getCell($positionsCount+3,5)->writeText('Итого без НДС',$fontBold,new ParFormat('right'));
	$table->getCell($positionsCount+4,5)->writeText('НДС',$fontBold,new ParFormat('right'));
	$table->getCell($positionsCount+5,5)->writeText('Всего',$fontBold,new ParFormat('right'));
	$table->getCell($positionsCount+2,6)->writeText($data['discount'],$fontBold,new ParFormat('right'));
	$table->getCell($positionsCount+3,6)->writeText($data['sum']-0.2*$data['sum'],$fontBold,new ParFormat('right'));
	$table->getCell($positionsCount+4,6)->writeText(0.2*$data['sum'],$fontBold,new ParFormat('right'));
	$table->getCell($positionsCount+5,6)->writeText($data['sum'],$fontBold,new ParFormat('right'));
	
	// bottom
	$sect->writeText('Всего на сумму:',$fontBig,new ParFormat('left'));
	$sect->writeText($data['in_words'].'<br/><br/>',$fontHead,new ParFormat('left'));
	
	$sect->writeText('Выписал(а): _______________________<br/>',$fontDefault,new ParFormat('right'));
	$sect->writeText('Грабовский Андрей Александрович',$fontDefault,new ParFormat('right'));
	$sect->writeText('Счет действителен к оплате до '.$data['valid_date'],$fontBold,new ParFormat('right'));
	
	return $rtf;
	 
}
