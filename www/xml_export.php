<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once '../libs/DbSimple/Generic.php';
$db = DbSimple_Generic::connect('mysql://u_babysmile:mWQbGNMu@localhost/babysmile');
$db->query('SET NAMES CP1251');

class Shop extends DOMDocument {
	
	/**
	 * Shop DOM element
	 *
	 * @var DOMElement
	 */
	private $shop;
	
	/**
	 * Database object
	 *
	 * @var DbSimple_MySql
	 */
	private $db;
	
	public function __construct($db) {
		
		$this->db = $db;
		
		parent::__construct('1.0','windows-1251');
		$imp = new DOMImplementation();
		$dtd = $imp->createDocumentType('yml_catalog','','shops.dtd');
		$this->appendChild($dtd);
		
		$root = new DOMElement('yml_catalog');
		$this->appendChild($root);
		$root->setAttribute('date',date('Y-m-d H:i'));
		
		$this->add_shop($root);
		$this->create_currencies();
		$this->create_categories();
		$this->create_offers();
		
	}
	
	private function add_shop($root) {
		
		$this->shop = new DOMElement('shop');
		$root->appendChild($this->shop);
		
		$name = new DOMElement('name');
		$this->shop->appendChild($name);
		$name->appendChild($this->createTextNode('Baby Smile'));
		
		$company = new DOMElement('company');
		$this->shop->appendChild($company);
		$company->appendChild($this->createTextNode('Children goods'));		
		
		$url = new DOMElement('url');
		$this->shop->appendChild($url);
		$url->appendChild($this->createTextNode('http://babysmile.dp.ua'));
		
	}
	
	private function create_currencies() {
		
		$currencies = $this->createElement('currencies');
		$this->shop->appendChild($currencies);
		
		$currency = $this->createElement('currency');
		$currencies->appendChild($currency);
		$currency->setAttribute('id','UAH');
		
	}
	
	private function create_categories() {
		
		$categories = $this->createElement('categories');
		$this->shop->appendChild($categories);
		
		// корневые категории товаров
		$data_category = $this->db->select('SELECT name,id FROM items WHERE template = ? AND protected=0 ORDER BY sort','category');
		foreach ($data_category as $item) {
			$category = $this->createElement('category');
			$categories->appendChild($category);
			$category->setAttribute('id',$item['id']);
			$category->appendChild($this->createTextNode(iconv('WINDOWS-1251','UTF-8',$item['name'])));
		}
		
		// категории товаров
		$data_category =
			$this->db->select('SELECT name,id,pid FROM items WHERE (template = ? OR type = ?) AND protected=0 ORDER BY sort, pid, type ','subcategory','good_set');
		foreach ($data_category as $item) {
			$category = $this->createElement('category');
			$categories->appendChild($category);
			$category->setAttribute('id',$item['id']);
			$category->setAttribute('parentId',$item['pid']);
			$category->appendChild($this->createTextNode(iconv('WINDOWS-1251','UTF-8',$item['name'])));
		}
		
	}
	
	private function create_offers() {
		
		$offers = $this->createElement('offers');
		$this->shop->appendChild($offers);
		
		// товары
		$goods = $this->db->select('
		      SELECT i.id,i.pid,i.name,i.url,cat.name AS cat_name,img.filename,i.description,c.words,g.price,i_biz.name AS biz_name
		      FROM items i
		      JOIN items cat ON cat.id = i.pid
			  JOIN goods g ON g.id = i.id
			  LEFT JOIN biz b ON b.id = g.biz_id
			  LEFT JOIN items i_biz ON i_biz.id = b.id
			  LEFT JOIN top_images img ON img.id = i.id
              LEFT JOIN content c ON i.id = c.id
			  WHERE i.type = ? AND i.protected=0 AND g.price>0 ORDER BY i.pid,i.sort','good');
		
		foreach ($goods as $item) {
			
			$offer = $this->createElement('offer');
			$offers->appendChild($offer);
			$offer->setAttribute('id',$item['id']);
			$offer->setAttribute('type','vendor.model');
			$offer->setAttribute('available',"true");
			
			$url = $this->createElement('url');
			$offer->appendChild($url);
			$url->appendChild($this->createTextNode('http://babysmile.dp.ua'.$item['url']));
			
			$price = $this->createElement('price');
			$offer->appendChild($price);
			$price->appendChild($this->createTextNode($item['price']));
			
			$currency = $this->createElement('currencyId');
			$offer->appendChild($currency);
			$currency->appendChild($this->createTextNode('UAH'));
			
			$category = $this->createElement('categoryId');
			$offer->appendChild($category);
			$category->appendChild($this->createTextNode($item['pid']));
			
			$picture = $this->createElement('picture');
			$offer->appendChild($picture);
			$picture->appendChild($this->createTextNode('http://babysmile.dp.ua/i/top/'.$item['id'].'/'.$item['filename']));
			
			$type = $this->createElement('typePrefix');
			$offer->appendChild($type);
			$type->appendChild($this->createTextNode(iconv('WINDOWS-1251','UTF-8',$item['cat_name'])));
			
			$vendor = $this->createElement('vendor');
			$offer->appendChild($vendor);
			$vendor->appendChild($this->createTextNode(iconv('WINDOWS-1251','UTF-8',$item['biz_name'])));
			
			$model = $this->createElement('model');
			$offer->appendChild($model);
			$model->appendChild($this->createTextNode(iconv('WINDOWS-1251','UTF-8',$item['name'])));
			
			$description = $this->createElement('description');
			$offer->appendChild($description);
			$description->appendChild($this->createTextNode(iconv('WINDOWS-1251','UTF-8',$item['words'])));
			
//			$category->appendChild($this->createTextNode(iconv('WINDOWS-1251','UTF-8',$item['name'])));
		}
		
	}
	
	/**
	 * @return DOMElement
	 */
	public function getShop() {
		return $this->shop;
	}
	
	public function output() {
		header("Content-type: application/xml; charset=windows-1251");
		echo $this->saveXML();
	}
	
}

$shop = new Shop($db);
$shop->output();

?>