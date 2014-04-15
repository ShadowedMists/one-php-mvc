<?php
    
class User Controller extends Controller {
    public function index($id = null) {
        if($id != NULL) {
            $this->meta->title = "$id - User";
            $this->view(array('user' => $id), 'user');
            return;
        }

        $this->meta->title = 'Users - one-php-mvc Sample Site';
        $this->view();
    }
}

?>