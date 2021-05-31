<?php
function matchmaker_pager($app)
{
    $app->setTitle(lang('matchmaker::matchmaker'));
    $val = input('val');
    if ($val) {
        unset($val['submit']);
        header('location: ' . url_to_pager('matchmaker') . '?' . http_build_query($val));
    }
    $filters = $_GET;
    $encounters = get_encounters($filters);
    return $app->render(view('matchmaker::index', ['encounters' => $encounters]));
}

function likes_pager($app)
{
    $app->setTitle(lang('matchmaker::matchmaker-likes'));
    $likes = matchmaker_get_likes();
    return $app->render(view('matchmaker::likes', ['likes' => $likes]));
}

function matches_pager($app)
{
    $app->setTitle(lang('matchmaker::matchmaker-matches'));
    return $app->render(view('matchmaker::matches', ['matches' => matchmaker_get_matches()]));
}

function like_encounter_pager($app)
{
    $id = (int) segment(2);
    if (like_encounter($id)) {
        $response = ['status' => 1, 'matched' => has_matched($id)];
        $response['user'] = $response['matched'] ? find_user($id) : [];
        if ($response['matched']) {
            $user = $response['user'];
            $gender = (isset($user['gender']) and $user['gender']) ? $user['gender'] : null;
            $gender_image = ($gender) ? img("images/avatar/{$gender}.png") : img("images/avatar.png");
            $response['user']['avatar'] = $user['avatar'] ? url_img($user['avatar'], 200) : $gender_image;
        }
        return json_encode($response);
    }
    $message = lang("matchmaker::error-like");
    if (isset($_SESSION['matchmaker_error'])) {
        $message .= ". " . $_SESSION['matchmaker_error'];
        unset($_SESSION['matchmaker_error']);
    }
    return json_encode(['status' => 0, 'message' => $message]);
}