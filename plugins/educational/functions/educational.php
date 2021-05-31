<?php

class Educational extends App
{
    private $user_id;
    private $paginate = true;
    private $limit = 6;
    private static $instance;
    private $table_name = "lh_books";
    private $cTableName = "`books_categories`"; //categoryTable Name
    private $pluginName = "educational";

    public function __constructor()
    {

    }


    public static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new Educational();
        }
        return static::$instance;
    }

    public function profileBooks($uid){
        $sql = "SELECT * FROM ".$this->table_name." WHERE user_id ='{$uid}' ORDER BY time DESC";
        return paginate($sql,$this->limit);
    }

    public function saveBook($val, $t = 'new', $admin = false)
    {
        $expected = array(
            'title' => '',
            'description' => '',
            'category' => '',
            'privacy' => 1,
            'comment' => 1,
            'genre' => '',
            'price' => '',
            'author' => '',
            'book' => '',
            'slug' => '',
            'photo' => ''
        );
        extract(array_merge($expected, $val));
        /**
         * @var $title
         * @var $description
         * @var $category
         * @var $privacy
         * @var $comment
         * @var $genre
         * @var $price
         * @var $author
         * @var $book
         * @var $slug
         * @var $photo
         */
        $db = db();
        $title = sanitizeText($title);
        $category = sanitizeText($category);
        $description =sanitizeText($description);
        $privacy = sanitizeText($privacy);
        $comment = sanitizeText($comment);
        $genre = sanitizeText($genre);
        $price = sanitizeText($price);
        $author = sanitizeText($author);
        $book = sanitizeText($book);
        $slug = sanitizeText($slug);

        if ($t == 'new') {
            $time = time();
            $id = get_userid();
            $sql = "INSERT INTO " . $this->table_name . " (title,description,category,image,user_id,comment,privacy,price,author,genre,time,book,slug)
            VALUES ('{$title}','{$description}','{$category}','{$photo}','{$id}','{$comment}','{$privacy}','{$price}','{$author}',
            '{$genre}','{$time}','{$book}','{$slug}')";
            $db->query($sql);
            //echo $db->error;die();
            $id = $db->insert_id;
            if($admin && isset($val['featured'])){
                $featured = $val['featured'];
                db()->query("UPDATE ".$this->table_name ." SET featured='{$featured}' WHERE id='{$id}'");
            }
            fire_hook("added.new.book",null,array($val,$id));
            return $id;
        } else {
            //$t is now the id
            $id = $t;
            $sql = "UPDATE " . $this->table_name . " SET title='{$title}',description='{$description}', category='{$category}',book='{$book}',slug='{$slug}',
        image='{$photo}', comment = '{$comment}', privacy= '{$privacy}', price='{$price}', author = '{$author}', genre ='{$genre}' WHERE id='{$id}'";
            $db->query($sql);

            if($admin){
                $featured = $val['featured'];
                db()->query("UPDATE ".$this->table_name ." SET featured='{$featured}' WHERE id='{$id}'");
            }
            //echo db()->error;die();
            return $id;
        }
    }

    public function IncreaseDownloadCount($id){
        if(session_get("book_download".$id)) return true;
        db()->query("UPDATE ".$this->table_name ." SET downloads = downloads + 1 WHERE id='{$id}'");
        session_put("book_download".$id,true);
        return true;
    }

    public function updateBookView($id){
        if(session_get("book_".$id)) return true;
        db()->query("UPDATE ".$this->table_name ." SET views = views + 1 WHERE id='{$id}'");
        session_put("book_".$id,true);
        return true;
    }
    public function deleteBook($book){
        $id = $book['id'];
        $slug = $book['slug'];
        //delete file
        delete_file(path($book['image']));
        delete_file(path($book['book']));
        $sql = " DELETE FROM ".$this->table_name . " WHERE id='{$id}' AND slug ='{$slug}' ";
        db()->query($sql);
        return true;
    }

    public function isBookOwner($book){
        if($book['user_id'] == get_userid()) return true;
        return false;
    }
    public function canViewBook($book){
        if(!$book) return false;
        if($this->isBookOwner($book)) return true;
        if($book['privacy'] == 1) return true;
        if($book['privacy'] == 2 and relationship_valid($book['user_id'],1)) return true;
        if($book['privacy'] == 3 and $this->isBookOwner($book)) return true;
        return false;
    }
    public function getFeatured($limit){
        $sql = "SELECT * FROM ".$this->table_name. " WHERE featured='1' ORDER BY time DESC  ";
        return paginate($sql,$limit);
    }
    public function getTopDownloads($limit){
        $sql = "SELECT * FROM ".$this->table_name. " ORDER BY downloads DESC  ";
        return paginate($sql,$limit);
    }

    public function getLatest($limit){
        $sql = "SELECT * FROM ".$this->table_name. " ORDER BY time DESC";
        return paginate($sql,$limit);
    }
    public function getRelated($book,$limit){
        $title = $book['title'];
        $genre = $book['genre'];
        $id = $book['id'];
        $category = $book['category'];
        $author = $book['author'];
        return paginate("SELECT * FROM ".$this->table_name. " WHERE id != '{$id}' AND  title LIKE '%{$title}'
         OR category='{$category}' OR genre LIKE '%{$genre}' OR author LIKE '%{$author}' ORDER BY RAND()", $limit);
    }
    public function getBook($id,$slug = null){
        $id = sanitizeText($id);
        $sql = "SELECT * FROM ".$this->table_name." WHERE id='{$id}'";
        if($slug){
            $slug = sanitizeText($slug);
            $sql .= " AND slug = '{$slug}'";
        }
        //echo $sql;die();
        $this->paginate = false;
        return $this->dbQuery($sql);
    }
    public function getBooks($type = 'all',$term = null,$category= null,$genre = null,$author = null,$filter = 'all')
    {
        switch ($type) {
            case 'all':
                $sql = "SELECT * FROM " . $this->table_name ." WHERE time != 0 ";
                if($term){
                    $sql .= " AND title LIKE '%{$term}%' ";
                }
                if($category && $category != 'all'){
                    $sql .= " AND category='{$category}'";
                }
                if($genre){
                    $sql .= " AND genre LIKE '%{$genre}%'";
                }
                if($author){
                    $sql .= " AND author LIKE '%{$author}%'";
                }
                if($filter == 'all'){
                    $sql .=" ORDER BY time DESC ";
                }else{
                    if($filter == 'top'){
                        $sql .=" ORDER BY views DESC";
                    }
                    if($filter == 'featured'){
                        $sql .= " AND featured = 1 ORDER BY time DESC";
                    }
                    if($filter == 'downloads'){
                        $sql .=" ORDER BY downloads DESC";
                    }
                }
                return $this->dbQuery($sql);
                break;
            case 'mine':
                $id = get_userid();
                $sql = "SELECT * FROM " . $this->table_name . " WHERE user_id='{$id}'";
                if($term){
                    $sql .= " AND title LIKE '%{$term}%'";
                }
                if($category && $category != 'all'){
                    $sql .= " AND category='{$category}'";
                }
                if($genre){
                    $sql .= " AND genre LIKE '%{$genre}%'";
                }
                if($author){
                    $sql .= " AND author LIKE '%{$author}%'";
                }
                $sql .=" ORDER BY time DESC ";
                return $this->dbQuery($sql);
                break;
        }
        return '';
    }

    public function getTableName()
    {
        return $this->table_name;
    }

    public function setTableName($name)
    {
        $this->table_name = $name;
        return $this->table_name;
    }


    public function getUserid()
    {
        return $this->user_id;
    }

    public function setUserid($id)
    {
        $this->user_id = $id;
        return $this->user_id;
    }

    public function  dbQuery($sql)
    {
        if ($this->paginate) {
            return paginate($sql, $this->limit);
        }
        $arr = array();
        $q = db()->query($sql);
        if(db()->error){
            echo db()->error; die();
        }
        if ($q->num_rows > 0) {
            while ($r = $q->fetch_assoc()) {
                $arr[] = $r;
            }
        }
        return $arr;
    }

    /**
     * @param $val
     * @return bool
     * Blog Categories
     */
    public function add_category($val)
    {
        $expected = array(
            'title' => ''
        );

        /**
         * @var $title
         * @var $desc
         */
        extract(array_merge($expected, $val));
        $titleSlug = $this->pluginName . "_category_" . md5(time() . serialize($val)) . '_title';

        foreach ($title as $langId => $t) {
            add_language_phrase($titleSlug, $t, $langId, $this->pluginName);
        }


        $time = time();
        $order = db()->query('SELECT id FROM ' . $this->cTableName);
        $order = $order->num_rows;
        $query = db()->query("INSERT INTO " . $this->cTableName . "(
            `title`,`category_order`) VALUES(
            '{$titleSlug}','{$order}'
            )
        ");

        return true;
    }

    public function save_category($val, $category)
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
            (phrase_exists($langId, $titleSlug)) ? update_language_phrase($titleSlug, $t, $langId, $this->pluginName) : add_language_phrase($titleSlug, $t, $langId, $this->pluginName);
        }

        return true;
    }

    public function get_categories()
    {
        $query = db()->query("SELECT * FROM " . $this->cTableName . " ORDER BY `category_order` ASC");
        return fetch_all($query);
    }

    public function get_category($id)
    {
        $query = db()->query("SELECT * FROM " . $this->cTableName . " WHERE `id`='{$id}'");
        return $query->fetch_assoc();
    }

    public function delete_category($id, $category)
    {
        delete_all_language_phrase($category['title']);

        db()->query("DELETE FROM " . $this->cTableName . " WHERE `id`='{$id}'");

        return true;
    }

    public function update_category_order($id, $order)
    {
        db()->query("UPDATE " . $this->cTableName . " SET `category_order`='{$order}' WHERE  `id`='{$id}'");
    }

    //Rating start
    function hasRated($type,$typeid){
        $userid = get_userid();
        $sql = db()->query("SELECT * FROM lp_ratings WHERE type='{$type}' AND type_id='{$typeid}' AND user_id='{$userid}'");
        if($sql->num_rows > 0) return true;
        return false;
    }

    public function rateItem($type,$type_id,$rating){
        $userid = get_userid();
        $sql = "INSERT INTO lp_ratings(type,type_id,user_id,rating) VALUES ('{$type}','{$type_id}','{$userid}','{$rating}')";
        db()->query($sql);
        return true;
    }

    public function getUserRating($type,$typeid,$userid= null){
        $userid = ($userid) ? $userid : get_userid();
        $sql = db()->query("SELECT rating FROM lp_ratings WHERE type='{$type}' AND type_id='{$typeid}' AND user_id='{$userid}'");
        if($sql->num_rows > 0){
            $r = $sql->fetch_assoc();
            return $r['rating'];
        }
        return false;
    }

    public function getItemRatings($type,$typeid){
        $sql = db()->query("SELECT * FROM lp_ratings WHERE type='{$type}' AND type_id='{$typeid}'");
        $response = array();
        if($sql->num_rows > 0){
            while($r = $sql->fetch_assoc()){
                $response[] = $r['rating'];
            }
            return $response;
        }
        return $response;
    }

    public function getAverageRating($ratings_arr){
        $average = 0;
        if($ratings_arr){
            $count = count($ratings_arr);
            $total = array_sum($ratings_arr);
            $average= ceil($total/$count);
        }
        return $average;
    }

}

