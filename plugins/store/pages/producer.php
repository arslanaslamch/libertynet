<?php

function producer_index_pager($app){
    $app->setTitle(lang('store::store'));
    $producers = get_store_producer();
    return $app->render(view('store::producer/home',array('producers'=>$producers)));
}

function producer_create_pager($app){
    $app->setTitle(lang('store::store'));
    $message = '';
    $val = input('val');
    if($val){
        CSRFProtection::validate();
        $rules = array(
            'name'=>'required'
        );
        $validator = validator($val,$rules);
        if(validation_passes()){
            $result = add_store_producer($val);

            if($result){
                return redirect_to_pager("producer-home");

            }
        }else{
            $message = validation_first();
            return $app->render(view('store::producer/add',array('message'=>$message)));
        }

    }
    return $app->render(view('store::producer/add',array('message'=>$message)));
}

function producer_edit_pager($app){
    $app->setTitle(lang('store::store'));
    $message = '';
    $pid = (int)input('pid');
    $producer = get_store_producer_by_id($pid);
    if(empty($producer)){
        return redirect_to_pager("producer-home");
    }
    $val = input('val');
    if($val){
        CSRFProtection::validate();
        $rules = array(
            'name'=>'required'
        );
        $validator = validator($val,$rules);
        if(validation_passes()){
            $val['pid'] = $pid;
            $result = save_store_producer($val);

            if($result){
                return redirect_to_pager("producer-home");

            }
        }else{
            $message = validation_first();
            return $app->render(view('store::producer/edit',array('message'=>$message)));
        }

    }

    return $app->render(view('store::producer/edit',array('message'=>$message,'producer'=>$producer[0])));
}

function producer_remove_pager(){
    $id = input("id");
    return removeStoreProducer($id);
}