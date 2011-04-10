<?php

class Admin_Model_Posthandler_Common extends Admin_Db_Abstract {
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct() {
		
		$this->setDefaultTable(Admin_Core::DEFAULT_TABLE);
        $this->setId(Admin_Core::getItemId());
		
	}
	
	/**
	 * Перевод комментариев на "гостя" при удалении пользователя
	 *
	 */
	public function posthandler_update_comments () {
	    
	    $this->updateField('comments','user_id',0,array('user_id'=>$this->id));
	    
	}
	
	/**
	 * Обновление даты изменения элемента при редактировании его контента
	 *
	 */
	public function posthandler_update_mod_date () {
	    
	    $this->updateField(false,'mod_date',date('Y-m-d H:i:s'));
	    
	}
	
	/**
	 * Удаление из всех связанных таблиц
	 *
	 */
	public function posthandler_delete_by_link() {
		
		$this->deleteMulti(array('content_id'=>$this->getId()),'comments');
		
		$this->deleteMulti(array('biz_id'=>$this->getId()),'biz_sell');
		$this->deleteMulti(array('biz_id'=>$this->getId()),'biz_services');
		
		$this->deleteMulti(array('container_id'=>$this->getId()),'for_all_smack');
		$this->deleteMulti(array('article_id'=>$this->getId()),'for_all_smack');
		
		$this->deleteMulti(array('biz_id'=>$this->getId()),'brand_biz');
		$this->deleteMulti(array('container_id'=>$this->getId()),'brand_biz');
		
		$this->deleteMulti(array('biz_id'=>$this->getId()),'rec_biz');
		$this->deleteMulti(array('container_id'=>$this->getId()),'rec_biz');
		
		$this->deleteMulti(array('news_id'=>$this->getId()),'rec_prices');
		$this->deleteMulti(array('container_id'=>$this->getId()),'rec_prices');
		
		$this->deleteMulti(array('item_id'=>$this->getId()),'phones');
		
		$this->deleteMulti(array('source_id'=>$this->getId()),'linked_services');
		$this->deleteMulti(array('linked_id'=>$this->getId()),'linked_services');
		
		$this->deleteMulti(array('source_id'=>$this->getId()),'linked_biz');
		$this->deleteMulti(array('linked_id'=>$this->getId()),'linked_biz');
		
	}
	
	public function posthandler_updateClothersPrice() {
		
		$price = $this->getItem('price','goods');
		$priceOld = $this->getItem('price_old','goods');
		
		// обновление цены у тех экземпляров одежды, у которых цена не задана
		$this->query('
			UPDATE goods g
			JOIN items i ON i.id = g.id
			SET price = ?
			WHERE pid  = ?d',$price,$this->getItem('pid'));
		
		$this->query('
			UPDATE goods g
			JOIN items i ON i.id = g.id
			SET price_old = ?
			WHERE pid  = ?d',$priceOld,$this->getItem('pid'));
		
	}
	
}
