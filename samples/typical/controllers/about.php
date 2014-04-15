<?php
    
class AboutController extends Controller {
    public function index() {
        $this->meta->title = 'About - one-php-mvc Sample Site';
        $this->view();
    }

    public function what_we_do() {
        $this->meta->title = 'What We Do - one-php-mvc Sample Site';
        $this->view();
    }
}

?>