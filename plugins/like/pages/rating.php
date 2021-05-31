<?php
function add_rating_pager() {
    $rate = input('rate');
    $type = input('type');
    $type_id = input('type_id');
    $result = array('status' => 0, 'content' => '', 'rate' => '', 'message' => lang('error'));
    if ($rate && $type && $type_id) {
        $add = add_rating($rate, $type, $type_id);
        if ($add) {
            $result['status'] = 1;
            $result['content'] = view('like::rate/display', array('type' => $type, 'type_id' => $type_id));
            $result['rate'] = view('like::rate/rate', array('type' => $type, 'type_id' => $type_id));
            $result['message'] = lang('successful');
        }
    }
    return json_encode($result);
}