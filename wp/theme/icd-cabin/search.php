<?php if (isset($_GET['post_type']) && $_GET['post_type'] === 'product') get_template_part('archive-product'); else get_template_part('archive');
