<?php

function tvPayWithWallet($tv)
{
    if (function_exists('emoney_credit_user')) {
        $action = lang("onlinetv::pay-tv") . ' : ' . $tv['name'];

        ///check the current balance of me
        $bc = getEmoneyBalance();
        //course owner
        $amount = (float)$tv['price'];
        if ($bc > $amount) {
            //credit the owner what is left after commission
            //$co = str_replace('%', '', config('commission-on-course-purchase', 5));
            //$ba = formatEmoney((1 - ($co / 100)) * $amount);
            $ba = $amount;//added balance
            $owner = $tv['user_id'];
            emoney_credit_user('credit', $action, $ba, $owner);

            //reduce buyeer money
            $buyer = get_userid();
            emoney_credit_user('debit', $action, $amount, $buyer);

            //add new member
            $id = $tv['id'];
            $time = time();
            db()->query("INSERT INTO onlinetv_paid(user_id,tv_id,time) VALUE('{$buyer}','{$id}','{$time}')");
            return redirect(url('onlinetv/' . $tv['slug']));
        } else {
            //let go and add fund
            return redirect(url_to_pager("emoney-add-fund"));
        }
    }
    return false;
}

function tvPricing($course)
{
    if ($course['price']) {
        return '<i>' . lang("onlinetv::price") . '</i>' . " : <span class='text-danger'>" . $course['price'] . ' ' . config('default-currency', '$') . "</span>";
    } else {
        return '<i>' . lang("onlinetv::price") . '</i>' . " : <span class='text-success'>" . lang("course::free") . "</span>";
    }
}

function has_paid_for_tv($tvid, $uid = null)
{
    $uid = ($uid) ? $uid : get_userid();
    $q = db()->query("SELECT * FROM onlinetv_paid WHERE user_id='{$uid}' AND tv_id='{$tvid}'");
    if ($q->num_rows > 0) return true;
    return false;
}

function can_not_watch_tv($onlinetv)
{

    //if tv is free, just everybody can watch
    if ($onlinetv['price'] == 0) return false;
    //if you the owner, you don't pay
    if (is_onlinetv_owner($onlinetv)) return false;

    //you get here because you are not the onwer and tv is not free
    //check if the user have paid
    if (has_paid_for_tv($onlinetv['id'])) {
        return false;
    }

    //he has not paid
    return true;

}

function countTvCategoryUse($id)
{
    $q = db()->query("SELECT * FROM onlinetvs WHERE category_id='{$id}'");
    return $q->num_rows;
}

function importTv()
{
    $arr = array(
        array(
            'Blitzed',
            "Wayne's World meets SportsNation, BLITZED is an NFL show like no other. Set in a cozy neighborhood bar, it busts out of the traditional news desk format with it's",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=5702&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '5',
            img('onlinetv::images/import/1.png'),
        ), array(
            'FilmOn Boxing',
            "FilmOn Boxing brings you hours of full classic fights of some of the greatest British Boxing talent including Joe Calzaghe, Chris Eubank, Ricky Hatton and Prince",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3143&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '5',
            img('onlinetv::images/import/2.png'),
        ),
        array(
            'AFL Classic',
            "Classic AFL Games from the vaults.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=980&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '5',
            img('onlinetv::images/import/3.png')
        ), array(
            'FilmOn Football',
            "The only global football channel to feature the very best of the English Premier League, The FA cup and The England Football team, from the Greatest games, best...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=374&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '5',
            img('onlinetv::images/import/4.png')
        ), array(
            'Zombie Underworld',
            'Join the undead with these classic Zombie films.',
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3071&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '6',
            img('onlinetv::images/import/5.png')
        ), array(
            'Alien (invasion) channel',
            'A collection of infamous Sci-fi must sees.',
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3122&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '6',
            img('onlinetv::images/import/6.png')
        ), array(
            'California Life',
            'California Life with Heather Dawson, a syndicated lifestyle news magazine show that airs across the entire state of California, focusing on the Best of California!...',
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3416&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '7',
            img('onlinetv::images/import/7.png')
        ), array(
            'On The Mike',
            "On The Mike' With Mike Sherman takes you deep inside today's pop culture, along with the hottest music artists, movie premiers and red carpet VIP events from the...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=5810&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '7',
            img('onlinetv::images/import/8.png')
        ), array(
            'MVTV',
            'MVTV is Malta´s digital music television channel airing non-stop video clips and related material by Maltese artists, singers and bands. Broadcasting non-stop music...',
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=5744&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '8',
            img('onlinetv::images/import/9.png')
        ), array(
            'TalentWatch',
            'TalentWatch showcases music videos of exciting music artists from around the world. Hosted by Alyssa Jacey, an international singer-songwriter and recording artist...',
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4706&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '8',
            img('onlinetv::images/import/10.png')
        ), array(
            'HotRock TV',
            'HotRock TV features musicians with the Hottest Country Music on the Planet along with interviews from the biggest artists in the Country Music arena.',
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4667&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '8',
            img('onlinetv::images/import/11.png')
        ), array(
            'FilmOn Comedy Classics',
            "If it's funny and if it's classic, you find find it here!",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4295&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '9',
            img('onlinetv::images/import/12.png')
        ), array(
            'Fun Little Movies',
            "Fun Little Movies’ strongest suit is entertainment. With a library of over 1600 licensed short films and series, FLM has an abundance of comedic content for all...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=1727&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '9',
            img('onlinetv::images/import/13.png')
        ), array(
            'Lumbfilm Comedy',
            "A Comedy channel featuring short films, sketches, impressions and much much more from award winning UK based Lumbfilm Productions.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3851&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '9',
            img('onlinetv::images/import/14.png')
        ), array(
            'comiCZoo',
            "Cool cult comedy from Leomark Studios.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3368&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '9',
            img('onlinetv::images/import/15.png')
        ), array(
            'Kids Zone',
            "Kidszone offers a visual delight for your little ones to get entertained and also learn their basics. Kids Zone is a one stop shop for kids edutainment with nursery...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3794&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '10',
            img('onlinetv::images/import/16.png')
        ), array(
            'Felix The Cat',
            "Originally created as a silent film star, Felix the Cat became the beloved star of many cartoons both in print and on TV. He's a wonderful, wonderful cat!",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=5546&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '10',
            img('onlinetv::images/import/17.png')
        ), array(
            'Little Smart Planet',
            "Little Smart Planet is an amazingly fun and educational learning platform designed to fuel children's curiosity for knowledge. It is a pioneer in the development...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=5726&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '10',
            img('onlinetv::images/import/18.png')
        ), array(
            'Super Geek Heroes',
            "The SUPER GEEK HEROES are a unique group of super-kids... having fun in turn with a mission to learn! Their seven friendly 'super-powers' are derived from the three...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=5507&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '10',
            img('onlinetv::images/import/19.png')
        ), array(
            'Clutch Cargo',
            "Animated TV show featuring Clutch Cargo on his many adventures. Creative use of Syncro-Vox for the 'moving lips' of the characters.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=5540&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '10',
            img('onlinetv::images/import/20.png')
        ), array(
            'Children Reading Channel',
            "We nurture early literacy by bringing books to life!",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4160&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '10',
            img('onlinetv::images/import/21.png')
        ), array(
            'FilmOn Kids',
            "The best children's programming around can be found on here: Dragonfly TV, Missing, Swap TV, Dog Tales, Animal Rescue, The Real Winning Edge and Think Big. Fun for...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=7&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '10',
            img('onlinetv::images/import/22.png')
        ), array(
            'Victory at Sea TV',
            "This Emmy-winning TV series from 1952-1953 depicts warfare from WWII and helped promote the popularity of compilation documentaries on television.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4652&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '11',
            img('onlinetv::images/import/23.png')
        ), array(
            'FBI Insider',
            "Go inside the FBI and see the training videos and documentaries about this US institution plus video programs from police agencies, the US Treasury and the military...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4166&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '11',
            img('onlinetv::images/import/24.png')
        ), array(
            'Living History Channel',
            "LIVING HISTORY is history itself, unfiltered, unexplained , raw, many times confusing, exuberant, and devastating as it unfolded in front of the eyes of the film...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=2710&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '11',
            img('onlinetv::images/import/25.png')
        ), array(
            'Aliens and UFOs',
            "Aliens and UFOs - The Close Encounters Channel features top authorities on the UFO enigma. Pilots, Astronauts, Government Officials, Military Officials, Medical...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3548&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '11',
            img('onlinetv::images/import/26.png')
        ), array(
            'EIC TV',
            "The EIC TV Network (www.EICnetwork.TV) is the Internet television network of the Entertainment Industries Council (EIC). EIC is a non-profit organization founded...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4676&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '12',
            img('onlinetv::images/import/27.png')
        ), array(
            'NEWSMAX',
            "Newsmax TV provides independent news and is the first truly “informational” news channel, offering not only the latest in politics and current events but also practical...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3223&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '12',
            img('onlinetv::images/import/28.png')
        ), array(
            'Press TV',
            "Iran's television network, broadcasting in English round-the-clock. Based in Tehran.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=905&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '12',
            img('onlinetv::images/import/29.png')
        ), array(
            'Adom TV',
            '',
            '<iframe width="560" height="315" src="https://www.youtube.com/embed/Uql5zh-f58U" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
            '12',
            '',
        ), array(
            'Insider Exclusive TV',
            "The Insider Exclusive is dedicated to making critically acclaimed Legal Issue Documentaries to publicize stories often overlooked in mainstream media.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3521&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '12',
            img('onlinetv::images/import/30.png'),
        ), array(
            'Dr. Fab Show',
            "Do you want to look and feel fab, inside and out? Then ask Dr. Fab for you and your family... Dr Fab is the UK & Ireland's NEW lifestyle chat show hosted by a team...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4100&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '13',
            img('onlinetv::images/import/31.png'),
        ), array(
            'Health and Lifestyle',
            "Health & Lifestyles Weekly is a hour hour co-hosted magazine show that inspires and motivates you to be healthy and fit in a fun way. Every show covers optimal nutrition...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4262&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '2',
            img('onlinetv::images/import/32.png'),
        ), array(
            'Medical News Minute',
            "Medical News Minute is a network channel that brings viewers the latest in health. Trusted professionals provide the latest fact-supported medical news and information...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3220&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '2',
            img('onlinetv::images/import/33.png'),
        ), array(
            'Faith Cinema',
            "Inspirational films culled from the FilmOn movie vault.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4655&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '14',
            img('onlinetv::images/import/34.png'),
        ), array(
            'West Coast Praze',
            "West Coast Praze features music from Gospel's hottest artists along with clean family comedy and news.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=2891&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '14',
            img('onlinetv::images/import/35.png'),
        ), array(
            'Smarts TV',
            "Educate yourself on something new on a variety of topics, with new shows added often.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=4178&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '15',
            img('onlinetv::images/import/36.png'),
        ), array(
            'StarShop',
            "Starshop (Celebrity TV) - ap into Celebrity! StarShop is an entertainment powerhouse that gives mobile shoppers VIP access to celebrities and their favorite products...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3575&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '3',
            img('onlinetv::images/import/37.png'),
        ), array(
            'AutoTV',
            "AutoTV Channel offers a diverse range of over 100 hours of motoring content, including biographies about the world’s greats of motor sport; documentaries on auto...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=2945&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '4',
            img('onlinetv::images/import/38.png'),
        ), array(
            'Nollywood',
            "Nollywood features the best in Nigerian movies, music videos and entertainment content.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3617&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '1',
            img('onlinetv::images/import/39.png'),
        ), array(
            'Igboro TV',
            "Igboro TV is a Nigerian movie channel bringing you the best of Nollywood. Our 24-hour linear channel show cases the best of Nollywood movies and more. Igboro TV...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3449&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '1',
            img('onlinetv::images/import/40.png'),
        ), array(
            'Films Of India',
            "There is one religion that binds India and that is BOLLYWOOD. We at Films Of India believe that the Worshipers of this religion are actually the Gods who make or...",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3791&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '1',
            img('onlinetv::images/import/41.png'),
        ), array(
            'Live9 TV',
            "From paparazzi to fashion weeks to movie launch, Live9tv gives you your daily dose of gossip and information on the page 3 circuit.",
            '<iframe width="640" height="480" src="https://www.filmon.com/tv/channel/export?channel_id=3797&autoPlay=1" frameborder="0" allowfullscreen> </iframe>',
            '1',
            img('onlinetv::images/import/42.png'),
        ),
    );
    $entity_id = get_userid();
    $userid = get_userid();
    $entity_type = 'user';
    $privacy = 1;
    $price = 0;
    $source = 'embed';
    $source_url = "";
    $country = "";
    $time = time();
    $privacy = 1;


    foreach ($arr as $a) {
        $name = $a[0];
        $description = $a[1];
        $description = mysqli_real_escape_string(db(), htmlentities($description));
        $source_embed = $a[2];
        $source_embed = mysqli_real_escape_string(db(), htmlentities($source_embed));
        $category = $a[3];
        $image = $a[4];
        //$slug = unique_slugger($name);
        $slug = slugger($name);
        db()->query("INSERT INTO onlinetvs (source,source_url,source_embed,price,country,user_id, entity_type, entity_id, `name`, slug, description, image, 
update_time, time, category_id, privacy, import) VALUES ('" . $source . "','" . $source_url . "','" . $source_embed . "','" . $price . "','" . $country . "','" . $userid . "', '" . $entity_type . "', '" . $entity_id . "', 
'" . $name . "', '" . $slug . "', '" . $description . "', '" . $image . "', '" . $time . "', '" . $time . "', 
'" . $category . "', '" . $privacy . "','1')");
    }

}

function deleteTvsImported()
{
    db()->query("DELETE FROM onlinetvs WHERE import='1'");
    return true;
}

function add_onlinetv($val)
{
    $expected = array(
        'name' => '',
        'description' => '',
        'category' => '',
        'entity' => '',
        'privacy' => '',
        'price' => 0,
        'country' => 'all',
        'source' => '',
        'source_embed' => '',
        'source_url' => ''
    );
    /**
     * @var $name
     * @var $description
     * @var $category
     * @var $entity
     * @var $featured
     * @var $privacy
     * @var $price
     * @var $country
     * @var $source
     * @var $source_embed
     * @var $source_url
     *
     */
    extract(array_merge($expected, $val));

    $image = '';
    $file = input_file('image');
    if ($file) {
        $uploader = new Uploader($file);
        if ($uploader->passed()) {
            $uploader->setPath('onlinetvs/preview/');
            $image = $uploader->resize(700, 500)->result();
        }
    }

    $time = time();
    $userid = get_userid();
    //$slug = unique_slugger($name);
    $slug = slugger($name);
    $entity = explode('-', $entity);
    if (count($entity) == 2) {
        $entity_type = $entity[0];
        $entity_id = $entity[1];
    }
    if (!isset($entity_type) || !isset($entity_type)) {
        return false;
    }
    $source_url = urlencode($source_url);
    $source_embed = mysqli_real_escape_string(db(), htmlentities($source_embed));
    db()->query("INSERT INTO onlinetvs (source,source_url,source_embed,price,country,user_id, entity_type, entity_id, `name`, slug, description, image, 
update_time, time, category_id, privacy) VALUES ('" . $source . "','" . $source_url . "','" . $source_embed . "','" . $price . "','" . $country . "','" . $userid . "', '" . $entity_type . "', '" . $entity_id . "', 
'" . $name . "', '" . $slug . "', '" . $description . "', '" . $image . "', '" . $time . "', '" . $time . "', 
'" . $category . "', '" . $privacy . "')");
    $onlinetvId = db()->insert_id;
    $onlinetv = get_onlinetv($onlinetvId);
    fire_hook("onlinetv.added", null, array($onlinetvId, $onlinetv));
    fire_hook('plugins.users.category.updater', null, array('onlinetv', $val, $onlinetvId, 'id'));
    return $onlinetvId;
}

function save_onlinetv($val, $onlinetv, $admin = false)
{
    $expected = array(
        'name' => '',
        'description' => '',
        'category' => '',
        'entity' => '',
        'privacy' => '',
        'price' => 0,
        'country' => 'all',
        'source' => '',
        'source_embed' => '',
        'source_url' => '',
        'featured' => $onlinetv['featured']
    );
    /**
     * @var $name
     * @var $description
     * @var $category
     * @var $entity
     * @var $featured
     * @var $privacy
     * @var $price
     * @var $country
     * @var $source
     * @var $source_embed
     * @var $source_url
     *
     */
    if (!$admin) $val['featured'] = $onlinetv['featured'];
    extract(array_merge($expected, $val));
    $image = $onlinetv['image'];
    $id = $onlinetv['id'];
    //$slug = unique_slugger($name, 'onlinetv', $onlinetv['id']);
    $slug = slugger($name);
    $file = input_file('image');
    if ($file) {
        $uploader = new Uploader($file);
        if ($uploader->passed()) {
            $uploader->setPath('onlinetvs/preview/');
            $image = $uploader->resize(700, 500)->result();
        }
    }

    $time = time();
    $entity = explode('-', $entity);
    if (count($entity) == 2) {
        $entity_type = $entity[0];
        $entity_id = $entity[1];
    }
    if (!isset($entity_type) || !isset($entity_type)) {
        return false;
    }
    $source_url = urlencode($source_url);
    $source_embed = mysqli_real_escape_string(db(), htmlentities($source_embed));
    db()->query("UPDATE onlinetvs SET source = '" . $source . "',country = '" . $country . "',price = '" . $price . "',source_embed = '" . $source_embed . "',source_url = '" . $source_url . "',slug = '" . $slug . "', featured = '" . $featured . "', image = '" . $image . "', name = '" . $name . "', description = '" . $description . "', update_time = '" . $time . "', privacy = '" . $privacy . "', category_id = '" . $category . "' WHERE id = '" . $id . "'");
    fire_hook('plugins.users.category.updater', null, array('onlinetvs', $val, $id, 'id'));
    return true;
}

function get_onlinetv($id)
{
    $db = db();
    $query = $db->query("SELECT * FROM onlinetvs WHERE " . (is_numeric($id) ? "id = " . $id : "slug = '" . $id . "'"));
    $onlinetv = $query->fetch_assoc();
    return $onlinetv ? arrange_onlinetv($onlinetv) : $onlinetv;
}

function is_onlinetv_owner($onlinetv)
{
    if (!is_loggedIn()) return false;
    if ($onlinetv['user_id'] == get_userid()) return true;
    return false;
}

function delete_onlinetv($id)
{
    $onlinetv = get_onlinetv($id);
    if ($onlinetv['image']) delete_file(path($onlinetv['image']));
    return db()->query("DELETE FROM onlinetvs WHERE id='" . $id . "'");
}

function get_onlinetvs($type, $category = null, $term = null, $user_id = null, $limit = 12, $filter = 'all', $onlinetv = null, $entity_type = 'user', $entity_id = null, $country = null)
{
    $limit = isset($limit) ? $limit : 10;
    $sql = "SELECT * FROM onlinetvs ";
    $user_id = $user_id ? $user_id : get_userid();
    $sql = fire_hook("use.different.onlinetvs.query", $sql, array());
    if ($type == 'mine') {
        $sql .= " WHERE user_id = '" . $user_id . "' ";
        $sql .= $filter == 'featured' ? " AND featured = '1' " : '';
    } elseif ($type == 'related') {
        $name = $onlinetv['name'];
        $explode = explode(' ', $name);
        $w = '';
        foreach ($explode as $t) {
            $w .= $w ? " OR  (name LIKE '%" . $t . "%' OR description LIKE '%" . $t . "') " : "  (name LIKE '%" . $t . "%' OR description LIKE '%" . $t . "')";
        }
        $onlinetv_id = $onlinetv['id'];
        $privacy_sql = fire_hook('privacy.sql', ' ');
        $sql .= " WHERE (" . $w . ") AND status = '1' AND id != '" . $onlinetv_id . "' AND (" . $privacy_sql . ") ";
        $sql = fire_hook("more.onlinetvs.query.filter", $sql, array($entity_type, $entity_id));
    } else {
        if ($term && !$category) {
            $sql .= " WHERE status = 1 AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
        } elseif ($term && $category != 'all') {
            $subCategories = get_onlinetv_parent_categories($category);
            if (!empty($subCategories)) {
                $subIds = array();
                foreach ($subCategories as $cat) {
                    $subIds[] = $cat['id'];
                }
                $subIds = implode(',', $subIds);
                $sql .= " WHERE status = 1 AND (category_id = '" . $category . "' OR category_id IN ({$subIds})) AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
            } else {
                $sql .= " WHERE status = 1 AND category_id = '" . $category . "' AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
            }
        } elseif ($term && $category == 'all') {
            $sql .= " WHERE status = 1 AND (name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "')";
        } elseif ($category && $category != 'all') {
            $sql .= " WHERE status = 1 AND category_id = '" . $category . "'";
        } else {
            $sql .= " WHERE status = '1'";
        }
        if ($country and $country != 'all') {
            $sql .= " AND country = '{$country}'";
        }
        $sql .= $filter == 'featured' ? " AND featured = '1' " : '';
        $privacy_sql = fire_hook('privacy.sql', ' ');
        $sql .= " AND (" . $privacy_sql . ") ";
        if ($entity_type && $entity_id) {
            $entity_sql = "entity_type = '" . $entity_type . "' AND entity_id = " . $entity_id;
            $sql .= " AND (" . $entity_sql . ") ";
        }
        $sql = fire_hook("more.onlinetvs.query.filter", $sql, array($entity_type, $entity_id));
    }
    $sql = fire_hook('users.category.filter', $sql, array($sql));
    if ($filter == 'top') {
        $sql .= " ORDER BY views desc";
    } else {
        $sql .= " ORDER BY time desc";
    }
    //echo $sql;die();
    return paginate($sql, $limit);
}

function admin_get_onlinetvs($term = null, $limit = 10)
{
    $sql = '';

    if ($term) $sql .= " WHERE name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "%' OR tags LIKE '%" . $term . "%'";
    return paginate("SELECT * FROM onlinetvs " . $sql . " ORDER BY TIME DESC", $limit);
}

function count_total_onlinetvs()
{
    $query = db()->query("SELECT * FROM onlinetvs");
    return $query->num_rows;
}

function top_onlinetvgers()
{
    $sql = "SELECT user_id AS onlinetvger_id, (SELECT COUNT(id) FROM onlinetvs WHERE user_id = onlinetvger_id) AS user_onlinetvs FROM onlinetvs GROUP BY onlinetvger_id ORDER BY user_onlinetvs DESC LIMIT 5";
    $query = db()->query($sql);
    return fetch_all($query);
}

function count_top_onlinetvger_onlinetvs($onlinetvger)
{
    $query = db()->query("SELECT * FROM onlinetvs WHERE user_id = '" . $onlinetvger . "'");
    return $query->num_rows;
}

function onlinetv_add_category($val)
{
    $expected = array(
        'title' => '',
        'category' => ''
    );

    /**
     * @var $title
     * @var $desc
     * @var $category
     */
    extract(array_merge($expected, $val));
    $titleSlug = "onlinetv_category_" . md5(time() . serialize($val)) . '_title';
    foreach ($title as $langId => $t) {
        add_language_phrase($titleSlug, $t, $langId, 'onlinetv');
    }
    $order = db()->query('SELECT id FROM onlinetv_categories');
    $order = $order->num_rows;
    db()->query("INSERT INTO `onlinetv_categories`(`title`,`category_order`,`parent_id`) VALUES('" . $titleSlug . "','" . $order . "','" . $category . "')");
    return true;
}

function save_onlinetv_category($val, $category)
{
    $expected = array(
        'title' => ''
    );

    /**
     * @var $title
     */
    extract(array_merge($expected, $val));
    $titleSlug = $category['title'];

    foreach ($title as $langId => $t) {
        (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, ' onlinetv') : add_language_phrase($titleSlug, $t, $langId, 'onlinetv');
    }

    return true;
}

function get_onlinetv_categories()
{
    $query = db()->query("SELECT * FROM `onlinetv_categories` WHERE parent_id ='0' ORDER BY `category_order` ASC");
    return fetch_all($query);
}

function get_onlinetv_parent_categories($id)
{
    $db = db()->query("SELECT * FROM `onlinetv_categories` WHERE parent_id='{$id}' ORDER BY `category_order` ASC");
    $result = fetch_all($db);
    return $result;
}

function get_onlinetv_category($id)
{
    $query = db()->query("SELECT * FROM `onlinetv_categories` WHERE `id`='" . $id . "'");
    return $query->fetch_assoc();
}

function arrange_onlinetv($onlinetv)
{
    $category = get_onlinetv_category($onlinetv['category_id']);
    if ($category) {
        $onlinetv['category'] = $category;
    }
    $onlinetv = fire_hook('onlinetv.arrange', $onlinetv);
    $onlinetv['publisher'] = get_onlinetv_publisher($onlinetv);
    return $onlinetv;
}

function get_onlinetv_publisher($onlinetv)
{
    if ($onlinetv['entity_type'] == 'user') {
        $user = find_user($onlinetv['entity_id']);
        $publisher = array(
            'id' => $user['username'],
            'name' => get_user_name($user),
            'avatar' => get_avatar(200, $user)
        );
    } else {
        $publisher = fire_hook('entity.data', array(false), array($onlinetv['entity_type'], $onlinetv['entity_id']));
    }
    return $publisher;
}

function delete_onlinetv_category($id, $category)
{
    delete_all_language_phrase($category['title']);
    db()->query("DELETE FROM `onlinetv_categories` WHERE `id`='" . $id . "'");
    return true;
}

function update_onlinetv_category_order($id, $order)
{
    db()->query("UPDATE `onlinetv_categories` SET `category_order`='" . $order . "' WHERE  `id`='" . $id . "'");
}

function onlinetv_slug_exists($slug)
{
    $query = db()->query("SELECT COUNT(id) FROM `onlinetvs` WHERE  `slug`='" . $slug . "'");
    $result = $query->fetch_row();
    return $result[0] == 0 ? FALSE : TRUE;
}