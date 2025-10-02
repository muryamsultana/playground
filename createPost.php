<?php
register_post_type('book', ['label' => 'Books',  'public' => true, 'supports' => ['title', 'editor', 'thumbnail'], 'has_archive' => true]);
