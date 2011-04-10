<?php

$objectDb = Admin_Core::getObjectDatabase();

$objectConfig = Admin_Core::getObjectGlobalConfig();

$comment_id = @$_POST['comment_id'];
$objectDb->setId($comment_id);
$item_id = $objectDb->getItem('item_id','comments');

// обновляем поле в items
$objectDb->query('UPDATE ?_items SET comments_count=comments_count-1 WHERE id = ?',$item_id);

// удаляем комментарий
$objectDb->deleteRecord('comments');

// пересчет score
$score_param=$objectDb->selectRow('
	SELECT SUM(value) sum, COUNT(value) count FROM ?_comments comments,
		?_votes votes WHERE comments.id=votes.id AND `date`>DATE_SUB(NOW(), INTERVAL ? MONTH) AND item_id = ?d',
        $objectConfig->getConfigSection('OTHER','vote_life'),$item_id);

if ($score_param['count']==0) {
    $score = 0;
} else {
    $score = $score_param['sum']/$score_param['count'];
}

$objectDb->query('UPDATE ?_biz SET score=? WHERE id = ?d',$score,$item_id);

// удаляем vote
$objectDb->deleteRecord('votes');

?>