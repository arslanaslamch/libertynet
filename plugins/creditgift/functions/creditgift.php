<?php
function creditgift_get_balance($user_id = null) {
	$user_id = $user_id ? $user_id : get_userid();
	$db = db();
	$query = $db->query("SELECT `credit_balance`, `credit_spent` FROM users WHERE id = ".$user_id);
	return $query->fetch_assoc();
}

function get_creditgift_balance($user_id) {
	$db = db();
	$query = $db->query("SELECT * FROM users WHERE id='$user_id'");
	return fetch_all($query);
}

function add_creditgift_bonus($user_id, $amount, $type) {
	$db = db();
	$credit = get_creditgift_balance($user_id);
	$balance = isset($credit[0]) ? $credit[0]['credit_balance'] : 0;
	$balance = is_numeric($balance) ? $balance : 0;
	$amount = is_numeric($amount) ? $amount : 0;
	$tbalance = $balance + $amount;
	$time = time();
	$db->query("UPDATE users SET credit_balance='$tbalance' WHERE id='$user_id'");
	$db->query("INSERT into creditgift (user_id,type,amount,date)  VALUES ('$user_id','$type','$amount',$time)");

}

function remove_creditgift_bonus($user_id, $amount, $type) {
	$amount = (int) $amount;
	$db = db();
	$credit = get_creditgift_balance($user_id);
	$balance = $credit[0]['credit_balance'];
	$tbalance = $balance - $amount;
	$time = time();
	$db->query("UPDATE users SET credit_balance='$tbalance' WHERE id='$user_id'");
	$db->query("INSERT into creditgift (user_id,type,amount,date)  VALUES ('$user_id','$type','$amount',$time)");
}

function get_creditgift_transactions($user_id = null, $limit = 10) {
	$user_id = $user_id ? $user_id : get_userid();
	$sql = "SELECT * FROM creditgift";
	if($user_id) {
		$sql .= " WHERE user_id = ".$user_id;
	}
	$sql .= " ORDER BY id DESC ";
	return paginate($sql, $limit);
}

function get_creditgift_transaction($id) {
	$db = db();
	$sql = "SELECT * FROM creditgift WHERE id = ".$id." ORDER BY id DESC ";
	$query = $db->query($sql);
	$transaction = $query->fetch_assoc();
	return $transaction;
}

function creditgift_add_rank($val, $rank_image) {
	$db = db();
	$rank = $val['rank'];
	$credit = $val['credit'];
	$description = $val['description'];
	$original = '';
	if($rank_image) {
		$uploader = new Uploader($rank_image, 'image');
		$uploader->setPath('creditgift/');
		if($uploader->passed()) {
			$original = $uploader->resize($uploader->getWidth(300), null, 'fill', 'any')->result();
		}
	}
	$query = $db->query("INSERT INTO creditgift_rank (rank,credit,description,rank_image) VALUES('$rank','$credit','$description','$original')");
	return $query;
}

function get_creditgift_rank() {
	$db = db();
	$query = $db->query("SELECT * FROM creditgift_rank");
	return fetch_all($query);
}

function get_creditgift_id($id) {
	$db = db();
	$query = $db->query("SELECT * FROM creditgift_rank WHERE id ='$id'");
	return fetch_all($query);
}

function add_creditgift_category($val) {
	$db = db();
	$cat = $val['category'];
	$db->query("INSERT INTO creditgift_category (category) VALUE('$cat')");
}

function get_creditgift_category() {
	$db = db();
	$query = $db->query("SELECT * FROM creditgift_category");
	return fetch_all($query);
}

function get_creditgift_gift() {
	$db = db();
	$query = $db->query("SELECT * FROM creditgift_gifts");
	return fetch_all($query);
}

function update_creditgift($id, $val, $img) {
	$db = db();
	$rank = $val['rank'];
	$credit = $val['credit'];
	$description = $val['description'];
	if($img) {
		$uploader = new Uploader($img, 'image');
		$uploader->setPath(get_userid().'/'.date('Y').'/photos/contest/');
		if($uploader->passed()) {
			$original = $uploader->resize($uploader->getWidth(300), null, 'fill', 'any')->result();
		}
	} else {
		$original = $val['rank_image'];
	}
	$query = $db->query("UPDATE creditgift_rank SET rank='$rank',credit='$credit',description='$description',rank_image='$original' WHERE id =$id ");
}

function delete_rank($id) {
	$db = db();
	$query = $db->query("DELETE FROM creditgift_rank WHERE id='$id' ");
}

/*function creditgift_spend($cost, $credit_reason) {
    $db = db();
    $user_id = get_userid();
    $credit = get_creditgift_balance($user_id);
    $balance = $credit[0]['credit_balance'];
    $spent = $credit[0]['credit_spent'];
    $tspent = $spent + $cost;
    $tbalance = $balance - $cost;
    $time = time();
    $db->query("UPDATE users SET credit_balance = '$tbalance', credit_spent = '$tspent' WHERE id = '$user_id'");
    if($cost>0){
        $db->query("INSERT into creditgift (user_id, type, amount, date)  VALUES ('$user_id', '$credit_reason', '$cost', $time)");
    }
}*/
function creditgift_spend($cost, $credit_reason) {
	if($cost <= 0) {
		return false;
	}
	$db = db();
	$user_id = get_userid();
	$credit = get_creditgift_balance($user_id);
	$balance = $credit[0]['credit_balance'];
	$spent = $credit[0]['credit_spent'];
	$tspent = $spent + $cost;
	$balance = $balance - $cost;
	$time = time();
	$db->query("UPDATE users SET credit_balance = '$balance', credit_spent = '$tspent' WHERE id = '$user_id'");
	$db->query("INSERT INTO creditgift (user_id, type, amount, date) VALUES('$user_id', '$credit_reason', '$cost', $time)");
	return true;
}

function creditgift_richest($limit) {
	$db = db();
	$query = $db->query("SELECT * FROM users ORDER BY credit_balance DESC LIMIT $limit");
	return fetch_all($query);
}

function creditgift_rank_num_members($credit) {
	$db = db();
	$sql = "SELECT credit FROM creditgift_rank WHERE credit < ".$credit." ORDER BY credit DESC LIMIT 1";
	$query = $db->query($sql);
	$result = $query->fetch_assoc();
	$prev = isset($result['credit']) ? $result['credit'] : 0;
	$sql = "SELECT credit FROM creditgift_rank WHERE credit > ".$credit." ORDER BY credit ASC LIMIT 1";
	$query = $db->query($sql);
	$result = $query->fetch_assoc();
	$next = isset($result['credit']) ? $result['credit'] : $credit;
	$sql = "SELECT COUNT(id) FROM users WHERE credit_balance >= ".$prev." AND credit_balance < ".$next;
	$query = $db->query($sql);
	$result = $query->fetch_row();
	$num = $result[0];
	return $num;
}

function creditgift_myrank() {
	$db = db();
	$user_id = get_userid();
	$credit = get_creditgift_balance($user_id);
	$balance = $credit[0]['credit_balance'];
	$r1 = $db->query("SELECT * FROM creditgift_rank WHERE id='1'");
	$i = fetch_all($r1);
	$rnk1 = $i ? $i[0] : 0;
	$rank1 = $rnk1['credit'];
	$r2 = $db->query("SELECT * FROM creditgift_rank WHERE id='2'");
	$i = fetch_all($r2);
	$rnk2 = $i ? $i[0] : 0;
	$rank2 = $rnk2['credit'];
	$r3 = $db->query("SELECT * FROM creditgift_rank WHERE id='3'");
	$i = fetch_all($r3);
	$rnk3 = $i ? $i[0] : 0;
	$rank3 = $rnk3['credit'];
	$r4 = $db->query("SELECT * FROM creditgift_rank WHERE id='4'");
	$i = fetch_all($r4);
	$rnk4 = $i ? $i[0] : 0;
	$rank4 = $rnk4['credit'];
	$r5 = $db->query("SELECT * FROM creditgift_rank WHERE id='5'");
	$i = fetch_all($r5);
	$rnk5 = $i ? $i[0] : 0;
	if($balance <= $rank1) {
		$rank = $rnk1['id'];
	} elseif($balance > $rank1 && $balance <= $rank2) {
		$rank = $rnk2['id'];
	} elseif($balance > $rank2 && $balance <= $rank3) {
		$rank = $rnk3['id'];
	} elseif($balance > $rank3 && $balance <= $rank4) {
		$rank = $rnk4['id'];
	} elseif($balance > $rank4) {
		$rank = $rnk5['id'];
	} else {
	}
	return $rank;
}

function get_creditgift_rankid($id) {
	$db = db();
	$query = $db->query("SELECT * FROM creditgift_rank WHERE id='$id'");
	return fetch_all($query);
}

function creditgift_send($amount, $friend, $credit_reason_1, $credit_reason_2) {
	$db = db();
	$user_id = get_userid();
	$credit = get_creditgift_balance($user_id);
	$balance = $credit[0]['credit_balance'];
	if($balance >= $amount) {
		creditgift_spend($amount, $credit_reason_1);
		$time = time();
		$db->query("INSERT INTO creditgift (user_id, type, amount, date)  VALUES ('$friend', '$credit_reason_2', '$amount', $time)");
		$transaction_id = $db->insert_id;
		$db->query("UPDATE users SET credit_balance = credit_balance + '$amount' WHERE id = '$friend'");
		send_notification($friend, 'credit.send', $transaction_id);
		$status = true;
		$message = lang('creditgift::credit-sent');
	} elseif($amount < $balance OR $amount != $balance) {
		$status = false;
		$message = lang('creditgift::credit-not-enough');
	} else {
		$status = false;
		$message = lang('gift::gift-not-sent');
	}
	return array('status' => $status, 'message' => $message);
}

function get_creditgift_user() {
	return paginate("SELECT * FROM users", 10);
}

function creditgift_sendToAll($sendTo_all) {
	$db = db();
	$query = $db->query("UPDATE users SET credit_balance=credit_balance+$sendTo_all");
	return $query;
}

function creditgift_sendToOne($sendTo_one, $reciever_id) {
	$db = db();
	$query = $db->query("UPDATE users SET credit_balance=credit_balance+$sendTo_one WHERE id='$reciever_id'");
	return $query;
}

function get_creditgift_plan($limit = 10) {
	return paginate("SELECT * FROM creditgift_plan", $limit);
}

function get_creditgift_planId($id) {
	$db = db();
	$query = $db->query("SELECT * FROM creditgift_plan WHERE id='$id'");
	return $query->fetch_assoc();
}

function update_creditgift_plan($id, $val) {
	$db = db();
	$unit = $val['unit'];
	$description = $val['description'];
	$cost = $val['cost'];
	$query = $db->query("UPDATE creditgift_plan SET unit=$unit,description='$description',cost='$cost' WHERE id='$id'");
	return $query;
}

function delete_plan($id) {
	$db = db();
	$db->query("DELETE FROM creditgift_plan WHERE id='$id' ");
}

function add_creditgift_plan($val) {
	$db = db();
	$unit = $val['unit'];
	$description = $val['description'];
	$cost = $val['cost'];
	$query = $db->query("INSERT INTO creditgift_plan (unit,description,cost) VALUES('$unit','$description','$cost')");
	return $query;
}

function activate_creditgift($plan, $user_id = null) {
	$db = db();
	$user_id = $user_id ? $user_id : get_userid();
	$query = $db->query("UPDATE users SET credit_balance = credit_balance + ".$plan['unit']." WHERE id = ".$user_id);
	if($query) {
		$query = $db->query("INSERT into creditgift (user_id, type, amount, date)  VALUES (".$user_id.", '".$plan['description']."', ".$plan['unit'].", ".time().")");
	}
	return $query ? true : false;
}

function get_creditgift_sales() {
	return paginate("SELECT * FROM creditgift_sales", 10);
}